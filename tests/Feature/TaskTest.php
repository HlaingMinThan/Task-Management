<?php

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('allows user to create a task in a column', function () {
    $user = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $user->id]);
    $column = $project->columns()->first();

    $response = $this->actingAs($user)->post(route('tasks.store', [$project, $column]), [
        'title' => 'New Task',
        'description' => 'Task Description',
        'priority' => 'high',
        'due_date' => '2026-12-31',
    ]);

    $response->assertRedirect();
    expect($column->tasks)->toHaveCount(1);
    expect($column->tasks->first()->title)->toBe('New Task');
    expect($column->tasks->first()->priority)->toBe('high');
});

it('allows user to update a task', function () {
    $user = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $user->id]);
    $column = $project->columns()->first();
    $task = Task::factory()->create([
        'project_id' => $project->id,
        'column_id' => $column->id,
        'title' => 'Old Title',
    ]);

    $response = $this->actingAs($user)->patch(route('tasks.update', [$project, $task]), [
        'title' => 'New Title',
        'description' => 'New Description',
        'priority' => 'low',
        'due_date' => '2026-01-01',
    ]);

    $response->assertRedirect();
    expect($task->fresh()->title)->toBe('New Title');
    expect($task->fresh()->priority)->toBe('low');
});

it('allows user to delete a task', function () {
    $user = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $user->id]);
    $column = $project->columns()->first();
    $task = Task::factory()->create([
        'project_id' => $project->id,
        'column_id' => $column->id,
    ]);

    $response = $this->actingAs($user)->delete(route('tasks.destroy', [$project, $task]));

    $response->assertRedirect();
    expect(Task::find($task->id))->toBeNull();
});

it('allows user to reorder tasks in a column', function () {
    $user = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $user->id]);
    $column = $project->columns()->first();

    $task1 = Task::factory()->create(['project_id' => $project->id, 'column_id' => $column->id, 'position' => 1]);
    $task2 = Task::factory()->create(['project_id' => $project->id, 'column_id' => $column->id, 'position' => 2]);

    $response = $this->actingAs($user)->post(route('tasks.reorder', $project), [
        'column_id' => $column->id,
        'tasks' => [
            ['id' => $task1->id, 'position' => 2],
            ['id' => $task2->id, 'position' => 1],
        ],
    ]);

    $response->assertRedirect();
    expect($task1->fresh()->position)->toBe(2);
    expect($task2->fresh()->position)->toBe(1);
});

it('allows user to move a task to another column', function () {
    $user = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $user->id]);
    $column1 = $project->columns()->where('title', 'To Do')->first();
    $column2 = $project->columns()->where('title', 'Doing')->first();

    $task = Task::factory()->create(['project_id' => $project->id, 'column_id' => $column1->id, 'position' => 1]);

    $response = $this->actingAs($user)->post(route('tasks.reorder', $project), [
        'column_id' => $column2->id,
        'tasks' => [
            ['id' => $task->id, 'position' => 1],
        ],
    ]);

    $response->assertRedirect();
    expect($task->fresh()->column_id)->toBe($column2->id);
});

it('prevents user from modifying tasks of other users projects', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    $project1 = Project::factory()->create(['user_id' => $user1->id]);
    $column1 = $project1->columns()->first();
    $task1 = Task::factory()->create(['project_id' => $project1->id, 'column_id' => $column1->id]);

    $response = $this->actingAs($user2)->patch(route('tasks.update', [$project1, $task1]), [
        'title' => 'Hacked',
        'priority' => 'high',
    ]);

    $response->assertStatus(403);
    expect($task1->fresh()->title)->not->toBe('Hacked');
});
