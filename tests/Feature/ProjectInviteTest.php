<?php

use App\Models\Project;
use App\Models\ProjectInvite;
use App\Models\ProjectMember;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->project = Project::factory()->create(['user_id' => $this->user->id]);
    $this->actingAs($this->user);
});

test('user can invite someone to project by email', function () {
    $invite = [
        'email' => 'newuser@example.com',
        'role' => 'editor',
    ];

    $response = $this->post("/projects/{$this->project->id}/members", $invite);

    $response->assertRedirect();
    $this->assertDatabaseHas('project_invites', [
        'project_id' => $this->project->id,
        'email' => 'newuser@example.com',
        'role' => 'editor',
        'status' => 'pending',
    ]);
});

test('cannot invite same email twice if pending invite exists', function () {
    ProjectInvite::create([
        'project_id' => $this->project->id,
        'email' => 'duplicate@example.com',
        'token' => Str::random(32),
        'role' => 'member',
        'invited_by' => $this->user->id,
        'expires_at' => now()->addHours(24),
        'status' => 'pending',
    ]);

    $response = $this->post("/projects/{$this->project->id}/members", [
        'email' => 'duplicate@example.com',
        'role' => 'editor',
    ]);

    $response->assertSessionHasErrors('email');
});

test('cannot invite existing project member', function () {
    $memberUser = User::factory()->create();
    ProjectMember::create([
        'project_id' => $this->project->id,
        'user_id' => $memberUser->id,
        'role' => 'member',
        'status' => 'active',
        'invited_by' => $this->user->id,
    ]);

    $response = $this->post("/projects/{$this->project->id}/members", [
        'email' => $memberUser->email,
        'role' => 'editor',
    ]);

    $response->assertSessionHasErrors('email');
});

test('invited user can accept and join project', function () {
    $invitedUser = User::factory()->create(['email' => 'invited@example.com']);

    $invite = ProjectInvite::create([
        'project_id' => $this->project->id,
        'email' => $invitedUser->email,
        'token' => $token = Str::random(32),
        'role' => 'editor',
        'invited_by' => $this->user->id,
        'expires_at' => now()->addHours(24),
        'status' => 'pending',
    ]);

    $this->actingAs($invitedUser)
        ->post("/invites/{$token}/accept")
        ->assertRedirect("/projects/{$this->project->id}");

    $this->assertDatabaseHas('project_members', [
        'project_id' => $this->project->id,
        'user_id' => $invitedUser->id,
        'role' => 'editor',
        'status' => 'active',
    ]);

    $this->assertDatabaseHas('project_invites', [
        'id' => $invite->id,
        'status' => 'accepted',
    ]);
});

test('non-registered user is redirected to register when accepting invite', function () {
    $token = Str::random(32);

    ProjectInvite::create([
        'project_id' => $this->project->id,
        'email' => 'newperson@example.com',
        'token' => $token,
        'role' => 'member',
        'invited_by' => $this->user->id,
        'expires_at' => now()->addHours(24),
        'status' => 'pending',
    ]);

    $response = $this->post("/invites/{$token}/accept");

    $response->assertRedirect(route('register', [
        'email' => 'newperson@example.com',
        'invite_token' => $token,
    ]));
});

test('authenticated browser session is logged out before redirecting unregistered invitee to register', function () {
    $browserUser = User::factory()->create();
    $token = Str::random(32);

    ProjectInvite::create([
        'project_id' => $this->project->id,
        'email' => 'fresh@example.com',
        'token' => $token,
        'role' => 'member',
        'invited_by' => $this->user->id,
        'expires_at' => now()->addHours(24),
        'status' => 'pending',
    ]);

    $response = $this->actingAs($browserUser)->post("/invites/{$token}/accept");

    $response->assertRedirect(route('register', [
        'email' => 'fresh@example.com',
        'invite_token' => $token,
    ]));

    $this->assertGuest();
});

test('expired invite cannot be accepted', function () {
    $invitedUser = User::factory()->create(['email' => 'expired@example.com']);

    $token = Str::random(32);
    ProjectInvite::create([
        'project_id' => $this->project->id,
        'email' => $invitedUser->email,
        'token' => $token,
        'role' => 'member',
        'invited_by' => $this->user->id,
        'expires_at' => now()->subHours(1), // Expired
        'status' => 'pending',
    ]);

    $this->actingAs($invitedUser)->get("/invites/{$token}")
        ->assertNotFound();
});

test('non-member cannot view members page', function () {
    $otherUser = User::factory()->create();
    $this->actingAs($otherUser)
        ->get("/projects/{$this->project->id}/members")
        ->assertForbidden();
});

test('project owner can view members page', function () {
    $response = $this->get("/projects/{$this->project->id}/members");

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Board/Members')
        ->has('project')
        ->has('members')
        ->has('pendingInvites')
    );
});

test('invited member can view members page', function () {
    $memberUser = User::factory()->create();
    ProjectMember::create([
        'project_id' => $this->project->id,
        'user_id' => $memberUser->id,
        'role' => 'member',
        'status' => 'active',
        'invited_by' => $this->user->id,
    ]);

    $response = $this->actingAs($memberUser)
        ->get("/projects/{$this->project->id}/members");

    $response->assertOk();
});

test('project owner can remove member', function () {
    $memberUser = User::factory()->create();
    $member = ProjectMember::create([
        'project_id' => $this->project->id,
        'user_id' => $memberUser->id,
        'role' => 'member',
        'status' => 'active',
        'invited_by' => $this->user->id,
    ]);

    $response = $this->delete("/projects/{$this->project->id}/members/{$member->id}");

    $response->assertRedirect();
    $this->assertDatabaseMissing('project_members', [
        'id' => $member->id,
        'status' => 'active',
    ]);
});

test('validates email format when inviting', function () {
    $response = $this->post("/projects/{$this->project->id}/members", [
        'email' => 'not-an-email',
        'role' => 'editor',
    ]);

    $response->assertSessionHasErrors('email');
});

test('validates role when inviting', function () {
    $response = $this->post("/projects/{$this->project->id}/members", [
        'email' => 'valid@example.com',
        'role' => 'invalid-role',
    ]);

    $response->assertSessionHasErrors('role');
});

test('project permission checks work for tasks', function () {
    $memberUser = User::factory()->create();
    ProjectMember::create([
        'project_id' => $this->project->id,
        'user_id' => $memberUser->id,
        'role' => 'editor',
        'status' => 'active',
        'invited_by' => $this->user->id,
    ]);

    // Member can view project
    $response = $this->actingAs($memberUser)
        ->get("/projects/{$this->project->id}");

    $response->assertOk();
});

test('accepted member project appears on dashboard', function () {
    $memberUser = User::factory()->create();

    ProjectMember::create([
        'project_id' => $this->project->id,
        'user_id' => $memberUser->id,
        'role' => 'member',
        'status' => 'active',
        'invited_by' => $this->user->id,
    ]);

    $response = $this->actingAs($memberUser)->get('/dashboard');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->has('projects', 1)
        ->where('projects.0.id', $this->project->id)
    );
});

test('non-member cannot view project board', function () {
    $otherUser = User::factory()->create();

    $response = $this->actingAs($otherUser)
        ->get("/projects/{$this->project->id}");

    $response->assertForbidden();
});
