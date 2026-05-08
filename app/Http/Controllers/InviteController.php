<?php

namespace App\Http\Controllers;

use App\Models\ProjectInvite;
use App\Models\ProjectMember;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InviteController extends Controller
{
    /**
     * Show the invite acceptance page.
     */
    public function show(string $token)
    {
        $invite = ProjectInvite::query()
            ->where('token', '=', $token)
            ->with('project', 'invitedBy')
            ->firstOrFail();

        $canAccept = $invite->status === 'pending'
            && $invite->expires_at
            && $invite->expires_at->isFuture();

        $statusMessage = null;

        if (! $canAccept) {
            if ($invite->status === 'pending' && $invite->expires_at && $invite->expires_at->isPast()) {
                $statusMessage = 'Invitation has expired. Please ask the project owner to resend it.';
            } elseif ($invite->status === 'accepted') {
                $statusMessage = 'This invitation has already been accepted.';
            } elseif ($invite->status === 'cancelled') {
                $statusMessage = 'This invitation was cancelled by the project owner.';
            } else {
                $statusMessage = 'This invitation is no longer available.';
            }
        }

        // Check if user email already exists
        $emailExists = User::query()
            ->where('email', '=', $invite->email)
            ->exists();

        return inertia('AcceptInvite', [
            'invite' => $invite->only('email', 'role', 'id'),
            'projectName' => $invite->project->name,
            'inviterName' => $invite->invitedBy->name,
            'token' => $token,
            'userExists' => $emailExists,
            'canAccept' => $canAccept,
            'statusMessage' => $statusMessage,
        ]);
    }

    /**
     * Accept an invite and add user to project.
     */
    public function accept(Request $request, string $token)
    {
        $invite = ProjectInvite::query()
            ->where('token', '=', $token)
            ->with('project')
            ->firstOrFail();

        $canAccept = $invite->status === 'pending'
            && $invite->expires_at
            && $invite->expires_at->isFuture();

        if (! $canAccept) {
            return redirect()->route('invites.show', ['token' => $token]);
        }

        // Find or create user
        $user = User::query()
            ->where('email', '=', $invite->email)
            ->first();

        if (! $user) {
            // Clear any existing session so the guest register route won't bounce to dashboard.
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            // Create new user - redirect to register page
            return redirect()->route('register', [
                'email' => $invite->email,
                'invite_token' => $token,
            ]);
        }

        // User exists, authorize them
        Auth::login($user);

        // Check if already a member
        $existingMember = ProjectMember::query()
            ->where('project_id', '=', $invite->project_id)
            ->where('user_id', '=', $user->id)
            ->where('status', '=', 'active')
            ->exists();

        if (! $existingMember) {
            // Add user to project
            ProjectMember::create([
                'project_id' => $invite->project_id,
                'user_id' => $user->id,
                'role' => $invite->role,
                'status' => 'active',
                'invited_by' => $invite->invited_by,
            ]);
        }

        // Mark invite as accepted
        $invite->update(['status' => 'accepted']);

        return redirect()->route('projects.show', ['project' => $invite->project_id])
            ->with('success', 'You have accepted the invitation and joined the project!');
    }
}
