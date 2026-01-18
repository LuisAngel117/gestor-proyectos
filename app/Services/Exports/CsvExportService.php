<?php

namespace App\Services\Exports;

use App\Models\Project;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Excel as ExcelFormat;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class CsvExportService
{
    public function download(object $export, string $filename): BinaryFileResponse
    {
        return Excel::download($export, $filename, ExcelFormat::CSV, [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function fileName(Project $project, string $suffix, array $parts = []): string
    {
        $stamp = now()->format('Ymd');
        $extra = $parts ? '_' . implode('_', $parts) : '';

        return "project_{$project->id}_{$suffix}{$extra}_{$stamp}.csv";
    }
}
