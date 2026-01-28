<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminTaskController extends Controller
{
    public function __construct()
    {
        $this->middleware(function (Request $request, $next) {
            abort_unless($request->user() && $request->user()->isSuperadmin(), 403);
            return $next($request);
        });
    }

    public function index(): View
    {
        $tasks = Task::query()
            ->whereHas('project')
            ->with(['project.team', 'sprint', 'assignees'])
            ->orderByDesc('created_at')
            ->paginate(25);

        return view('admin.tasks', [
            'tasks' => $tasks,
        ]);
    }
}
