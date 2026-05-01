<?php

namespace App\Http\Controllers;

use App\Models\Column;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ColumnController extends Controller
{
    public function store(Request $request, Project $project)
    {
        Gate::authorize('view', $project);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:100'],
        ]);

        $maxPosition = $project->columns()->max('position') ?? 0;

        $project->columns()->create([
            'title' => $validated['title'],
            'position' => $maxPosition + 1,
        ]);

        return back();
    }

    public function update(Request $request, Project $project, Column $column)
    {
        Gate::authorize('update', $column);

        if ($column->project_id !== $project->id) {
            abort(404);
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:100'],
        ]);

        $column->update($validated);

        return back();
    }

    public function destroy(Project $project, Column $column)
    {
        Gate::authorize('delete', $column);

        if ($column->project_id !== $project->id) {
            abort(404);
        }

        $column->delete();

        return back();
    }

    public function reorder(Request $request, Project $project)
    {
        Gate::authorize('view', $project);

        $validated = $request->validate([
            'columns' => ['required', 'array'],
            'columns.*.id' => ['required', 'exists:columns,id'],
            'columns.*.position' => ['required', 'integer'],
        ]);

        foreach ($validated['columns'] as $columnData) {
            $project->columns()->where('id', $columnData['id'])->update([
                'position' => $columnData['position'],
            ]);
        }

        return back();
    }
}
