<?php

namespace App\Http\Controllers;

use App\Mail\InviteProjectMember;
use App\Models\Project;
use App\Models\ProjectInvite;
use App\Models\ProjectMember;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class ProjectMemberController extends Controller
{
    /**
     * Show members and invites for a project.
     */
    public function show(Project $project)
    {
        $this->authorize('view', $project);

        $members = $project->members()
            ->where('status', 'active')
            ->with('user', 'invitedBy')
            ->get();

        $invites = $project->invites()
            ->where('status', 'pending')
            ->where('expires_at', '>', now())
            ->with('invitedBy')
            ->get();

        return inertia('Board/Members', [
            'project' => $project,
            'members' => $members,
            'pendingInvites' => $invites,
        ]);
    }

    /**
     * Store a new project invite.
     */
    public function store(Request $request, Project $project)
    {
        // Check if user is a member of the project
        $this->authorize('view', $project);

        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'role' => ['required', 'in:owner,editor,viewer'],
        ]);

        // Check if email is already a member
        $existingMember = ProjectMember::where('project_id', $project->id)
            ->whereHas('user', fn($q) => $q->where('email', $validated['email']))
            ->first();

        if ($existingMember) {
            return back()->withErrors([
                'email' => 'This user is already a member of this project.',
            ]);
        }

        // Check if there's a pending invite
        $pendingInvite = ProjectInvite::where('project_id', $project->id)
            ->where('email', $validated['email'])
            ->first();

        if ($pendingInvite) {
            return back()->withErrors([
                'email' => 'An invitation for this email is already pending.',
            ]);
        }

        // Create the invite (handle unique constraint if an invite record exists)
        try {
            $invite = ProjectInvite::create([
                'project_id' => $project->id,
                'email' => $validated['email'],
                'token' => Str::random(32),
                'role' => $validated['role'],
                'invited_by' => Auth::id(),
                'expires_at' => now()->addHours(24),
            ]);
        } catch (\Illuminate\Database\UniqueConstraintViolationException $e) {
            // Find existing invite record
            $existing = ProjectInvite::where('project_id', $project->id)
                ->where('email', $validated['email'])
                ->first();

            if (! $existing) {
                // Unexpected — rethrow
                throw $e;
            }

            // If there's an active pending invite that hasn't expired, return error
            if ($existing->status === 'pending' && $existing->expires_at && $existing->expires_at->isFuture()) {
                return back()->withErrors([
                    'email' => 'An invitation for this email is already pending.',
                ]);
            }

            // Otherwise reuse/update the existing invite record to become a fresh pending invite
            $existing->update([
                'token' => Str::random(32),
                'role' => $validated['role'],
                'status' => 'pending',
                'invited_by' => Auth::id(),
                'expires_at' => now()->addHours(24),
            ]);

            $invite = $existing;
        }

        // Send invitation email
        Mail::send(new InviteProjectMember($invite));

        return back()->with('success', 'Invitation sent successfully!');
    }

    /**
     * Update a member's role.
     */
    public function update(Request $request, Project $project, ProjectMember $member)
    {
        // Only project owner can update roles
        if ($project->user_id !== Auth::id()) {
            $this->authorize('update', $project);
        }

        if ($member->project_id !== $project->id) {
            return abort(404);
        }

        $validated = $request->validate([
            'role' => ['required', 'in:owner,editor,viewer'],
        ]);

        $member->update(['role' => $validated['role']]);

        return back()->with('success', 'Member role updated.');
    }

    /**
     * Remove a member from the project.
     */
    public function destroy(Project $project, ProjectMember $member)
    {
        // Only project owner can remove members
        if ($project->user_id !== Auth::id()) {
            $this->authorize('delete', $project);
        }

        if ($member->project_id !== $project->id) {
            return abort(404);
        }

        // Cannot remove the owner
        if ($member->user_id === $project->user_id && $member->role === 'owner') {
            return back()->withErrors(['error' => 'Cannot remove the project owner.']);
        }

        $member->delete();

        return back()->with('success', 'Member removed from project.');
    }

    /**
     * Cancel a pending invite.
     */
    public function cancelInvite(Project $project, ProjectInvite $invite)
    {
        // Only project owner can cancel invites
        if ($project->user_id !== Auth::id()) {
            $this->authorize('delete', $project);
        }

        if ($invite->project_id !== $project->id) {
            return abort(404);
        }

        $invite->update(['status' => 'cancelled']);

        return back()->with('success', 'Invitation cancelled.');
    }
}
