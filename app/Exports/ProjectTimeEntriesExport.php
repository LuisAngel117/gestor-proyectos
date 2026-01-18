<?php

namespace App\Exports;

use App\Models\Project;
use App\Models\TaskTimeEntry;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ProjectTimeEntriesExport implements FromQuery, WithHeadings, WithMapping, WithChunkReading
{
    public function __construct(
        private Project $project,
        private Carbon $from,
        private Carbon $to,
        private array $filters = []
    ) {
    }

    public function query(): Builder
    {
        $query = TaskTimeEntry::query()
            ->whereNotNull('stopped_at')
            ->where('duration_seconds', '>', 0)
            ->whereBetween('started_at', [$this->from, $this->to])
            ->whereHas('task', function (Builder $builder) {
                $builder->where('project_id', $this->project->id);
            })
            ->with([
                'task:id,title,project_id',
                'user:id,name,apellido',
            ])
            ->orderBy('started_at');

        if (!empty($this->filters['task_id'])) {
            $query->where('task_id', (int) $this->filters['task_id']);
        }

        if (!empty($this->filters['user_id'])) {
            $query->where('user_id', (int) $this->filters['user_id']);
        }

        if (!empty($this->filters['source'])) {
            $query->where('source', $this->filters['source']);
        }

        return $query;
    }

    public function headings(): array
    {
        return [
            'entry_id',
            'task_id',
            'task_title',
            'user_id',
            'user_name',
            'source',
            'started_at',
            'stopped_at',
            'duration_seconds',
            'duration_hours',
            'note',
            'created_by',
            'created_at',
        ];
    }

    public function map($entry): array
    {
        $userName = $entry->user
            ? trim($entry->user->name . ' ' . $entry->user->apellido)
            : null;

        $durationSeconds = (int) $entry->duration_seconds;

        return [
            $entry->id,
            $entry->task_id,
            $entry->task?->title,
            $entry->user_id,
            $userName,
            $entry->source,
            $entry->started_at?->format('Y-m-d H:i:s'),
            $entry->stopped_at?->format('Y-m-d H:i:s'),
            $durationSeconds,
            $this->formatHours($durationSeconds),
            $entry->note,
            $entry->created_by,
            $entry->created_at?->format('Y-m-d H:i:s'),
        ];
    }

    public function chunkSize(): int
    {
        return 1000;
    }

    private function formatHours(int $seconds): string
    {
        return number_format($seconds / 3600, 2, '.', '');
    }
}
