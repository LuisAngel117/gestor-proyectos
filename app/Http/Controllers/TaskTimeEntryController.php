<?php

namespace App\Http\Controllers;

use App\Http\Requests\TimeEntries\DestroyTaskTimeEntryRequest;
use App\Http\Requests\TimeEntries\StoreTaskTimeEntryRequest;
use App\Http\Requests\TimeEntries\UpdateTaskTimeEntryRequest;
use App\Models\Project;
use App\Models\Task;
use App\Models\TaskTimeEntry;
use App\Models\User;
use App\Notifications\TaskTimeLoggedNotification;
use App\Services\TimeTracking\TimeEntryValidationService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class TaskTimeEntryController extends Controller
{
    public function __construct(private TimeEntryValidationService $validationService)
    {
    }

    public function index(Project $project, Task $task): JsonResponse
    {
        $this->ensureProjectTaskConsistency($project, $task);
        $this->authorize('view', $task);

        return response()->json([
            'task_id' => $task->id,
            'entries' => $task->timeEntries()
                ->latest('started_at')
                ->get(),
        ]);
    }

    public function store(
        StoreTaskTimeEntryRequest $request,
        Project $project,
        Task $task
    ): JsonResponse|RedirectResponse {
        $this->ensureProjectTaskConsistency($project, $task);
        $this->authorize('trackTime', $task);

        $data = $request->validated();
        $this->ensureEntryOwnership($request->user(), $task, (int) $data['user_id']);

        $start = Carbon::parse($data['started_at']);
        $end = Carbon::parse($data['stopped_at']);
        $duration = $this->validationService->calculateDurationSeconds($start, $end);

        $entry = TaskTimeEntry::create([
            'task_id' => $task->id,
            'user_id' => (int) $data['user_id'],
            'started_at' => $start,
            'stopped_at' => $end,
            'duration_seconds' => $duration,
            'source' => 'manual',
            'note' => $data['note'] ?? null,
            'created_by' => $request->user()->id,
        ]);

        $this->notifyTaskCreator($task, $entry, $request->user()->id);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Entrada manual creada.',
                'entry' => $entry,
            ], 201);
        }

        return back()->with('success', 'Entrada manual creada.');
    }

    public function update(
        UpdateTaskTimeEntryRequest $request,
        Project $project,
        Task $task,
        TaskTimeEntry $timeEntry
    ): JsonResponse|RedirectResponse {
        $this->ensureProjectTaskConsistency($project, $task);
        $this->ensureTaskEntryConsistency($task, $timeEntry);
        $this->authorize('trackTime', $task);

        $data = $request->validated();
        $targetUserId = (int) ($data['user_id'] ?? $timeEntry->user_id);
        $this->ensureEntryOwnership($request->user(), $task, $targetUserId, $timeEntry);

        $start = Carbon::parse($data['started_at']);
        $end = Carbon::parse($data['stopped_at']);
        $duration = $this->validationService->calculateDurationSeconds($start, $end);

        $timeEntry->update([
            'user_id' => $targetUserId,
            'started_at' => $start,
            'stopped_at' => $end,
            'duration_seconds' => $duration,
            'note' => $data['note'] ?? $timeEntry->note,
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Entrada manual actualizada.',
                'entry' => $timeEntry->refresh(),
            ]);
        }

        return back()->with('success', 'Entrada manual actualizada.');
    }

    public function destroy(
        DestroyTaskTimeEntryRequest $request,
        Project $project,
        Task $task,
        TaskTimeEntry $timeEntry
    ): JsonResponse|RedirectResponse {
        $this->ensureProjectTaskConsistency($project, $task);
        $this->ensureTaskEntryConsistency($task, $timeEntry);
        $this->authorize('trackTime', $task);
        $this->ensureEntryOwnership($request->user(), $task, $timeEntry->user_id, $timeEntry);

        $timeEntry->delete();

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Entrada manual eliminada.',
            ]);
        }

        return back()->with('success', 'Entrada manual eliminada.');
    }

    private function ensureProjectTaskConsistency(Project $project, Task $task): void
    {
        if ($task->project_id !== $project->id) {
            abort(404);
        }
    }

    private function ensureTaskEntryConsistency(Task $task, TaskTimeEntry $timeEntry): void
    {
        if ($timeEntry->task_id !== $task->id) {
            abort(404);
        }
    }

    private function ensureEntryOwnership(
        User $user,
        Task $task,
        int $targetUserId,
        ?TaskTimeEntry $timeEntry = null
    ): void {
        $projectRole = $user->roleInProject($task->project_id);
        $teamRole = $user->roleInTeam($task->project->team_id);

        if (
            $projectRole === 'member'
            && !in_array($teamRole, ['owner', 'admin'], true)
            && $targetUserId !== $user->id
        ) {
            abort(403);
        }

        if (
            $timeEntry
            && $projectRole === 'member'
            && !in_array($teamRole, ['owner', 'admin'], true)
            && $timeEntry->user_id !== $user->id
        ) {
            abort(403);
        }
    }

    private function notifyTaskCreator(Task $task, TaskTimeEntry $entry, int $actorId): void
    {
        $creatorId = $task->created_by;
        if (!$creatorId || (int) $creatorId === (int) $actorId) {
            return;
        }

        $creator = $task->creator ?? User::query()->find($creatorId);
        if (!$creator) {
            return;
        }

        DB::afterCommit(function () use ($creator, $task, $entry, $actorId) {
            $creator->notify(new TaskTimeLoggedNotification($task, $entry, $actorId));
        });
    }
}
