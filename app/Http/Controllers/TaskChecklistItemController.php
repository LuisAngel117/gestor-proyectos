<?php

namespace App\Http\Controllers;

use App\Http\Requests\TaskChecklist\ReorderChecklistItemsRequest;
use App\Http\Requests\TaskChecklist\StoreChecklistItemRequest;
use App\Http\Requests\TaskChecklist\UpdateChecklistItemRequest;
use App\Models\Project;
use App\Models\Task;
use App\Models\TaskChecklistItem;
use App\Models\TaskTimeEntry;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TaskChecklistItemController extends Controller
{
    public function store(
        Project $project,
        Task $task,
        StoreChecklistItemRequest $request
    ): RedirectResponse|\Illuminate\Http\JsonResponse {
        $this->ensureProjectTaskConsistency($project, $task);
        $this->authorize('update', $task);

        $position = (int) $task->checklistItems()->max('position');
        $position = $position > 0 ? $position + 1 : 1;

        $isCompleted = $request->boolean('is_completed');

        $item = $task->checklistItems()->create([
            'content' => $request->string('content')->toString(),
            'position' => $position,
            'is_completed' => $isCompleted,
            'completed_at' => $isCompleted ? now() : null,
            'completed_by' => $isCompleted ? Auth::id() : null,
            'created_by' => Auth::id(),
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Checklist agregado.',
                'item' => $item,
            ], 201);
        }

        return back()->with('success', 'Checklist agregado.');
    }

    public function update(
        Project $project,
        Task $task,
        TaskChecklistItem $item,
        UpdateChecklistItemRequest $request
    ): RedirectResponse|\Illuminate\Http\JsonResponse {
        $this->ensureProjectTaskConsistency($project, $task);
        $this->ensureTaskChecklistConsistency($task, $item);
        $onlyToggle = $request->has('is_completed') && !$request->filled('content');
        if ($onlyToggle) {
            $this->authorize('toggleChecklist', $task);
        } else {
            $this->authorize('update', $task);
        }

        $requiresTimer = $request->has('is_completed')
            && $request->boolean('is_completed')
            && !$request->user()->can('update', $task);
        if ($requiresTimer) {
            $userId = (int) $request->user()->id;
            if (!$this->hasActiveTimerForUser($task, $userId)) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'message' => 'Debes iniciar el timer antes de completar el checklist.',
                    ], 422);
                }

                return back()->withErrors([
                    'timer' => 'Debes iniciar el timer antes de completar el checklist.',
                ]);
            }
        }

        $payload = [];

        if ($request->filled('content')) {
            $payload['content'] = $request->string('content')->toString();
        }

        if ($request->has('is_completed')) {
            $isCompleted = $request->boolean('is_completed');
            $payload['is_completed'] = $isCompleted;
            $payload['completed_at'] = $isCompleted ? now() : null;
            $payload['completed_by'] = $isCompleted ? Auth::id() : null;
        }

        if ($payload) {
            $item->update($payload);
        }
        if ($payload) {
            AuditLogger::log($request->user(), 'checklist.update', $item, [
                'task_id' => $task->id,
                'completed' => $item->is_completed,
            ]);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Checklist actualizado.',
                'item' => $item->refresh(),
            ]);
        }

        return back()->with('success', 'Checklist actualizado.');
    }

    public function destroy(Project $project, Task $task, TaskChecklistItem $item): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $this->ensureProjectTaskConsistency($project, $task);
        $this->ensureTaskChecklistConsistency($task, $item);
        $this->authorize('update', $task);

        $item->delete();
        $this->normalizePositions($task);
        AuditLogger::log(request()->user(), 'checklist.delete', $item, [
            'task_id' => $task->id,
        ]);

        if (request()->expectsJson()) {
            return response()->json([
                'message' => 'Checklist eliminado.',
            ]);
        }

        return back()->with('success', 'Checklist eliminado.');
    }

    public function reorder(
        Project $project,
        Task $task,
        ReorderChecklistItemsRequest $request
    ): RedirectResponse|\Illuminate\Http\JsonResponse {
        $this->ensureProjectTaskConsistency($project, $task);
        $this->authorize('update', $task);

        $orderedIds = $request->validated()['ordered_ids'];
        $items = $task->checklistItems()->orderBy('position')->get();

        $existingIds = $items->pluck('id')->map(fn ($id) => (int) $id)->all();
        $providedIds = array_map('intval', $orderedIds);

        sort($existingIds);
        $sortedProvided = $providedIds;
        sort($sortedProvided);

        if ($existingIds != $sortedProvided) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'El reordenamiento debe incluir todos los items del checklist.',
                ], 422);
            }
            return back()->withErrors([
                'ordered_ids' => 'El reordenamiento debe incluir todos los items del checklist.',
            ]);
        }

        DB::transaction(function () use ($items, $orderedIds) {
            foreach ($orderedIds as $index => $itemId) {
                $item = $items->firstWhere('id', (int) $itemId);
                if ($item) {
                    $item->update(['position' => $index + 1]);
                }
            }
        });

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Checklist reordenado.',
                'ordered_ids' => $orderedIds,
            ]);
        }

        return back()->with('success', 'Checklist reordenado.');
    }

    private function ensureProjectTaskConsistency(Project $project, Task $task): void
    {
        if ($task->project_id !== $project->id) {
            abort(404);
        }
    }

    private function ensureTaskChecklistConsistency(Task $task, TaskChecklistItem $item): void
    {
        if ($item->task_id !== $task->id) {
            abort(404);
        }
    }

    private function normalizePositions(Task $task): void
    {
        $items = $task->checklistItems()->orderBy('position')->get();

        DB::transaction(function () use ($items) {
            foreach ($items as $index => $item) {
                $item->update(['position' => $index + 1]);
            }
        });
    }

    private function hasActiveTimerForUser(Task $task, int $userId): bool
    {
        return TaskTimeEntry::query()
            ->where('task_id', $task->id)
            ->where('user_id', $userId)
            ->whereNull('stopped_at')
            ->exists();
    }
}
