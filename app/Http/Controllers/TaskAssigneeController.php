<?php

namespace App\Http\Controllers;

use App\Http\Requests\TaskAssignees\AssignTaskAssigneesRequest;
use App\Http\Requests\TaskAssignees\UnassignTaskAssigneeRequest;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Notifications\TaskAssignedNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class TaskAssigneeController extends Controller
{
    public function index(Project $project, Task $task): JsonResponse
    {
        $this->ensureProjectTaskConsistency($project, $task);
        $this->authorize('view', $task);

        $assignees = $task->assignees()
            ->withPivot(['assigned_by', 'assigned_at'])
            ->get(['users.id', 'users.name', 'users.apellido', 'users.email']);

        return response()->json([
            'task_id' => $task->id,
            'assignees' => $assignees,
        ]);
    }

    public function store(
        Project $project,
        Task $task,
        AssignTaskAssigneesRequest $request
    ): RedirectResponse|\Illuminate\Http\JsonResponse {
        $this->ensureProjectTaskConsistency($project, $task);
        $this->authorize('update', $task);

        $userIds = array_values(array_unique(array_map('intval', $request->validated()['user_ids'])));

        $alreadyAssigned = $task->assignees()
            ->whereIn('users.id', $userIds)
            ->pluck('users.id')
            ->all();

        $newUserIds = array_values(array_diff($userIds, $alreadyAssigned));

        if (empty($newUserIds)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'No hay nuevos usuarios para asignar.',
                    'task_id' => $task->id,
                ]);
            }
            return back()->with('success', 'No hay nuevos usuarios para asignar.');
        }

        $actorId = $request->user()->id;
        $assignedAt = now();
        $pivotData = [];

        foreach ($newUserIds as $userId) {
            $pivotData[$userId] = [
                'assigned_by' => $actorId,
                'assigned_at' => $assignedAt,
            ];
        }

        $task->assignees()->syncWithoutDetaching($pivotData);

        $assignees = User::query()
            ->whereIn('id', $newUserIds)
            ->get();

        DB::afterCommit(function () use ($assignees, $task, $actorId, $assignedAt) {
            foreach ($assignees as $assignee) {
                $assignee->notify(new TaskAssignedNotification($task, $actorId, $assignedAt));
            }
        });

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Asignaciones actualizadas.',
                'task_id' => $task->id,
                'assignee_ids' => $newUserIds,
            ]);
        }

        return back()->with('success', 'Asignaciones actualizadas.');
    }

    public function destroy(
        Project $project,
        Task $task,
        User $user,
        UnassignTaskAssigneeRequest $request
    ): RedirectResponse|\Illuminate\Http\JsonResponse {
        $this->ensureProjectTaskConsistency($project, $task);
        $this->authorize('update', $task);

        $isAssigned = $task->assignees()->whereKey($user->id)->exists();
        if (!$isAssigned) {
            abort(404);
        }

        $task->assignees()->detach($user->id);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Usuario desasignado.',
                'task_id' => $task->id,
                'user_id' => $user->id,
            ]);
        }

        return back()->with('success', 'Usuario desasignado.');
    }

    private function ensureProjectTaskConsistency(Project $project, Task $task): void
    {
        if ($task->project_id !== $project->id) {
            abort(404);
        }
    }
}
