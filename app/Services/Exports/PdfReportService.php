<?php

namespace App\Services\Exports;

use App\Models\Project;
use App\Models\Sprint;
use App\Models\Task;
use App\Models\TaskTimeEntry;
use App\Services\Boards\ScrumBoardService;
use App\Services\Reports\BurndownChartService;
use App\Services\Tracking\TaskStatusTrackingService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class PdfReportService
{
    public function __construct(private BurndownChartService $burndownService)
    {
    }

    public function downloadSprintSummary(Project $project, Sprint $sprint): Response
    {
        $dataset = $this->buildDataset($project, $sprint);
        $burndown = $this->burndownService->buildSeries($project, $sprint);
        $dataset['burndown_svg'] = $this->burndownService->renderSvg($burndown);

        $pdf = Pdf::loadView('exports.pdf.sprint-summary', $dataset)
            ->setPaper('letter', 'portrait');

        return $pdf->download($this->makeFilename($project, $sprint));
    }

    public function buildDataset(Project $project, Sprint $sprint): array
    {
        $tasks = Task::query()
            ->where('project_id', $project->id)
            ->where('sprint_id', $sprint->id)
            ->with([
                'assignees:id,name,apellido',
            ])
            ->orderBy('created_at')
            ->get();

        $statusCounts = $this->buildStatusCounts($tasks);
        $estimatedTotal = (float) $tasks->sum('estimated_hours');
        $completedEstimated = $tasks
            ->filter(fn (Task $task) => $this->isDone($task))
            ->sum('estimated_hours');

        $timeTotals = $this->timeTotalsByTask($project, $sprint);
        $realTotalSeconds = array_sum($timeTotals);

        $taskRows = $tasks->map(function (Task $task) use ($timeTotals) {
            $assignees = $task->assignees
                ->map(fn ($user) => trim($user->name . ' ' . $user->apellido))
                ->implode(', ');

            $realSeconds = (int) ($timeTotals[$task->id] ?? 0);

            return [
                'id' => $task->id,
                'title' => $task->title,
                'status' => $task->status,
                'priority' => $task->priority,
                'assignees' => $assignees,
                'estimated_hours' => $this->formatHours($task->estimated_hours),
                'real_hours' => $this->formatHours($realSeconds / 3600),
                'completed_at' => $task->completed_at?->format('Y-m-d H:i'),
            ];
        });

        return [
            'project' => $project,
            'sprint' => $sprint,
            'generated_at' => now(),
            'statuses' => ScrumBoardService::STATUSES,
            'status_counts' => $statusCounts,
            'tasks_total' => $tasks->count(),
            'estimated_total' => $this->formatHours($estimatedTotal),
            'estimated_completed' => $this->formatHours($completedEstimated),
            'real_total_hours' => $this->formatHours($realTotalSeconds / 3600),
            'real_total_seconds' => $realTotalSeconds,
            'tasks' => $taskRows,
        ];
    }

    public function makeFilename(Project $project, Sprint $sprint): string
    {
        $stamp = now()->format('Ymd');
        return "project_{$project->id}_sprint_{$sprint->id}_summary_{$stamp}.pdf";
    }

    private function buildStatusCounts(Collection $tasks): array
    {
        $counts = array_fill_keys(array_keys(ScrumBoardService::STATUSES), 0);

        foreach ($tasks as $task) {
            $status = $task->status;
            if (!array_key_exists($status, $counts)) {
                $counts[$status] = 0;
            }
            $counts[$status]++;
        }

        return $counts;
    }

    private function isDone(Task $task): bool
    {
        return $task->completed_at !== null
            || in_array($task->status, TaskStatusTrackingService::DONE_STATUSES, true);
    }

    private function timeTotalsByTask(Project $project, Sprint $sprint): array
    {
        return TaskTimeEntry::query()
            ->join('tasks', 'tasks.id', '=', 'task_time_entries.task_id')
            ->where('tasks.project_id', $project->id)
            ->where('tasks.sprint_id', $sprint->id)
            ->whereNotNull('task_time_entries.stopped_at')
            ->where('task_time_entries.duration_seconds', '>', 0)
            ->groupBy('task_time_entries.task_id')
            ->pluck(DB::raw('SUM(task_time_entries.duration_seconds) as total_seconds'), 'task_time_entries.task_id')
            ->map(fn ($value) => (int) $value)
            ->toArray();
    }

    private function formatHours($value): string
    {
        return number_format((float) $value, 2, '.', '');
    }
}
