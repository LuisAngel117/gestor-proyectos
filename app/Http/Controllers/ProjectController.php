<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Team;
use App\Support\Catalog;
use App\Support\Visibility\ProjectVisibility;
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
        $search = $request->string('q')->trim()->toString();

        // Filtrar por equipo si se proporciona
        $teamId = $request->get('team');

        if ($teamId) {
            $team = Team::findOrFail($teamId);
            $this->authorize('view', $team);

            $projectsQuery = ProjectVisibility::visibleProjectsForTeam($user, $team);
        } else {
            $projectsQuery = ProjectVisibility::visibleProjectsFor($user);
        }

        $projectsQuery->with(['team', 'creator', 'members']);

        if ($search !== '') {
            $projectsQuery->where(function ($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%')
                    ->orWhereHas('team', function ($teamQuery) use ($search) {
                        $teamQuery->where('name', 'like', '%' . $search . '%');
                    })
                    ->orWhereHas('creator', function ($creatorQuery) use ($search) {
                        $creatorQuery->where('name', 'like', '%' . $search . '%')
                            ->orWhere('apellido', 'like', '%' . $search . '%')
                            ->orWhere('email', 'like', '%' . $search . '%');
                    });
            });
        }

        $projects = $projectsQuery
            ->latest()
            ->paginate(12)
            ->appends($request->query());

        $userTeams = $user->isSuperadmin()
            ? Team::orderBy('name')->get()
            : $user->teams;

        return view('projects.index', [
            'projects' => $projects,
            'userTeams' => $userTeams,
            'teamId' => $teamId,
            'search' => $search,
            'isSuperadmin' => $user->isSuperadmin(),
        ]);
    }

    /**
     * Show the form for creating a new project.
     */
    public function create(Request $request)
    {
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
        if ($teamId) {
            $team = Team::findOrFail($teamId);
            $this->authorize('create', [Project::class, $team]);
        } else {
            $this->authorize('create', Project::class);
        }

        return view('projects.create', compact('teams', 'teamId'));
    }

    /**
     * Store a newly created project in storage.
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'team_id' => ['required', 'exists:teams,id'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'status' => ['required', Rule::in(Catalog::projectStatuses())],
            'priority' => ['required', Rule::in(Catalog::projectPriorities())],
            'start_date' => ['nullable', 'date'],
            'due_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'estimated_hours' => ['nullable', 'numeric', 'min:0', 'max:9999.99'],
        ]);

        // Verificar que el usuario pertenece al equipo
        $team = Team::findOrFail($validated['team_id']);
        $this->authorize('create', [Project::class, $team]);

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

        return redirect()
            ->to(route('projects.show', $project) . '#project-assistant')
            ->with('success', 'Proyecto creado exitosamente');
    }

    /**
     * Display the specified project.
     */
    public function show(Project $project)
    {
        $this->authorize('view', $project);

        $project->load(['team.users', 'creator', 'members', 'sprints'])
            ->loadCount(['tasks', 'backlogItems']);

        $availableMembers = $project->team->users
            ->filter(fn ($user) => !$project->members->contains($user))
            ->sortBy('name');

        return view('projects.show', [
            'project' => $project,
            'availableMembers' => $availableMembers,
            'planningSprint' => $project->sprints->firstWhere('status', 'planificacion'),
            'activeSprint' => $project->sprints->firstWhere('status', 'activo'),
        ]);
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
            'status' => ['required', Rule::in(Catalog::projectStatuses())],
            'priority' => ['required', Rule::in(Catalog::projectPriorities())],
            'start_date' => ['nullable', 'date'],
            'due_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'estimated_hours' => ['nullable', 'numeric', 'min:0', 'max:9999.99'],
        ]);

        if (!empty($validated['team_id']) && $validated['team_id'] !== $project->team_id) {
            $this->authorize('changeTeam', $project);
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
        $this->authorize('transferOwnership', $project);

        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
        ]);

        $newOwner = $project->members()->where('users.id', $validated['user_id'])->first();
        if (!$newOwner) {
            abort(403, 'El usuario seleccionado no pertenece al proyecto.');
        }

        if (!$project->team->hasMember($newOwner)) {
            abort(403, 'El usuario seleccionado no pertenece al team del proyecto.');
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
