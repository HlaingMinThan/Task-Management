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
        $projects = Project::query()
            ->where(function ($query) use ($request) {
                $query->where('user_id', $request->user()->id)
                    ->orWhereHas('members', function ($memberQuery) use ($request) {
                        $memberQuery->where('user_id', $request->user()->id)
                            ->where('status', 'active');
                    });
            })
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

        $project->load(['columns' => function ($query) {
            $query->orderBy('position')->with(['tasks' => function ($query) {
                $query->orderBy('position');
            }]);
        }]);

        return inertia('Board/Show', [
            'project' => $project,
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
