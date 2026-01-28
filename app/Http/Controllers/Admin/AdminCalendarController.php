<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Task;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminCalendarController extends Controller
{
    public function __construct()
    {
        $this->middleware(function (Request $request, $next) {
            abort_unless($request->user() && $request->user()->isSuperadmin(), 403);
            return $next($request);
        });
    }

    public function index(Request $request): View
    {
        $monthValue = $request->query('month', now()->format('Y-m'));
        try {
            $month = Carbon::createFromFormat('Y-m', $monthValue)->startOfMonth();
        } catch (\Throwable $e) {
            $month = now()->startOfMonth();
            $monthValue = $month->format('Y-m');
        }

        $rangeStart = $month->copy()->startOfWeek(Carbon::MONDAY);
        $rangeEnd = $month->copy()->endOfMonth()->endOfWeek(Carbon::SUNDAY);

        $datedTasks = Task::query()
            ->whereNotNull('due_date')
            ->whereBetween('due_date', [$rangeStart->toDateString(), $rangeEnd->toDateString()])
            ->whereHas('project')
            ->with(['project.team'])
            ->orderBy('due_date')
            ->orderBy('created_at')
            ->get();

        $tasksByDate = $datedTasks->groupBy(function (Task $task) {
            return optional($task->due_date)->toDateString();
        });

        $days = [];
        $cursor = $rangeStart->copy();
        while ($cursor->lte($rangeEnd)) {
            $dateKey = $cursor->toDateString();
            $days[] = [
                'date' => $cursor->copy(),
                'is_current_month' => $cursor->month === $month->month,
                'tasks' => $tasksByDate->get($dateKey, collect()),
            ];
            $cursor->addDay();
        }

        $undatedTasks = Task::query()
            ->whereNull('due_date')
            ->whereHas('project')
            ->with(['project.team'])
            ->orderByDesc('created_at')
            ->take(200)
            ->get();

        return view('admin.calendar', [
            'month' => $month,
            'month_label' => $month->translatedFormat('F Y'),
            'month_value' => $monthValue,
            'prev_month' => $month->copy()->subMonth()->format('Y-m'),
            'next_month' => $month->copy()->addMonth()->format('Y-m'),
            'days' => $days,
            'undated_tasks' => $undatedTasks,
        ]);
    }
}
