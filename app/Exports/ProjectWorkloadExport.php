<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ProjectWorkloadExport implements FromCollection, WithHeadings, WithMapping
{
    public function __construct(private Collection $rows)
    {
    }

    public function collection(): Collection
    {
        return $this->rows;
    }

    public function headings(): array
    {
        return [
            'user_id',
            'user_name',
            'planned_hours',
            'planned_tasks',
            'real_hours',
            'real_seconds',
        ];
    }

    public function map($row): array
    {
        return [
            $row['user_id'],
            $row['label'],
            $row['planned_hours'],
            $row['planned_tasks'],
            $row['real_hours'],
            $row['real_seconds'],
        ];
    }
}
