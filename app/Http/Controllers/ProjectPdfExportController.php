<?php

namespace App\Http\Controllers;

use App\Http\Requests\Exports\ExportSprintPdfRequest;
use App\Models\Project;
use App\Models\Sprint;
use App\Services\Exports\PdfReportService;
use Illuminate\Http\Response;

class ProjectPdfExportController extends Controller
{
    public function __construct(private PdfReportService $pdfService)
    {
    }

    public function sprintSummary(ExportSprintPdfRequest $request, Project $project): Response
    {
        $this->authorize('view', $project);

        $sprint = $this->resolveSprint($project, $request->validated()['sprint'] ?? 'active');
        $this->authorize('view', $sprint);

        return $this->pdfService->downloadSprintSummary($project, $sprint);
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
}
