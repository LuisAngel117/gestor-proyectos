<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ProjectController extends Controller
{
    /**
     * Display a listing of the projects.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // Filtrar por equipo si se proporciona
        $teamId = $request->get('team');

        if ($teamId) {
            $team = Team::findOrFail($teamId);
            $this->authorize('view', $team);

            $projects = $team->projects()
                ->with(['team', 'creator', 'members'])
                ->latest()
                ->paginate(12);
        } else {
            // Mostrar proyectos de equipos donde el usuario es miembro
            $teamIds = $user->teams()->pluck('teams.id');

            $projects = Project::whereIn('team_id', $teamIds)
                ->with(['team', 'creator', 'members'])
                ->latest()
                ->paginate(12);
        }

        $userTeams = $user->teams;

        return view('projects.index', compact('projects', 'userTeams', 'teamId'));
    }

    /**
     * Show the form for creating a new project.
     */
    public function create(Request $request)
    {
        $this->authorize('create', Project::class);

        $user = Auth::user();

        // Obtener equipos donde el usuario puede crear proyectos (owner o admin)
        $teams = $user->teams()
            ->wherePivotIn('role', ['owner', 'admin'])
            ->get();

        if ($teams->isEmpty()) {
            return redirect()->route('teams.index')
                ->with('warning', 'Debes ser propietario o administrador de un equipo para crear proyectos.');
        }

        $teamId = $request->get('team');

        return view('projects.create', compact('teams', 'teamId'));
    }

    /**
     * Store a newly created project in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('create', Project::class);

        $user = Auth::user();

        $validated = $request->validate([
            'team_id' => ['required', 'exists:teams,id'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'status' => ['required', Rule::in(['planificacion', 'en_progreso', 'en_espera', 'completado', 'cancelado'])],
            'priority' => ['required', Rule::in(['baja', 'media', 'alta', 'urgente'])],
            'start_date' => ['nullable', 'date'],
            'due_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'estimated_hours' => ['nullable', 'numeric', 'min:0', 'max:9999.99'],
        ]);

        // Verificar que el usuario pertenece al equipo
        $team = Team::findOrFail($validated['team_id']);
        if (!$team->hasMember($user) && !$user->isSuperadmin()) {
            abort(403, 'No perteneces a este equipo.');
        }

        // Verificar que puede crear proyectos en el equipo
        $userRole = $team->getUserRole($user);
        if (!in_array($userRole, ['owner', 'admin']) && !$user->isSuperadmin()) {
            abort(403, 'No tienes permiso para crear proyectos en este equipo.');
        }

        // Crear el proyecto
        $project = Project::create([
            'team_id' => $validated['team_id'],
            'name' => $validated['name'],
            'description' => $validated['description'],
            'status' => $validated['status'],
            'priority' => $validated['priority'],
            'start_date' => $validated['start_date'],
            'due_date' => $validated['due_date'],
            'estimated_hours' => $validated['estimated_hours'],
            'created_by' => $user->id,
        ]);

        // Agregar al creador como owner del proyecto
        $project->addMember($user, 'owner');

        return redirect()->route('projects.show', $project)
            ->with('success', 'Proyecto creado exitosamente');
    }

    /**
     * Display the specified project.
     */
    public function show(Project $project)
    {
        $this->authorize('view', $project);

        $project->load(['team.users', 'creator', 'members']);

        return view('projects.show', compact('project'));
    }

    /**
     * Show the form for editing the specified project.
     */
    public function edit(Project $project)
    {
        $this->authorize('update', $project);

        $user = Auth::user();
        $teams = $user->isSuperadmin()
            ? Team::orderBy('name')->get()
            : $user->teams()->wherePivotIn('role', ['owner', 'admin'])->get();

        return view('projects.edit', compact('project', 'teams'));
    }

    /**
     * Update the specified project in storage.
     */
    public function update(Request $request, Project $project)
    {
        $this->authorize('update', $project);

        $validated = $request->validate([
            'team_id' => ['nullable', 'exists:teams,id'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'status' => ['required', Rule::in(['planificacion', 'en_progreso', 'en_espera', 'completado', 'cancelado'])],
            'priority' => ['required', Rule::in(['baja', 'media', 'alta', 'urgente'])],
            'start_date' => ['nullable', 'date'],
            'due_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'estimated_hours' => ['nullable', 'numeric', 'min:0', 'max:9999.99'],
        ]);

        if (!empty($validated['team_id']) && $validated['team_id'] !== $project->team_id) {
            $user = Auth::user();
            $newTeam = Team::findOrFail($validated['team_id']);

            if (!$newTeam->hasMember($user) && !$user->isSuperadmin()) {
                abort(403, 'No perteneces al equipo destino.');
            }

            $userRole = $newTeam->getUserRole($user);
            if (!in_array($userRole, ['owner', 'admin']) && !$user->isSuperadmin()) {
                abort(403, 'No tienes permiso para mover este proyecto al equipo destino.');
            }
        }

        $project->update($validated);

        return redirect()->route('projects.show', $project)
            ->with('success', 'Proyecto actualizado exitosamente');
    }

    /**
     * Transfer project ownership to another member.
     */
    public function transferOwner(Request $request, Project $project)
    {
        $this->authorize('manageMembers', $project);

        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
        ]);

        $newOwner = $project->members()->where('users.id', $validated['user_id'])->first();
        if (!$newOwner) {
            abort(403, 'El usuario seleccionado no pertenece al proyecto.');
        }

        $currentOwner = $project->members()->wherePivot('role', 'owner')->first();
        if ($currentOwner && $currentOwner->id !== $newOwner->id) {
            $project->members()->updateExistingPivot($currentOwner->id, ['role' => 'admin']);
            $project->members()->updateExistingPivot($newOwner->id, ['role' => 'owner']);
        }

        return redirect()->route('projects.show', $project)
            ->with('success', 'Propietario del proyecto actualizado.');
    }

    /**
     * Remove the specified project from storage.
     */
    public function destroy(Project $project)
    {
        $this->authorize('delete', $project);

        $projectName = $project->name;
        $project->delete();

        return redirect()->route('projects.index')
            ->with('success', "Proyecto '{$projectName}' eliminado exitosamente");
    }
}
