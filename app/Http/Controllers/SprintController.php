<?php

namespace App\Http\Controllers;

use App\Models\Project;
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
}
