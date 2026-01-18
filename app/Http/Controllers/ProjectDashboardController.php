<?php

namespace App\Http\Controllers;

use App\Http\Requests\Dashboard\ProjectDashboardQueryRequest;
use App\Models\Project;
use App\Models\Sprint;
use App\Services\Dashboard\ProjectDashboardMetricsService;
use Illuminate\Contracts\View\View;

class ProjectDashboardController extends Controller
{
    public function __construct(private ProjectDashboardMetricsService $metricsService)
    {
    }

    public function index(ProjectDashboardQueryRequest $request, Project $project): View
    {
        $this->authorize('view', $project);

        $data = $request->validated();
        $filters = [
            'sprint' => $data['sprint'] ?? 'active',
        ];

        [$sprint, $warning] = $this->resolveSprint($project, $filters['sprint']);
        $previousSprint = $sprint ? $this->resolvePreviousSprint($project, $sprint) : null;

        $metrics = $sprint
            ? $this->metricsService->build($project, $sprint, $previousSprint)
            : [
                'velocity' => null,
                'previous_velocity' => null,
                'time_in_state' => [
                    'statuses' => \App\Services\Boards\ScrumBoardService::STATUSES,
                    'summaries' => [],
                    'oldest' => [],
                    'warnings' => [],
                ],
                'workload' => ['rows' => [], 'totals' => []],
            ];

        return view('projects.dashboard.index', [
            'project' => $project,
            'sprint' => $sprint,
            'previousSprint' => $previousSprint,
            'metrics' => $metrics,
            'filters' => $filters,
            'warning' => $warning,
            'sprints' => $project->sprints()->orderByDesc('start_date')->get(),
        ]);
    }

    private function resolveSprint(Project $project, ?string $filter): array
    {
        $warning = null;
        $filter = $filter ?? 'active';

        if ($filter === 'active') {
            $active = $project->sprints()
                ->where('status', 'activo')
                ->orderByDesc('start_date')
                ->first();

            if (!$active) {
                $warning = 'No hay sprint activo. Selecciona otro sprint.';
            }

            return [$active, $warning];
        }

        if (is_string($filter) && ctype_digit($filter)) {
            $sprint = $project->sprints()->whereKey($filter)->first();
            if (!$sprint) {
                abort(404);
            }
            return [$sprint, null];
        }

        return [$project->sprints()->orderByDesc('start_date')->first(), $warning];
    }

    private function resolvePreviousSprint(Project $project, Sprint $current): ?Sprint
    {
        $query = $project->sprints()
            ->where('status', 'cerrado')
            ->where('id', '!=', $current->id);

        if ($current->start_date) {
            $query->where('start_date', '<', $current->start_date);
        }

        return $query->orderByDesc('end_date')
            ->orderByDesc('start_date')
            ->first();
    }
}
