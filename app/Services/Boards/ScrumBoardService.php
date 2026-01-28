<?php

namespace App\Services\Boards;

use App\Models\Project;
use App\Models\Sprint;
use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Collection;

class ScrumBoardService
{
    public const STATUSES = [
        'todo' => [
            'label' => 'Por hacer',
            'color' => 'secondary',
        ],
        'en_progreso' => [
            'label' => 'En progreso',
            'color' => 'warning',
        ],
        'hecho' => [
            'label' => 'Hecho',
            'color' => 'success',
        ],
    ];

    public function getActiveSprint(Project $project): ?Sprint
    {
        return $project->sprints()
            ->where('status', 'activo')
            ->orderByDesc('start_date')
            ->first();
    }

    public function buildBoard(Project $project, ?Sprint $sprint, User $viewer): array
    {
        $statuses = self::STATUSES;
        $statusKeys = array_keys($statuses);

        $tasks = $this->loadTasks($project, $sprint, $viewer);
        $members = $this->loadMembers($project);

        $assignedUsers = $tasks
            ->pluck('assignees')
            ->flatten()
            ->unique('id');

        $laneUsers = $members
            ->concat($assignedUsers)
            ->unique('id')
            ->values();

        $lanes = $this->buildLanes($laneUsers);
        $laneKeys = $lanes->keys()->all();

        $taskBuckets = $this->emptyBuckets($laneKeys, $statusKeys);
        $statusCounts = array_fill_keys($statusKeys, 0);
        $warnings = [];

        foreach ($tasks as $task) {
            $status = $this->normalizeStatus($task->status, $statusKeys, $warnings);
            $statusCounts[$status]++;

            $assignees = $task->assignees->sortBy(function (User $user) {
                return $user->pivot?->assigned_at;
            });

            $laneKey = $assignees->isEmpty()
                ? 'unassigned'
                : (string) $assignees->first()->id;

            $taskBuckets[$laneKey][$status][] = [
                'task' => $task,
                'assignee_count' => $assignees->count(),
                'extra_assignees' => max(0, $assignees->count() - 1),
            ];
        }

        return [
            'statuses' => $statuses,
            'lanes' => $lanes->values()->all(),
            'task_buckets' => $taskBuckets,
            'status_counts' => $statusCounts,
            'active_sprint' => $sprint,
            'warnings' => $warnings,
            'viewer_id' => $viewer->id,
        ];
    }

    private function loadTasks(Project $project, ?Sprint $sprint, User $viewer): Collection
    {
        if (!$sprint) {
            return collect();
        }

        $query = Task::query()
            ->where('project_id', $project->id)
            ->where('sprint_id', $sprint->id)
            ->with(['assignees' => function ($query) {
                $query->select('users.id', 'users.name', 'users.apellido');
            }])
            ->orderBy('created_at');

        $projectRole = $viewer->roleInProject($project->id);
        $teamRole = $viewer->roleInTeam($project->team_id);
        if (
            in_array($projectRole, ['member', 'observer'], true)
            && !in_array($teamRole, ['owner', 'admin', 'observer'], true)
        ) {
            $query->whereHas('assignees', function ($assigneeQuery) use ($viewer) {
                $assigneeQuery->where('users.id', $viewer->id);
            });
        }

        return $query->get();
    }

    private function loadMembers(Project $project): Collection
    {
        return $project->members()
            ->select('users.id', 'users.name', 'users.apellido')
            ->orderBy('users.name')
            ->get();
    }

    private function buildLanes(Collection $users): Collection
    {
        $lanes = collect();

        $lanes->put('unassigned', [
            'id' => null,
            'key' => 'unassigned',
            'label' => 'Sin asignar',
        ]);

        foreach ($users as $user) {
            $lanes->put((string) $user->id, [
                'id' => $user->id,
                'key' => (string) $user->id,
                'label' => trim($user->name . ' ' . $user->apellido),
            ]);
        }

        return $lanes;
    }

    private function emptyBuckets(array $laneKeys, array $statusKeys): array
    {
        $buckets = [];
        foreach ($laneKeys as $laneKey) {
            $buckets[$laneKey] = [];
            foreach ($statusKeys as $status) {
                $buckets[$laneKey][$status] = [];
            }
        }

        return $buckets;
    }

    private function normalizeStatus(string $status, array $allowed, array &$warnings): string
    {
        if (in_array($status, $allowed, true)) {
            return $status;
        }

        if (!in_array($status, $warnings, true)) {
            $warnings[] = $status;
        }

        return $allowed[0];
    }
}
