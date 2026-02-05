<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class UserReportController extends Controller
{
    public function index(): View
    {
        $request = request();
        $user = $request->user();
        $filters = [
            'type' => $request->string('type')->toString(),
            'action' => $request->string('action')->toString(),
            'range' => $request->string('range')->toString(),
        ];

        $query = $this->buildAuditQuery($request, $user)->latest();
        $totalCount = $query->count();
        $previewLogs = $query->limit(5)->get();

        return view('users.reports.index', [
            'filters' => $filters,
            'totalCount' => $totalCount,
            'previewLogs' => $previewLogs,
        ]);
    }

    public function exportHistoryPdf(Request $request): Response
    {
        $user = $request->user();

        $logs = $this->buildAuditQuery($request, $user)->latest()->get();

        $pdf = Pdf::loadView('admin.users.audit-export-pdf', [
            'user' => $user,
            'logs' => $logs,
            'filters' => [
                'type' => $request->string('type')->toString() ?: 'all',
                'action' => $request->string('action')->toString() ?: 'all',
                'range' => $request->string('range')->toString() ?: 'all',
            ],
            'generatedAt' => now(),
        ]);

        $filename = 'historial_' . $user->id . '_' . now()->format('Ymd_His') . '.pdf';

        return $pdf->download($filename);
    }

    public function exportProfilePdf(Request $request): Response
    {
        $user = User::query()
            ->with(['profile', 'teams', 'projects.team'])
            ->withCount(['teams', 'projects'])
            ->findOrFail($request->user()->id);

        $taskLimit = 20;
        $taskMap = [
            $user->id => $user->assignedTasks()
                ->with(['project.team', 'sprint'])
                ->orderByPivot('assigned_at', 'desc')
                ->orderByDesc('tasks.created_at')
                ->limit($taskLimit)
                ->get(),
        ];

        $pdf = Pdf::loadView('admin.users.export-pdf', [
            'users' => collect([$user]),
            'taskMap' => $taskMap,
            'taskLimit' => $taskLimit,
            'generatedAt' => now(),
        ]);

        $filename = 'mi_perfil_' . $user->id . '_' . now()->format('Ymd_His') . '.pdf';

        return $pdf->download($filename);
    }

    private function buildAuditQuery(Request $request, User $user)
    {
        $action = $request->string('action')->trim()->toString();
        $type = $request->string('type')->trim()->toString();
        $range = $request->string('range')->trim()->toString();

        $query = AuditLog::query()
            ->where('user_id', $user->id);

        if ($type !== '') {
            $map = [
                'team' => 'team.',
                'project' => 'project.',
                'sprint' => 'sprint.',
                'task' => 'task.',
                'checklist' => 'checklist.',
                'time' => 'time.',
                'timer' => 'timer.',
                'message' => 'message.',
                'auth' => 'auth.',
            ];
            if (isset($map[$type])) {
                $query->where('action', 'like', $map[$type] . '%');
            }
        }

        if ($action !== '') {
            $query->where('action', 'like', '%' . $action . '%');
        }

        if ($range !== '') {
            $now = now();
            if ($range === 'today') {
                $query->whereDate('created_at', $now->toDateString());
            } elseif ($range === '7d') {
                $query->where('created_at', '>=', $now->copy()->subDays(7));
            } elseif ($range === '30d') {
                $query->where('created_at', '>=', $now->copy()->subDays(30));
            }
        }

        return $query;
    }
}
