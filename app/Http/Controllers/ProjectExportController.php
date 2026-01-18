<?php

namespace App\Http\Controllers;

use App\Exports\ProjectTasksExport;
use App\Exports\ProjectTimeEntriesExport;
use App\Exports\ProjectWorkloadExport;
use App\Http\Requests\Exports\ExportTasksCsvRequest;
use App\Http\Requests\Exports\ExportTimeEntriesCsvRequest;
use App\Http\Requests\Exports\ExportWorkloadCsvRequest;
use App\Models\Project;
use App\Models\Sprint;
use App\Models\Task;
use App\Services\Dashboard\ProjectDashboardMetricsService;
use App\Services\Exports\CsvExportService;
use Carbon\Carbon;

class ProjectExportController extends Controller
{
    public function __construct(
        private CsvExportService $exportService,
        private ProjectDashboardMetricsService $metricsService
    ) {
    }

    public function tasks(ExportTasksCsvRequest $request, Project $project)
    {
        $this->authorize('view', $project);

        $filters = $this->normalizeTaskFilters($project, $request->validated());
        $export = new ProjectTasksExport($project, $filters);
        $filename = $this->exportService->fileName($project, 'tasks');

        return $this->exportService->download($export, $filename);
    }

    public function timeEntries(ExportTimeEntriesCsvRequest $request, Project $project)
    {
        $this->authorize('view', $project);

        $data = $request->validated();
        $from = Carbon::parse($data['from'])->startOfDay();
        $to = Carbon::parse($data['to'])->endOfDay();

        $filters = [
            'task_id' => $data['task_id'] ?? null,
            'user_id' => $data['user_id'] ?? null,
            'source' => $data['source'] ?? null,
        ];

        if (!empty($filters['task_id']) && !$this->taskBelongsToProject($project, (int) $filters['task_id'])) {
            abort(404);
        }

        if (!empty($filters['user_id']) && !$this->userBelongsToProject($project, (int) $filters['user_id'])) {
            abort(404);
        }

        $export = new ProjectTimeEntriesExport($project, $from, $to, $filters);
        $filename = $this->exportService->fileName($project, 'time_entries', [
            $from->format('Ymd'),
            $to->format('Ymd'),
        ]);

        return $this->exportService->download($export, $filename);
    }

    public function workload(ExportWorkloadCsvRequest $request, Project $project)
    {
        $this->authorize('view', $project);

        $sprintFilter = $request->validated()['sprint'] ?? 'active';
        $sprint = $this->resolveSprint($project, $sprintFilter);

        $workload = $this->metricsService->workload($project, $sprint);
        $rows = collect($workload['rows'] ?? []);

        $export = new ProjectWorkloadExport($rows);
        $filename = $this->exportService->fileName($project, 'workload', [$sprint->id]);

        return $this->exportService->download($export, $filename);
    }

    private function normalizeTaskFilters(Project $project, array $filters): array
    {
        if (!empty($filters['assignee']) && !$this->userBelongsToProject($project, (int) $filters['assignee'])) {
            abort(404);
        }

        if (!empty($filters['sprint'])) {
            if ($filters['sprint'] !== 'backlog' && !ctype_digit($filters['sprint'])) {
                abort(422, 'Filtro sprint invalido.');
            }

            if (ctype_digit($filters['sprint'])) {
                $exists = $project->sprints()
                    ->whereKey($filters['sprint'])
                    ->exists();

                if (!$exists) {
                    abort(404);
                }
            }
        }

        return $filters;
    }

    private function resolveSprint(Project $project, string $filter): Sprint
    {
        if ($filter === 'active') {
            $active = $project->sprints()
                ->where('status', 'activo')
                ->orderByDesc('start_date')
                ->first();

            if (!$active) {
                abort(422, 'No hay sprint activo.');
            }

            return $active;
        }

        if (ctype_digit($filter)) {
            $sprint = $project->sprints()->whereKey($filter)->first();
            if (!$sprint) {
                abort(404);
            }

            return $sprint;
        }

        abort(422, 'Filtro sprint invalido.');
    }

    private function userBelongsToProject(Project $project, int $userId): bool
    {
        return $project->members()->where('users.id', $userId)->exists();
    }

    private function taskBelongsToProject(Project $project, int $taskId): bool
    {
        return Task::query()
            ->where('project_id', $project->id)
            ->whereKey($taskId)
            ->exists();
    }
}
