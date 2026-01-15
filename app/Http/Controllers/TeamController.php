<?php

namespace App\Http\Controllers;

use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TeamController extends Controller
{
    /**
     * Display a listing of the teams.
     */
    public function index()
    {
        $user = Auth::user();

        // Equipos donde el usuario es miembro
        $teams = $user->teams()->with('owner')->get();

        // Equipos propiedad del usuario
        $ownedTeams = $user->ownedTeams()->with('users')->get();

        return view('teams.index', compact('teams', 'ownedTeams'));
    }

    /**
     * Show the form for creating a new team.
     */
    public function create()
    {
        return view('teams.create');
    }

    /**
     * Store a newly created team in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $user = Auth::user();

        // Crear el equipo
        $team = Team::create([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'owner_id' => $user->id,
        ]);

        // Agregar al creador como owner del equipo
        $team->addMember($user, 'owner');

        return redirect()->route('teams.show', $team)
            ->with('success', 'Equipo creado exitosamente');
    }

    /**
     * Display the specified team.
     */
    public function show(Team $team)
    {
        $user = Auth::user();

        // Verificar que el usuario sea miembro del equipo
        if (!$team->hasMember($user) && !$user->isSuperadmin()) {
            abort(403, 'No tienes acceso a este equipo');
        }

        $team->load(['owner', 'users']);
        $projects = $team->projects()
            ->with(['creator', 'members'])
            ->latest()
            ->paginate(12);

        return view('teams.show', compact('team', 'projects'));
    }

    /**
     * Show the form for editing the specified team.
     */
    public function edit(Team $team)
    {
        $user = Auth::user();

        // Solo el owner o superadmin puede editar
        if (!$team->isOwner($user) && !$user->isSuperadmin()) {
            abort(403, 'No tienes permiso para editar este equipo');
        }

        return view('teams.edit', compact('team'));
    }

    /**
     * Update the specified team in storage.
     */
    public function update(Request $request, Team $team)
    {
        $user = Auth::user();

        // Solo el owner o superadmin puede actualizar
        if (!$team->isOwner($user) && !$user->isSuperadmin()) {
            abort(403, 'No tienes permiso para actualizar este equipo');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $team->update($validated);

        return redirect()->route('teams.show', $team)
            ->with('success', 'Equipo actualizado exitosamente');
    }

    /**
     * Remove the specified team from storage.
     */
    public function destroy(Team $team)
    {
        $user = Auth::user();

        // Solo el owner o superadmin puede eliminar
        if (!$team->isOwner($user) && !$user->isSuperadmin()) {
            abort(403, 'No tienes permiso para eliminar este equipo');
        }

        $teamName = $team->name;
        $team->delete();

        return redirect()->route('teams.index')
            ->with('success', "Equipo '{$teamName}' eliminado exitosamente");
    }
}
