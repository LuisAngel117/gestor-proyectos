<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSprintRequest;
use App\Models\Project;
use App\Models\Sprint;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class SprintController extends Controller
{
    public function index(Project $project): View
    {
        $this->authorize('view', $project);

        $sprints = $project->sprints()
            ->withCount('tasks')
            ->orderByDesc('start_date')
            ->get();

        return view('sprints.index', [
            'project' => $project,
            'sprints' => $sprints,
        ]);
    }

    public function create(Project $project): View
    {
        $this->authorize('update', $project);

        $nextSequence = (int) $project->sprints()->max('sequence');
        $nextSequence = $nextSequence > 0 ? $nextSequence + 1 : 1;

        return view('sprints.create', [
            'project' => $project,
            'nextSequence' => $nextSequence,
        ]);
    }

    public function store(StoreSprintRequest $request, Project $project): RedirectResponse
    {
        $this->authorize('update', $project);

        $data = $request->validated();

        $sequence = (int) $project->sprints()->withTrashed()->max('sequence');
        $sequence = $sequence > 0 ? $sequence + 1 : 1;

        $sprint = $project->sprints()->create([
            'name' => $data['name'],
            'goal' => $data['goal'] ?? null,
            'sequence' => $sequence,
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'status' => $data['status'],
            'created_by' => Auth::id(),
        ]);
        AuditLogger::log($request->user(), 'sprint.create', $sprint, [
            'name' => $sprint->name,
            'project' => $project->name,
        ]);

        return redirect()
            ->to(route('tasks.index', $project) . '?sprint=' . $sprint->id . '#create-task')
            ->with('success', 'Sprint creado. Ahora agrega las tareas del sprint.');
    }

    public function show(Project $project, Sprint $sprint): View
    {
        $this->authorize('view', $sprint);

        $sprint->loadCount('tasks');

        return view('sprints.show', [
            'project' => $project,
            'sprint' => $sprint,
        ]);
    }

    public function destroy(Project $project, Sprint $sprint): RedirectResponse
    {
        $this->authorize('delete', $sprint);

        if ($sprint->project_id !== $project->id) {
            abort(404);
        }

        AuditLogger::log(Auth::user(), 'sprint.delete', $sprint, [
            'name' => $sprint->name,
            'project' => $project->name,
        ]);
        $sprint->delete();

        return redirect()
            ->route('sprints.index', $project)
            ->with('success', 'Sprint eliminado.');
    }
}
