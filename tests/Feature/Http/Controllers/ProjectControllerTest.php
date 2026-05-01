<?php

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Inertia\Testing\AssertableInertia;

uses(LazilyRefreshDatabase::class);

test('dashboard displays user projects and not others', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    $project1 = Project::factory()->create(['user_id' => $user1->id, 'name' => 'Project 1']);
    $project2 = Project::factory()->create(['user_id' => $user2->id, 'name' => 'Project 2']);

    $this->actingAs($user1)
        ->get('/dashboard')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Dashboard')
            ->has('projects', 1)
            ->where('projects.0.name', 'Project 1')
        );
});

test('user can create a project', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post('/projects', [
            'name' => 'New Project',
            'description' => 'A test description',
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('projects', [
        'user_id' => $user->id,
        'name' => 'New Project',
        'description' => 'A test description',
    ]);
});

test('project requires a name', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post('/projects', [
            'name' => '',
        ])
        ->assertSessionHasErrors(['name']);
});

test('user can update their own project', function () {
    $user = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user)
        ->put("/projects/{$project->id}", [
            'name' => 'Updated Name',
            'description' => 'Updated Description',
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('projects', [
        'id' => $project->id,
        'name' => 'Updated Name',
        'description' => 'Updated Description',
    ]);
});

test('user cannot update someone elses project', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $user2->id]);

    $this->actingAs($user1)
        ->put("/projects/{$project->id}", [
            'name' => 'Updated Name',
        ])
        ->assertForbidden();
});

test('user can delete their own project', function () {
    $user = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user)
        ->delete("/projects/{$project->id}")
        ->assertRedirect();

    $this->assertDatabaseMissing('projects', [
        'id' => $project->id,
    ]);
});

test('user cannot delete someone elses project', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $user2->id]);

    $this->actingAs($user1)
        ->delete("/projects/{$project->id}")
        ->assertForbidden();

    $this->assertDatabaseHas('projects', [
        'id' => $project->id,
    ]);
});
