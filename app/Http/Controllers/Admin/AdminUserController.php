<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreAdminUserRequest;
use App\Http\Requests\Admin\UpdateAdminUserRequest;
use App\Models\AuditLog;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AdminUserController extends Controller
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
        $search = $request->string('q')->trim()->toString();

        $query = User::query()
            ->withCount(['teams', 'projects'])
            ->where('estado', 'activo')
            ->orderBy('name');

        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder->where('name', 'like', '%' . $search . '%')
                    ->orWhere('apellido', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%')
                    ->orWhere('role', 'like', '%' . $search . '%')
                    ->orWhere('estado', 'like', '%' . $search . '%')
                    ->orWhereHas('teams', function ($teamQuery) use ($search) {
                        $teamQuery->where('teams.name', 'like', '%' . $search . '%');
                    });
            });
        }

        $users = $query->paginate(20)->appends($request->query());

        return view('admin.users.index', [
            'users' => $users,
            'filters' => [
                'q' => $search,
            ],
        ]);
    }

    public function create(): View
    {
        return view('admin.users.create');
    }

    public function show(User $user): View
    {
        $user->load([
            'profile',
            'teams' => function ($query) {
                $query->with('owner')->withCount('users');
            },
            'projects.team',
            'projects.members',
        ]);

        return view('admin.users.show', [
            'user' => $user,
            'teams' => $user->teams,
            'projects' => $user->projects,
        ]);
    }

    public function edit(User $user): View
    {
        return view('admin.users.edit', [
            'user' => $user,
        ]);
    }

    public function store(StoreAdminUserRequest $request): RedirectResponse
    {
        $data = $request->validated();

        User::create([
            'name' => $data['name'],
            'apellido' => $data['apellido'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => $data['role'],
            'estado' => $data['estado'],
            'email_verified_at' => now(),
            'must_change_password' => true,
            'profile_completed_at' => null,
        ]);

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'Usuario creado.');
    }

    public function update(UpdateAdminUserRequest $request, User $user): RedirectResponse
    {
        $data = $request->validated();

        if ($user->id === $request->user()->id && ($data['role'] ?? $user->role) !== 'superadmin') {
            return back()->withErrors([
                'role' => 'No puedes remover tu propio rol de superadmin.',
            ]);
        }

        $updates = [];

        foreach (['name', 'apellido', 'email'] as $field) {
            if (array_key_exists($field, $data)) {
                $updates[$field] = $data[$field];
            }
        }

        if (!empty($data['password'] ?? null)) {
            $updates['password'] = Hash::make($data['password']);
        }

        $role = $data['role'] ?? $user->role;
        if ($user->role !== 'superadmin' && $role === 'superadmin') {
            return back()->withErrors([
                'role' => 'Solo el superadmin actual puede conservar ese rol.',
            ]);
        }
        if ($user->role === 'superadmin') {
            $role = 'superadmin';
        }

        $estado = $data['estado'] ?? $user->estado;
        if ($user->role === 'superadmin' && $estado === 'inactivo') {
            return back()->withErrors([
                'estado' => 'No puedes desactivar al superadmin.',
            ]);
        }

        $updates['role'] = $role;
        $updates['estado'] = $estado;

        $user->update($updates);

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'Usuario actualizado.');
    }

    public function deactivate(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'password' => ['required', 'string'],
        ]);

        if (!Hash::check($data['password'], $request->user()->password)) {
            return back()->withErrors([
                'password' => 'Contrasena incorrecta.',
            ]);
        }

        if ($user->role === 'superadmin') {
            return back()->withErrors([
                'user' => 'No puedes desactivar al superadmin.',
            ]);
        }

        $user->update(['estado' => 'inactivo']);

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'Usuario desactivado.');
    }

    public function resetPassword(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'password' => ['required', 'string'],
        ]);

        if (!Hash::check($data['password'], $request->user()->password)) {
            return back()->withErrors([
                'password' => 'Contrasena incorrecta.',
            ]);
        }

        if ($user->role === 'superadmin') {
            return back()->withErrors([
                'user' => 'No puedes resetear la contrasena del superadmin.',
            ]);
        }

        $tempPassword = 'Contrasena123';

        $user->update([
            'password' => Hash::make($tempPassword),
            'must_change_password' => true,
        ]);

        return back()->with('info', "Contrasena temporal asignada: {$tempPassword}");
    }

    public function bulkDeactivate(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'password' => ['required', 'string'],
            'user_ids' => ['required', 'array', 'min:1'],
            'user_ids.*' => ['integer', 'exists:users,id'],
        ]);

        if (!Hash::check($data['password'], $request->user()->password)) {
            return back()->withErrors([
                'password' => 'Contrasena incorrecta.',
            ]);
        }

        $userIds = collect($data['user_ids'])->unique()->values();
        $hasSuperadmin = User::whereIn('id', $userIds)->where('role', 'superadmin')->exists();
        if ($hasSuperadmin) {
            return back()->withErrors([
                'user' => 'No puedes desactivar al superadmin.',
            ]);
        }

        User::whereIn('id', $userIds)->update(['estado' => 'inactivo']);

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'Usuarios desactivados.');
    }

    public function exportPdf(Request $request): Response
    {
        $data = $request->validate([
            'user_ids' => ['required', 'array', 'min:1'],
            'user_ids.*' => ['integer', 'exists:users,id'],
        ]);

        $users = User::query()
            ->with([
                'profile',
                'teams',
                'projects.team',
            ])
            ->withCount(['teams', 'projects'])
            ->whereIn('id', $data['user_ids'])
            ->orderBy('name')
            ->get();

        $taskLimit = 20;
        $taskMap = [];
        foreach ($users as $user) {
            $taskMap[$user->id] = $user->assignedTasks()
                ->with(['project.team', 'sprint'])
                ->orderByPivot('assigned_at', 'desc')
                ->orderByDesc('tasks.created_at')
                ->limit($taskLimit)
                ->get();
        }

        $pdf = Pdf::loadView('admin.users.export-pdf', [
            'users' => $users,
            'taskMap' => $taskMap,
            'taskLimit' => $taskLimit,
            'generatedAt' => now(),
        ]);

        $filename = 'usuarios_' . now()->format('Ymd_His') . '.pdf';

        return $pdf->download($filename);
    }

    public function audit(Request $request, User $user): View
    {
        [$logs, $filters, $labelMap, $typeBadge] = $this->buildAuditData($request, $user, 20);

        return view('admin.users.audit-table', [
            'logs' => $logs,
            'filters' => $filters,
            'labelMap' => $labelMap,
            'typeBadge' => $typeBadge,
        ]);
    }

    public function exportAuditPdf(Request $request, User $user): Response
    {
        [$logs, $filters] = $this->buildAuditData($request, $user, null);

        $pdf = Pdf::loadView('admin.users.audit-export-pdf', [
            'user' => $user,
            'logs' => $logs,
            'filters' => $filters,
            'generatedAt' => now(),
        ]);

        $filename = 'historial_' . $user->id . '_' . now()->format('Ymd_His') . '.pdf';

        return $pdf->download($filename);
    }

    private function buildAuditQuery(Request $request, User $user): Builder
    {
        $action = $request->string('action')->trim()->toString();
        $type = $request->string('type')->trim()->toString();

        $query = AuditLog::query()
            ->with('user')
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

        return $query;
    }

    private function buildAuditData(Request $request, User $user, ?int $limit): array
    {
        $query = $this->buildAuditQuery($request, $user)->latest();
        $logs = $limit ? $query->limit($limit)->get() : $query->get();

        $labelMap = [
            'auth.login' => 'Inicio de sesion',
            'auth.logout' => 'Cierre de sesion',
            'team.create' => 'Equipo creado',
            'team.update' => 'Equipo actualizado',
            'team.delete' => 'Equipo eliminado',
            'team.member.add' => 'Miembro agregado al equipo',
            'team.member.role' => 'Rol de equipo actualizado',
            'team.member.remove' => 'Miembro removido del equipo',
            'project.create' => 'Proyecto creado',
            'project.update' => 'Proyecto actualizado',
            'project.delete' => 'Proyecto eliminado',
            'project.transfer_owner' => 'Owner del proyecto actualizado',
            'project.member.add' => 'Miembro agregado al proyecto',
            'project.member.role' => 'Rol de proyecto actualizado',
            'project.member.remove' => 'Miembro removido del proyecto',
            'sprint.create' => 'Sprint creado',
            'sprint.delete' => 'Sprint eliminado',
            'sprint.start' => 'Sprint iniciado',
            'sprint.close' => 'Sprint cerrado',
            'task.create' => 'Tarea creada',
            'task.update' => 'Tarea actualizada',
            'task.delete' => 'Tarea eliminada',
            'task.move' => 'Tarea movida',
            'task.assign' => 'Tarea asignada',
            'task.unassign' => 'Tarea desasignada',
            'checklist.update' => 'Checklist actualizado',
            'checklist.delete' => 'Checklist eliminado',
            'timer.start' => 'Timer iniciado',
            'timer.stop' => 'Timer finalizado',
            'time.manual.create' => 'Tiempo manual registrado',
            'time.manual.update' => 'Tiempo manual actualizado',
            'time.manual.delete' => 'Tiempo manual eliminado',
            'message.send' => 'Mensaje enviado',
        ];

        $typeBadge = function (string $action): string {
            $prefix = explode('.', $action)[0] ?? '';
            $colors = [
                'auth' => 'bg-slate-100 text-slate-700',
                'team' => 'bg-sky-100 text-sky-700',
                'project' => 'bg-indigo-100 text-indigo-700',
                'sprint' => 'bg-amber-100 text-amber-700',
                'task' => 'bg-violet-100 text-violet-700',
                'checklist' => 'bg-emerald-100 text-emerald-700',
                'timer' => 'bg-rose-100 text-rose-700',
                'time' => 'bg-rose-100 text-rose-700',
                'message' => 'bg-teal-100 text-teal-700',
            ];
            return $colors[$prefix] ?? 'bg-gray-100 text-gray-700';
        };

        return [
            $logs,
            [
                'type' => $request->string('type')->trim()->toString(),
                'action' => $request->string('action')->trim()->toString(),
            ],
            $labelMap,
            $typeBadge,
        ];
    }
}
