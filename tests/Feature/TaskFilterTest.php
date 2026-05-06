<?php

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('searches tasks by title and description and applies filters', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $project = Project::factory()->create(['user_id' => $user->id]);

    $todo = $project->columns()->where('title', 'To Do')->first();
    $doing = $project->columns()->where('title', 'Doing')->first();
    $done = $project->columns()->where('title', 'Done')->first();

    // Create tasks
    $t1 = Task::factory()->create([
        'project_id' => $project->id,
        'column_id' => $todo->id,
        'title' => 'Fix login bug',
        'description' => 'Users cannot login',
        'due_date' => now()->addDays(2)->toDateString(),
    ]);

    $t2 = Task::factory()->create([
        'project_id' => $project->id,
        'column_id' => $doing->id,
        'title' => 'Update docs',
        'description' => 'Documentation update',
        'due_date' => now()->subDays(2)->toDateString(),
    ]);

    $t3 = Task::factory()->create([
        'project_id' => $project->id,
        'column_id' => $done->id,
        'title' => 'Refactor login',
        'description' => 'Refactor code',
        'due_date' => now()->subDays(3)->toDateString(),
    ]);

    // Search for "login" should return t1 and t3
    $response = $this->get(route('projects.show', ['project' => $project->id, 'q' => 'login']));
    $response->assertStatus(200);
    $response->assertSee('Fix login bug');
    $response->assertSee('Refactor login');

    // Overdue filter should return t2 only (due_date < now and not in Done)
    $response = $this->get(route('projects.show', ['project' => $project->id, 'overdue' => 1]));
    $response->assertStatus(200);
    $response->assertSee('Update docs');
    $response->assertDontSee('Refactor login');

    // Status filter (column_id) should return only tasks in that column
    $response = $this->get(route('projects.show', ['project' => $project->id, 'status' => $todo->id]));
    $response->assertStatus(200);
    $response->assertSee('Fix login bug');
    $response->assertDontSee('Update docs');
});
