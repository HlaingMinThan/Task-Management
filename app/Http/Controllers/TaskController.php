<?php

namespace App\Http\Controllers;

use App\Models\Column;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class TaskController extends Controller
{
    public function store(Request $request, Project $project, Column $column)
    {
        Gate::authorize('update', $column);

        if ($column->project_id !== $project->id) {
            abort(404);
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'priority' => ['required', 'in:low,medium,high'],
            'due_date' => ['nullable', 'date'],
        ]);

        $column->tasks()->create([
            'project_id' => $project->id,
            'title' => $validated['title'],
            'description' => $validated['description'],
            'priority' => $validated['priority'],
            'due_date' => $validated['due_date'],
            'position' => $column->tasks()->count() + 1,
        ]);

        return redirect()->back();
    }

    public function update(Request $request, Project $project, Task $task)
    {
        Gate::authorize('update', $task);

        if ($task->project_id !== $project->id) {
            abort(404);
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'priority' => ['required', 'in:low,medium,high'],
            'due_date' => ['nullable', 'date'],
            'column_id' => ['sometimes', 'required', 'exists:columns,id'],
        ]);

        if (isset($validated['column_id'])) {
            $column = \App\Models\Column::findOrFail($validated['column_id']);
            if ($column->project_id !== $project->id) {
                abort(403);
            }
        }

        $task->update($validated);

        return redirect()->back();
    }

    public function destroy(Project $project, Task $task)
    {
        Gate::authorize('delete', $task);

        if ($task->project_id !== $project->id) {
            abort(404);
        }

        $task->delete();

        return redirect()->back();
    }

    public function reorder(Request $request, Project $project)
    {
        Gate::authorize('view', $project);

        $validated = $request->validate([
            'column_id' => ['required', 'exists:columns,id'],
            'tasks' => ['required', 'array'],
            'tasks.*.id' => ['required', 'exists:tasks,id'],
            'tasks.*.position' => ['required', 'integer'],
        ]);

        $column = Column::findOrFail($validated['column_id']);

        if ($column->project_id !== $project->id) {
            abort(403);
        }

        foreach ($validated['tasks'] as $taskData) {
            Task::where('id', $taskData['id'])
                ->where('project_id', $project->id)
                ->update([
                    'column_id' => $column->id,
                    'position' => $taskData['position'],
                ]);
        }

        return redirect()->back();
    }
}
