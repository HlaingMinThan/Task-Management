<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\ProjectInvite;
use App\Models\ProjectMember;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(Request $request): Response
    {
        return Inertia::render('Auth/Register', [
            'email' => $request->query('email'),
            'inviteToken' => $request->query('invite_token'),
        ]);
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:'.User::class,
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        Auth::login($user);

        // Check if user registered via invite link
        $inviteToken = $request->input('invite_token');
        if ($inviteToken) {
            $invite = ProjectInvite::where('token', $inviteToken)
                ->where('email', $user->email)
                ->where('status', 'pending')
                ->where('expires_at', '>', now())
                ->with('project')
                ->first();

            if ($invite) {
                // Add user to project
                ProjectMember::create([
                    'project_id' => $invite->project_id,
                    'user_id' => $user->id,
                    'role' => $invite->role,
                    'status' => 'active',
                    'invited_by' => $invite->invited_by,
                ]);

                // Mark invite as accepted
                $invite->update(['status' => 'accepted']);

                return redirect()->route('projects.show', ['project' => $invite->project_id])
                    ->with('success', 'Welcome! You have joined the project.');
            }
        }

        return redirect()->route('dashboard');
    }
}
