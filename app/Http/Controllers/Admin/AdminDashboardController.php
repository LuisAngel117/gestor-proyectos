<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Team;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminDashboardController extends Controller
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
        return view('admin.index', [
            'usersCount' => User::count(),
            'teamsCount' => Team::count(),
            'projectsCount' => Project::count(),
            'recentUsers' => User::orderByDesc('created_at')->take(5)->get(),
            'recentTeams' => Team::with('owner')->orderByDesc('created_at')->take(5)->get(),
            'recentProjects' => Project::with('team')->orderByDesc('created_at')->take(5)->get(),
        ]);
    }
}
