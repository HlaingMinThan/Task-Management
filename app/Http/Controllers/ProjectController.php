<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $projects = $request->user()
            ->projects()
            ->when($request->search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%");
            })
            ->latest('updated_at')
            ->get();

        return inertia('Dashboard', [
            'projects' => $projects,
            'filters' => $request->only(['search']),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProjectRequest $request)
    {
        $request->user()->projects()->create($request->validated());

        return redirect()->back();
    }

    /**
     * Display the specified resource.
     */
    public function show(Project $project)
    {
        Gate::authorize('view', $project);

        // Read filters from request safely
        $filters = request()->only(['q', 'status', 'assigned_user_id', 'due_from', 'due_to', 'overdue']);

        $q = $filters['q'] ?? null;
        $status = $filters['status'] ?? null;
        $assigned = $filters['assigned_user_id'] ?? null;
        $dueFrom = $filters['due_from'] ?? null;
        $dueTo = $filters['due_to'] ?? null;
        $overdue = isset($filters['overdue']) && $filters['overdue'];

        $project->load(['columns' => function ($query) use ($q, $status, $assigned, $dueFrom, $dueTo, $overdue) {
            $query->orderBy('position')->with(['tasks' => function ($query) use ($q, $status, $assigned, $dueFrom, $dueTo, $overdue) {
                // Only tasks that belong to this project (redundant but safe)
                $query->where('project_id', request()->route('project')->id)
                    ->orderBy('position')
                    ->when($q, fn ($q2) => $q2->search($q))
                    ->when($status, fn ($q2, $status) => $q2->where('column_id', $status))
                    ->when(isset($assigned) && $assigned !== '', function ($q2) use ($assigned) {
                        if ($assigned === 'me') {
                            $q2->assignedTo(auth()->id());
                        } else {
                            $q2->assignedTo($assigned);
                        }
                    })
                    ->when($dueFrom || $dueTo, function ($q2) use ($dueFrom, $dueTo) {
                        $q2->dueBetween($dueFrom, $dueTo);
                    })
                    ->when($overdue, fn ($q2) => $q2->overdue());
            }]);
        }]);

        return inertia('Board/Show', [
            'project' => $project,
            'filters' => $filters,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProjectRequest $request, Project $project)
    {
        Gate::authorize('update', $project);

        $project->update($request->validated());

        return redirect()->back();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project)
    {
        Gate::authorize('delete', $project);

        $project->delete();

        return redirect()->back();
    }
}
