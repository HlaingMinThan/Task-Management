<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
use App\Models\TaskUser;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class TaskAssigneeController extends Controller
{
    public function users(Project $project, Request $request)
    {
        Gate::authorize('view', $project);

        $q = $request->query('query');

        $query = User::query();

        if ($q) {
            $query->where(function ($sub) use ($q) {
                $sub->where('name', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%");
            });
        }

        $users = $query->orderBy('name')->paginate(20)->through(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ];
        });

        return response()->json($users);
    }

    public function store(Project $project, Task $task, Request $request)
    {
        Gate::authorize('update', $task);

        if ($task->project_id !== $project->id) {
            abort(404);
        }

        $validated = $request->validate([
            'user_ids' => ['required', 'array'],
            'user_ids.*' => ['required', 'integer', 'exists:users,id'],
        ]);

        $userIds = $validated['user_ids'];

        // Remove assignees that are not present in the submitted list (sync behavior)
        $task->assignees()->whereNotIn('user_id', $userIds)->delete();

        foreach ($userIds as $userId) {
            TaskUser::firstOrCreate([
                'task_id' => $task->id,
                'user_id' => $userId,
            ], [
                'assigned_by' => $request->user()->id,
            ]);
        }

        $task->load('assignees.user');

        return response()->json(['assignees' => $task->assignees]);
    }

    public function destroy(Project $project, Task $task, User $user)
    {
        Gate::authorize('update', $task);

        if ($task->project_id !== $project->id) {
            abort(404);
        }

        $task->assignees()->where('user_id', $user->id)->delete();

        return response()->json(['success' => true]);
    }
}
