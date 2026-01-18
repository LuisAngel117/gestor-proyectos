<?php

namespace App\Http\Controllers;

use App\Http\Requests\Calendar\ProjectCalendarQueryRequest;
use App\Models\Project;
use App\Models\Sprint;
use App\Services\Calendar\ProjectCalendarService;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;

class ProjectCalendarController extends Controller
{
    public function __construct(private ProjectCalendarService $calendarService)
    {
    }

    public function index(ProjectCalendarQueryRequest $request, Project $project): View
    {
        $this->authorize('view', $project);

        $data = $request->validated();
        $month = $this->resolveMonth($data['month'] ?? null);
        $activeSprint = $this->calendarService->getActiveSprint($project);

        $filters = $this->buildFilters($project, $data, $activeSprint);

        $calendar = $this->calendarService->buildMonthly($project, $month, $request->user(), $filters);
        $calendar['active_sprint'] = $activeSprint;

        return view('projects.calendar.index', [
            'project' => $project,
            'calendar' => $calendar,
            'sprints' => $project->sprints()->orderByDesc('start_date')->get(),
            'assignees' => $project->members()
                ->select('users.id', 'users.name', 'users.apellido')
                ->orderBy('users.name')
                ->get(),
        ]);
    }

    private function resolveMonth(?string $month): Carbon
    {
        if (!$month) {
            return now()->startOfMonth();
        }

        return Carbon::createFromFormat('Y-m', $month)->startOfMonth();
    }

    private function buildFilters(Project $project, array $data, ?Sprint $activeSprint): array
    {
        $filters = [
            'status' => $data['status'] ?? null,
            'assignee' => isset($data['assignee']) ? (int) $data['assignee'] : null,
            'sprint' => $data['sprint'] ?? null,
            'sprint_mode' => null,
            'sprint_id' => null,
        ];

        if (!empty($filters['assignee'])) {
            $isMember = $project->members()
                ->where('users.id', $filters['assignee'])
                ->exists();

            if (!$isMember) {
                abort(404);
            }
        }

        $sprintFilter = $filters['sprint'];
        if ($sprintFilter === 'active') {
            $filters['sprint_mode'] = 'active';
            $filters['sprint_id'] = $activeSprint?->id;
        } elseif ($sprintFilter === 'backlog') {
            $filters['sprint_mode'] = 'backlog';
        } elseif (is_string($sprintFilter) && ctype_digit($sprintFilter)) {
            $filters['sprint_mode'] = 'specific';
            $filters['sprint_id'] = (int) $sprintFilter;

            $exists = $project->sprints()
                ->whereKey($filters['sprint_id'])
                ->exists();

            if (!$exists) {
                abort(404);
            }
        }

        return $filters;
    }
}
