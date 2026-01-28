<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Services\Boards\ScrumBoardService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminScrumBoardController extends Controller
{
    public function __construct(private ScrumBoardService $boardService)
    {
        $this->middleware(function (Request $request, $next) {
            abort_unless($request->user() && $request->user()->isSuperadmin(), 403);
            return $next($request);
        });
    }

    public function index(Request $request): View
    {
        $projects = Project::with('team')
            ->orderBy('updated_at', 'desc')
            ->get();

        $boards = [];
        foreach ($projects as $project) {
            $activeSprint = $this->boardService->getActiveSprint($project);
            if (!$activeSprint) {
                continue;
            }

            $boards[] = [
                'project' => $project,
                'active_sprint' => $activeSprint,
                'board' => $this->boardService->buildBoard($project, $activeSprint, $request->user()),
            ];
        }

        return view('admin.scrum', [
            'boards' => $boards,
        ]);
    }
}
