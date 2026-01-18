<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSprintRequest;
use App\Models\Project;
use App\Models\Sprint;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class SprintController extends Controller
{
    public function index(Project $project): View
    {
        $this->authorize('view', $project);

        $sprints = $project->sprints()
            ->withCount('backlogItems')
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

        $sequence = (int) $project->sprints()->max('sequence');
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

        return redirect()
            ->route('sprints.show', [$project, $sprint])
            ->with('success', 'Sprint creado.');
    }

    public function show(Project $project, Sprint $sprint): View
    {
        $this->authorize('view', $sprint);

        $sprint->loadCount('backlogItems');

        return view('sprints.show', [
            'project' => $project,
            'sprint' => $sprint,
        ]);
    }
}
