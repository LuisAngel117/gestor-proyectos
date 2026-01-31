<?php

namespace App\Http\Controllers;

use App\Http\Requests\Task\StoreTaskRequest;
use App\Http\Requests\Task\UpdateTaskRequest;
use App\Models\Project;
use App\Models\Task;
use App\Models\TaskTimeEntry;
use App\Models\TaskStatusEvent;
use App\Services\Boards\ScrumBoardService;
use App\Services\Tracking\TaskStatusTrackingService;
use App\Support\AuditLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class TaskController extends Controller
{
    public function __construct(private TaskStatusTrackingService $trackingService)
    {
    }

    public function index(Request $request, Project $project): JsonResponse|View
    {
        $this->authorize('viewAny', [Task::class, $project]);

        $query = Task::query()
            ->where('project_id', $project->id)
            ->with([
                'assignees:id,name,apellido',
                'sprint:id,name',
                'backlogItem:id,name',
                'parent:id,title',
            ])
            ->withCount(['comments', 'checklistItems', 'attachments', 'timeEntries'])
            ->orderByDesc('created_at');

        $projectRole = $request->user()->roleInProject($project->id);
        $teamRole = $request->user()->roleInTeam($project->team_id);
        if (
            in_array($projectRole, ['member', 'observer'], true)
            && !in_array($teamRole, ['owner', 'admin', 'observer'], true)
        ) {
            $query->whereHas('assignees', function ($assigneeQuery) use ($request) {
                $assigneeQuery->where('users.id', $request->user()->id);
            });
        }

        $this->applyFilters($request, $project, $query);

        $perPage = (int) $request->input('per_page', 20);
        $tasks = $query->paginate($perPage)->appends($request->query());

        if ($request->expectsJson()) {
            return response()->json($tasks);
        }

        return view('tasks.index', [
            'project' => $project,
            'tasks' => $tasks,
            'statuses' => ScrumBoardService::STATUSES,
            'doneStatuses' => TaskStatusTrackingService::DONE_STATUSES,
            'priorities' => ['baja', 'media', 'alta', 'urgente'],
            'sprints' => $project->sprints()->orderByDesc('start_date')->get(),
            'assignees' => $project->members()->orderBy('name')->get(),
            'parentOptions' => Task::query()
                ->where('project_id', $project->id)
                ->whereNull('parent_id')
                ->orderBy('title')
                ->get(),
            'filters' => [
                'status' => $request->input('status'),
                'sprint' => $request->input('sprint', $request->input('sprint_id')),
                'assignee' => $request->input('assignee'),
            ],
        ]);
    }

    public function myTasks(Request $request): View
    {
        $user = $request->user();

        $tasks = Task::query()
            ->whereHas('assignees', function ($query) use ($user) {
                $query->where('users.id', $user->id);
            })
            ->with([
                'project:id,name,team_id',
                'project.team:id,name',
                'sprint:id,name',
            ])
            ->orderByDesc('updated_at')
            ->paginate(20);

        return view('tasks.my', [
            'tasks' => $tasks,
            'statuses' => ScrumBoardService::STATUSES,
        ]);
    }

    public function store(StoreTaskRequest $request, Project $project): JsonResponse|\Illuminate\Http\RedirectResponse
    {
        $data = $request->validated();
        $this->authorize('create', [Task::class, $project, $data['due_date'] ?? null]);
        if ((int) $data['project_id'] !== (int) $project->id) {
            abort(404);
        }

        $now = now();
        $status = 'todo';
        $completedAt = in_array($status, TaskStatusTrackingService::DONE_STATUSES, true)
            ? $now
            : null;

        $task = Task::create([
            'project_id' => $project->id,
            'sprint_id' => $data['sprint_id'] ?? null,
            'backlog_item_id' => $data['backlog_item_id'] ?? null,
            'parent_id' => $data['parent_id'] ?? null,
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'status' => $status,
            'status_changed_at' => $now,
            'completed_at' => $completedAt,
            'priority' => $data['priority'],
            'due_date' => $data['due_date'] ?? null,
            'estimated_hours' => $data['estimated_hours'] ?? null,
            'created_by' => $request->user()->id,
        ]);
        AuditLogger::log($request->user(), 'task.create', $task, [
            'project' => $project->name,
        ]);

        TaskStatusEvent::create([
            'task_id' => $task->id,
            'project_id' => $project->id,
            'from_status' => null,
            'to_status' => $status,
            'changed_by' => $request->user()->id,
            'changed_at' => $now,
        ]);

        $task->load(['assignees:id,name,apellido', 'sprint:id,name', 'backlogItem:id,name', 'parent:id,title']);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Tarea creada.',
                'task' => $task,
            ], 201);
        }

        return redirect()
            ->route('tasks.show', [$project, $task])
            ->with('success', 'Tarea creada.');
    }

    public function show(Project $project, Task $task): JsonResponse|View
    {
        $this->ensureProjectTaskConsistency($project, $task);
        $this->authorize('view', $task);

        $task->load([
            'assignees:id,name,apellido',
            'sprint:id,name',
            'backlogItem:id,name',
            'parent:id,title',
            'subtasks:id,title,parent_id,status',
        ])->loadCount(['comments', 'checklistItems', 'attachments', 'timeEntries']);

        if (request()->expectsJson()) {
            return response()->json([
                'task' => $task,
            ]);
        }

        $task->load([
            'checklistItems',
            'comments.author',
            'comments.editor',
            'attachments.uploader',
            'timeEntries.user',
            'prerequisites:id,title',
            'dependents:id,title',
        ]);

          $viewer = request()->user();
          $activeTimerEntry = TaskTimeEntry::query()
            ->where('task_id', $task->id)
            ->where('user_id', $viewer->id)
            ->whereNull('stopped_at')
            ->latest('started_at')
            ->first();

          $activeTimerForUser = TaskTimeEntry::query()
            ->where('user_id', $viewer->id)
            ->whereNull('stopped_at')
            ->with(['task:id,title,project_id', 'task.project:id,name'])
            ->latest('started_at')
            ->first();
          $hasActiveTimerEntry = $activeTimerEntry !== null;

          $assignedIds = $task->assignees->pluck('id')->map(fn ($id) => (int) $id)->all();
          $availableMembers = $project->members()
              ->whereNotIn('users.id', $assignedIds)
              ->orderBy('name')
              ->get();

          return view('tasks.show', [
              'project' => $project,
              'task' => $task,
              'statuses' => ScrumBoardService::STATUSES,
              'priorities' => ['baja', 'media', 'alta', 'urgente'],
              'sprints' => $project->sprints()->orderByDesc('start_date')->get(),
              'members' => $availableMembers,
              'activeTimerEntry' => $activeTimerEntry,
              'hasActiveTimerEntry' => $hasActiveTimerEntry,
              'activeTimerForUser' => $activeTimerForUser,
              'availableTasks' => Task::query()
                  ->where('project_id', $project->id)
                ->where('id', '!=', $task->id)
                ->orderBy('title')
                ->get(),
        ]);
    }

    public function update(
        UpdateTaskRequest $request,
        Project $project,
        Task $task
    ): JsonResponse|\Illuminate\Http\RedirectResponse {
        $this->ensureProjectTaskConsistency($project, $task);

        $data = $request->validated();
        $this->authorize('update', [$task, $data['due_date'] ?? null]);
        if ((int) $data['project_id'] !== (int) $project->id) {
            abort(404);
        }

        $status = $data['status'];
        $payload = $data;
        unset($payload['project_id'], $payload['status']);

        DB::transaction(function () use ($task, $status, $payload, $request) {
            if ($task->status !== $status) {
                $this->trackingService->recordTransition($task, $status, $request->user());
            }

            if (!empty($payload)) {
                $task->update($payload);
            }
        });

        $task->refresh()->load([
            'assignees:id,name,apellido',
            'sprint:id,name',
            'backlogItem:id,name',
            'parent:id,title',
            'subtasks:id,title,parent_id,status',
        ])->loadCount(['comments', 'checklistItems', 'attachments', 'timeEntries']);

        AuditLogger::log($request->user(), 'task.update', $task, [
            'project' => $project->name,
            'status' => $task->status,
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Tarea actualizada.',
                'task' => $task,
            ]);
        }

        return redirect()
            ->route('tasks.show', [$project, $task])
            ->with('success', 'Tarea actualizada.');
    }

    public function destroy(Project $project, Task $task): JsonResponse|\Illuminate\Http\RedirectResponse
    {
        $this->ensureProjectTaskConsistency($project, $task);
        $this->authorize('delete', $task);

        $task->delete();
        AuditLogger::log(request()->user(), 'task.delete', $task, [
            'project' => $project->name,
        ]);

        if (request()->expectsJson()) {
            return response()->json([
                'message' => 'Tarea eliminada.',
            ]);
        }

        return redirect()
            ->route('tasks.index', $project)
            ->with('success', 'Tarea eliminada.');
    }

    private function ensureProjectTaskConsistency(Project $project, Task $task): void
    {
        if ($task->project_id !== $project->id) {
            abort(404);
        }
    }

    private function applyFilters(Request $request, Project $project, $query): void
    {
        $status = $request->input('status');
        if ($status) {
            $query->where('status', $status);
        }

        $sprintFilter = $request->input('sprint', $request->input('sprint_id'));
        if ($sprintFilter === 'backlog') {
            $query->whereNull('sprint_id');
        } elseif (is_string($sprintFilter) && ctype_digit($sprintFilter)) {
            $exists = $project->sprints()->whereKey($sprintFilter)->exists();
            if (!$exists) {
                abort(404);
            }
            $query->where('sprint_id', (int) $sprintFilter);
        }

        $assignee = $request->input('assignee');
        if ($assignee) {
            $assigneeId = (int) $assignee;
            $isMember = $project->members()->where('users.id', $assigneeId)->exists();
            if (!$isMember) {
                abort(404);
            }

            $query->whereHas('assignees', function ($subQuery) use ($assigneeId) {
                $subQuery->where('users.id', $assigneeId);
            });
        }

        $parentId = $request->input('parent_id');
        if ($parentId !== null && $parentId !== '') {
            $query->where('parent_id', (int) $parentId);
        }
    }
}
