<?php

namespace App\Http\Controllers;

use App\Http\Requests\TeamMemberStoreRequest;
use App\Http\Requests\TeamMemberUpdateRequest;
use App\Models\Team;
use App\Models\User;
use App\Support\AuditLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class TeamMemberController extends Controller
{
    public function index(Team $team): JsonResponse
    {
        $this->authorize('view', $team);

        $members = $team->users()
            ->withPivot('role', 'joined_at')
            ->get();

        return response()->json($members);
    }

    public function store(TeamMemberStoreRequest $request, Team $team): RedirectResponse
    {
        $this->authorize('manageMembers', $team);

        $data = $request->validated();
        $user = User::findOrFail($data['user_id']);

        $team->addMember($user, $data['role']);
        AuditLogger::log($request->user(), 'team.member.add', $team, [
            'member_id' => $user->id,
            'role' => $data['role'],
        ]);

        return redirect()
            ->to(route('teams.show', $team) . '#team-projects')
            ->with('success', 'Miembro agregado al equipo.');
    }

    public function update(TeamMemberUpdateRequest $request, Team $team, User $user): RedirectResponse
    {
        $this->authorize('manageMembers', $team);

        if ($team->owner_id === $user->id) {
            return back()->withErrors([
                'role' => 'No puedes cambiar el rol del owner del equipo.',
            ]);
        }

        $data = $request->validated();
        $team->updateMemberRole($user, $data['role']);
        AuditLogger::log($request->user(), 'team.member.role', $team, [
            'member_id' => $user->id,
            'role' => $data['role'],
        ]);

        return redirect()
            ->route('teams.show', $team)
            ->with('success', 'Rol actualizado.');
    }

    public function destroy(Team $team, User $user): RedirectResponse
    {
        $this->authorize('manageMembers', $team);

        if ($team->owner_id === $user->id) {
            return back()->withErrors([
                'user_id' => 'No puedes remover al owner del equipo.',
            ]);
        }

        $team->removeMember($user);
        AuditLogger::log(request()->user(), 'team.member.remove', $team, [
            'member_id' => $user->id,
        ]);

        return redirect()
            ->route('teams.show', $team)
            ->with('success', 'Miembro removido del equipo.');
    }
}
