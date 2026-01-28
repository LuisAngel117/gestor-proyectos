<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreAdminUserRequest;
use App\Http\Requests\Admin\UpdateAdminUserRequest;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
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
            ->withCount(['teams', 'projects'])
            ->whereIn('id', $data['user_ids'])
            ->orderBy('name')
            ->get();

        $pdf = Pdf::loadView('admin.users.export-pdf', [
            'users' => $users,
            'generatedAt' => now(),
        ]);

        $filename = 'usuarios_' . now()->format('Ymd_His') . '.pdf';

        return $pdf->download($filename);
    }
}
