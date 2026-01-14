<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTeamContext
{
    /**
     * Handle an incoming request.
     *
     * Este middleware asegura que el usuario haya seleccionado un equipo
     * antes de acceder a rutas que requieren contexto de equipo.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Verificar si el usuario está autenticado
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        // Obtener el ID del equipo del parámetro de ruta o sesión
        $teamId = $request->route('team') ?? session('current_team_id');

        // Si no hay equipo seleccionado, redirigir a la selección de equipo
        if (!$teamId) {
            return redirect()
                ->route('teams.index')
                ->with('warning', 'Por favor selecciona un equipo antes de continuar.');
        }

        // Verificar que el usuario pertenezca al equipo
        $user = auth()->user();
        if (!$user->belongsToTeam($teamId)) {
            abort(403, 'No tienes acceso a este equipo.');
        }

        // Guardar el equipo actual en la sesión
        session(['current_team_id' => $teamId]);

        return $next($request);
    }
}
