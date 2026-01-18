<?php

namespace App\Http\Controllers;

use App\Http\Requests\Board\MoveTaskOnBoardRequest;
use App\Models\Project;
use App\Models\Task;
use App\Services\Boards\ScrumBoardService;
use App\Services\Tracking\TaskStatusTrackingService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ScrumBoardController extends Controller
{
    public function __construct(
        private ScrumBoardService $boardService,
        private TaskStatusTrackingService $trackingService
    )
    {
    }

    public function index(Request $request, Project $project): View
    {
        $this->authorize('view', $project);

        $activeSprint = $this->boardService->getActiveSprint($project);
        $board = $this->boardService->buildBoard($project, $activeSprint, $request->user());

        return view('projects.board.index', [
            'project' => $project,
            'board' => $board,
        ]);
    }

    public function move(
        MoveTaskOnBoardRequest $request,
        Project $project,
        Task $task
    ): RedirectResponse {
        $this->ensureProjectTaskConsistency($project, $task);
        $this->authorize('update', $task);

        $activeSprint = $this->boardService->getActiveSprint($project);
        if (!$activeSprint || (int) $task->sprint_id !== (int) $activeSprint->id) {
            abort(404);
        }

        $payload = $request->validated();
        $this->trackingService->recordTransition($task, $payload['status'], $request->user());

        return back()->with('success', 'Estado actualizado.');
    }

    private function ensureProjectTaskConsistency(Project $project, Task $task): void
    {
        if ($task->project_id !== $project->id) {
            abort(404);
        }
    }
}
