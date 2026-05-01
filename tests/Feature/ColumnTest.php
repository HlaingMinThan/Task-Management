<?php

use App\Models\Column;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('creates default columns when a project is created', function () {
    $user = User::factory()->create();

    $project = Project::factory()->create([
        'user_id' => $user->id,
    ]);

    expect($project->columns)->toHaveCount(3);
    expect($project->columns->pluck('title')->toArray())->toEqual(['To Do', 'Doing', 'Done']);
});

it('allows user to create a column', function () {
    $user = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->post(route('columns.store', $project), [
        'title' => 'QA Testing',
    ]);

    $response->assertRedirect();
    expect($project->columns()->where('title', 'QA Testing')->exists())->toBeTrue();

    // Check if the position is assigned correctly (To Do, Doing, Done + QA Testing)
    $column = $project->columns()->where('title', 'QA Testing')->first();
    expect($column->position)->toBe(4);
});

it('allows user to rename a column', function () {
    $user = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $user->id]);
    $column = $project->columns()->first();

    $response = $this->actingAs($user)->patch(route('columns.update', [$project, $column]), [
        'title' => 'Backlog',
    ]);

    $response->assertRedirect();
    expect($column->fresh()->title)->toBe('Backlog');
});

it('allows user to delete a column', function () {
    $user = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $user->id]);
    $column = $project->columns()->first();

    $response = $this->actingAs($user)->delete(route('columns.destroy', [$project, $column]));

    $response->assertRedirect();
    expect(Column::find($column->id))->toBeNull();
});

it('prevents user from modifying columns of other users projects', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    $project1 = Project::factory()->create(['user_id' => $user1->id]);
    $column1 = $project1->columns()->first();

    // User 2 tries to rename User 1's column
    $response = $this->actingAs($user2)->patch(route('columns.update', [$project1, $column1]), [
        'title' => 'Hacked',
    ]);

    $response->assertStatus(403);
    expect($column1->fresh()->title)->not->toBe('Hacked');
});

it('allows user to reorder columns', function () {
    $user = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $user->id]);

    // Default columns: To Do (1), Doing (2), Done (3)
    $columns = $project->columns()->orderBy('position')->get();
    $todo = $columns[0];
    $doing = $columns[1];
    $done = $columns[2];

    $response = $this->actingAs($user)->post(route('columns.reorder', $project), [
        'columns' => [
            ['id' => $todo->id, 'position' => 3],
            ['id' => $doing->id, 'position' => 1],
            ['id' => $done->id, 'position' => 2],
        ],
    ]);

    $response->assertRedirect();

    expect($todo->fresh()->position)->toBe(3);
    expect($doing->fresh()->position)->toBe(1);
    expect($done->fresh()->position)->toBe(2);
});
