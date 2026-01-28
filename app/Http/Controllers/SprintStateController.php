<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Sprint;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;

class SprintStateController extends Controller
{
    public function start(Project $project, Sprint $sprint): RedirectResponse
    {
        $this->authorize('startSprint', $sprint);

        if (!$sprint->isPlanning()) {
            return back()->withErrors([
                'status' => 'El sprint debe estar en planificacion para iniciar.',
            ]);
        }

        if (!$sprint->backlogItems()->exists()) {
            return back()->withErrors([
                'status' => 'No puedes iniciar un sprint sin items asignados.',
            ]);
        }

        $hasActiveSprint = $project->sprints()
            ->where('status', 'activo')
            ->where('id', '!=', $sprint->id)
            ->exists();

        if ($hasActiveSprint) {
            return back()->withErrors([
                'status' => 'Ya existe un sprint activo en este proyecto.',
            ]);
        }

        $sprint->update([
            'status' => 'activo',
            'started_at' => now(),
        ]);
        AuditLogger::log(request()->user(), 'sprint.start', $sprint, [
            'project' => $project->name,
        ]);

        return redirect()
            ->to(route('projects.show', $project) . '#project-assistant')
            ->with('success', 'Sprint iniciado correctamente.');
    }

    public function close(Project $project, Sprint $sprint): RedirectResponse
    {
        $this->authorize('closeSprint', $sprint);

        if (!$sprint->isActive()) {
            return back()->withErrors([
                'status' => 'El sprint debe estar activo para cerrarlo.',
            ]);
        }

        $sprint->update([
            'status' => 'cerrado',
            'closed_at' => now(),
        ]);
        AuditLogger::log(request()->user(), 'sprint.close', $sprint, [
            'project' => $project->name,
        ]);

        return redirect()
            ->route('sprints.show', [$project, $sprint])
            ->with('success', 'Sprint cerrado correctamente.');
    }
}
