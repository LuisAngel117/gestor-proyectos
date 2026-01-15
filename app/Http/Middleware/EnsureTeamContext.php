<?php

namespace App\Http\Middleware;

use App\Models\Project;
use App\Models\Team;
use App\Support\Context\TeamContext;
use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTeamContext
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return $next($request);
        }

        $team = $this->resolveTeamCandidate($request);

        if (!$team) {
            return $this->handleMissingContext($request, $user->teams()->count());
        }

        if (!$user->isSuperadmin() && !$user->belongsToTeam($team->id)) {
            TeamContext::clear();
            abort(403);
        }

        TeamContext::set($team->id, $team->name);

        $project = $request->route('project');
        if ($project) {
            $projectModel = $project instanceof Project ? $project : Project::find($project);
            if ($projectModel && $projectModel->team_id !== $team->id) {
                abort(403);
            }
        }

        return $next($request);
    }

    private function resolveTeamCandidate(Request $request): ?Team
    {
        $teamParam = $request->route('team');
        if ($teamParam) {
            return $teamParam instanceof Team ? $teamParam : Team::find($teamParam);
        }

        $teamId = TeamContext::get();
        if ($teamId) {
            return Team::find($teamId);
        }

        $user = $request->user();
        if (!$user) {
            return null;
        }

        $teamIds = $user->teams()->pluck('teams.id');
        if ($teamIds->count() === 1) {
            return Team::find($teamIds->first());
        }

        return null;
    }

    private function handleMissingContext(Request $request, int $teamCount): RedirectResponse
    {
        TeamContext::clear();
        $request->session()->put('url.intended', $request->fullUrl());

        $message = $teamCount === 0
            ? 'No tienes equipos disponibles. Crea un equipo para continuar.'
            : 'Selecciona un equipo para continuar.';

        return redirect()
            ->route('teams.index')
            ->with('warning', $message);
    }
}
