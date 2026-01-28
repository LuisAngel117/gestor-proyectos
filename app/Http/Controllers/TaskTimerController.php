<?php

namespace App\Http\Controllers;

use App\Http\Requests\TaskTimer\StartTaskTimerRequest;
use App\Http\Requests\TaskTimer\StopTaskTimerRequest;
use App\Models\Project;
use App\Models\Task;
use App\Models\TaskTimeEntry;
use App\Models\User;
use App\Notifications\TaskTimeLoggedNotification;
use App\Services\Tracking\TaskStatusTrackingService;
use App\Services\TimeTracking\TaskTimerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class TaskTimerController extends Controller
{
    public function __construct(
        private TaskTimerService $timerService,
        private TaskStatusTrackingService $trackingService
    )
    {
    }

    public function show(Project $project, Task $task): JsonResponse
    {
        $this->ensureProjectTaskConsistency($project, $task);
        $this->authorize('view', $task);

        $user = request()->user();
        $activeForTask = $this->timerService->getActiveForUserAndTask($user, $task);
        $activeForUser = $this->timerService->getActiveForUser($user);

        return response()->json([
            'task_id' => $task->id,
            'active' => $activeForTask !== null,
            'active_entry' => $activeForTask,
            'active_entry_for_user' => $activeForUser,
        ]);
    }

    public function start(StartTaskTimerRequest $request, Project $project, Task $task): JsonResponse|RedirectResponse
    {
        $this->ensureProjectTaskConsistency($project, $task);
        $this->authorize('trackTime', $task);

        try {
            $entry = $this->timerService->start($task, $request->user());
        } catch (RuntimeException $exception) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => $exception->getMessage(),
                ], 409);
            }

            return back()->withErrors([
                'timer' => $exception->getMessage(),
            ]);
        }

        if ($task->status !== 'hecho' && $task->status !== 'en_progreso') {
            $this->trackingService->recordTransition($task, 'en_progreso', $request->user());
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Temporizador iniciado.',
                'entry' => $entry,
            ], 201);
        }

        return back()->with('success', 'Temporizador iniciado.');
    }

    public function stop(StopTaskTimerRequest $request, Project $project, Task $task): JsonResponse|RedirectResponse
    {
        $this->ensureProjectTaskConsistency($project, $task);
        $this->authorize('trackTime', $task);

        try {
            $entry = $this->timerService->stop($task, $request->user());
        } catch (RuntimeException $exception) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => $exception->getMessage(),
                ], 409);
            }

            return back()->withErrors([
                'timer' => $exception->getMessage(),
            ]);
        }

        if ($task->status !== 'hecho') {
            $this->trackingService->recordTransition($task, 'hecho', $request->user());
        }

        $this->notifyTaskCreator($task, $entry, $request->user()->id);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Temporizador detenido.',
                'entry' => $entry,
            ]);
        }

        return back()->with('success', 'Temporizador detenido.');
    }

    private function ensureProjectTaskConsistency(Project $project, Task $task): void
    {
        if ($task->project_id !== $project->id) {
            abort(404);
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
