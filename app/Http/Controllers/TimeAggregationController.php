<?php

namespace App\Http\Controllers;

use App\Http\Requests\TimeAggregation\SprintTimeSummaryRequest;
use App\Http\Requests\TimeAggregation\TaskTimeSummaryRequest;
use App\Models\Project;
use App\Models\Sprint;
use App\Models\Task;
use App\Services\TimeTracking\TimeAggregationService;
use Illuminate\Http\JsonResponse;

class TimeAggregationController extends Controller
{
    public function __construct(private TimeAggregationService $aggregationService)
    {
    }

    public function taskSummary(
        TaskTimeSummaryRequest $request,
        Project $project,
        Task $task
    ): JsonResponse {
        $this->ensureProjectTaskConsistency($project, $task);
        $this->authorize('view', $task);

        $includeSubtasks = $this->toBoolean($request->input('include_subtasks', false));
        $includeRunning = $this->toBoolean($request->input('include_running', false));

        $summary = $this->aggregationService->taskSummary($task, $includeSubtasks, $includeRunning);

        return response()->json($summary);
    }

    public function sprintSummary(
        SprintTimeSummaryRequest $request,
        Project $project,
        Sprint $sprint
    ): JsonResponse {
        $this->ensureProjectSprintConsistency($project, $sprint);
        $this->authorize('view', $sprint);

        $includeRunning = $this->toBoolean($request->input('include_running', false));
        $groupByUser = $this->toBoolean($request->input('group_by_user', false));

        $summary = $this->aggregationService->sprintSummary($sprint, $includeRunning, $groupByUser);

        return response()->json($summary);
    }

    private function ensureProjectTaskConsistency(Project $project, Task $task): void
    {
        if ($task->project_id !== $project->id) {
            abort(404);
        }
    }

    private function ensureProjectSprintConsistency(Project $project, Sprint $sprint): void
    {
        if ($sprint->project_id !== $project->id) {
            abort(404);
        }
    }

    private function toBoolean(mixed $value): bool
    {
        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }
}
