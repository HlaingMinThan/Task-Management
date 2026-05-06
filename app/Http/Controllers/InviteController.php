<?php

namespace App\Http\Controllers;

use App\Models\ProjectInvite;
use App\Models\ProjectMember;
use App\Models\User;
use Illuminate\Http\Request;

class InviteController extends Controller
{
    /**
     * Show the invite acceptance page.
     */
    public function show(string $token)
    {
        $invite = ProjectInvite::where('token', $token)
            ->where('status', 'pending')
            ->where('expires_at', '>', now())
            ->with('project', 'invitedBy')
            ->firstOrFail();

        // Check if user email already exists
        $emailExists = User::where('email', $invite->email)->exists();

        return inertia('AcceptInvite', [
            'invite' => $invite->only('email', 'role', 'id'),
            'projectName' => $invite->project->name,
            'inviterName' => $invite->invitedBy->name,
            'token' => $token,
            'userExists' => $emailExists,
        ]);
    }

    /**
     * Accept an invite and add user to project.
     */
    public function accept(Request $request, string $token)
    {
        $invite = ProjectInvite::where('token', $token)
            ->where('status', 'pending')
            ->where('expires_at', '>', now())
            ->with('project')
            ->firstOrFail();

        // Find or create user
        $user = User::where('email', $invite->email)->first();

        if (! $user) {
            // Create new user - redirect to register page
            return redirect()->route('register', [
                'email' => $invite->email,
                'invite_token' => $token,
            ]);
        }

        // User exists, authorize them
        auth()->login($user);

        // Check if already a member
        $existingMember = ProjectMember::where('project_id', $invite->project_id)
            ->where('user_id', $user->id)
            ->where('status', 'active')
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
