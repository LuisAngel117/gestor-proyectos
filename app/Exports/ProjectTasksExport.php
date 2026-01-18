<?php

namespace App\Exports;

use App\Models\Project;
use App\Models\Task;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ProjectTasksExport implements FromQuery, WithHeadings, WithMapping, WithChunkReading
{
    public function __construct(
        private Project $project,
        private array $filters = []
    ) {
    }

    public function query(): Builder
    {
        $query = Task::query()
            ->where('project_id', $this->project->id)
            ->with([
                'sprint:id,name',
                'assignees:id,name,apellido',
                'creator:id,name,apellido',
            ])
            ->orderBy('created_at');

        if (!empty($this->filters['status'])) {
            $query->where('status', $this->filters['status']);
        }

        if (!empty($this->filters['sprint'])) {
            if ($this->filters['sprint'] === 'backlog') {
                $query->whereNull('sprint_id');
            } elseif (ctype_digit((string) $this->filters['sprint'])) {
                $query->where('sprint_id', (int) $this->filters['sprint']);
            }
        }

        if (!empty($this->filters['assignee'])) {
            $assigneeId = (int) $this->filters['assignee'];
            $query->whereHas('assignees', function (Builder $builder) use ($assigneeId) {
                $builder->where('users.id', $assigneeId);
            });
        }

        return $query;
    }

    public function headings(): array
    {
        return [
            'task_id',
            'title',
            'status',
            'priority',
            'sprint_id',
            'sprint_name',
            'estimated_hours',
            'due_date',
            'completed_at',
            'assignees',
            'created_by',
            'created_by_name',
            'created_at',
        ];
    }

    public function map($task): array
    {
        $assignees = $task->assignees
            ->map(fn ($user) => trim($user->name . ' ' . $user->apellido))
            ->implode(', ');

        return [
            $task->id,
            $task->title,
            $task->status,
            $task->priority,
            $task->sprint_id,
            $task->sprint?->name,
            $this->formatDecimal($task->estimated_hours),
            $task->due_date?->format('Y-m-d'),
            $task->completed_at?->format('Y-m-d H:i'),
            $assignees,
            $task->created_by,
            $task->creator ? trim($task->creator->name . ' ' . $task->creator->apellido) : null,
            $task->created_at?->format('Y-m-d H:i'),
        ];
    }

    public function chunkSize(): int
    {
        return 1000;
    }

    private function formatDecimal($value): ?string
    {
        if ($value === null) {
            return null;
        }

        return number_format((float) $value, 2, '.', '');
    }
}
