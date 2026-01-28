<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class AuditLogController extends Controller
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
        $userId = $request->integer('user');
        $action = $request->string('action')->trim()->toString();
        $type = $request->string('type')->trim()->toString();

        $query = AuditLog::query()->with('user');

        if ($userId) {
            $query->where('user_id', $userId);
        }

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

        $logs = $query->latest()->paginate(30)->appends($request->query());

        return view('admin.audit.index', [
            'logs' => $logs,
            'users' => User::where('estado', 'activo')->orderBy('name')->get(),
            'filters' => [
                'user' => $userId,
                'type' => $type,
                'action' => $action,
            ],
        ]);
    }

    public function exportPdf(Request $request): Response
    {
        $userId = $request->integer('user');
        $action = $request->string('action')->trim()->toString();
        $type = $request->string('type')->trim()->toString();

        $query = AuditLog::query()->with('user');

        if ($userId) {
            $query->where('user_id', $userId);
        }

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

        $logs = $query->latest()->get();

        $pdf = Pdf::loadView('admin.audit.export-pdf', [
            'logs' => $logs,
            'filters' => [
                'user' => $userId,
                'type' => $type,
                'action' => $action,
            ],
            'generatedAt' => now(),
        ]);

        $filename = 'auditoria_' . now()->format('Ymd_His') . '.pdf';

        return $pdf->download($filename);
    }
}
