<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProjectMemberStoreRequest;
use App\Http\Requests\ProjectMemberUpdateRequest;
use App\Models\Project;
use App\Models\User;
use App\Support\AuditLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class ProjectMemberController extends Controller
{
    public function index(Project $project): JsonResponse
    {
        $this->authorize('view', $project);

        $members = $project->members()
            ->withPivot('role', 'joined_at')
            ->get();

        return response()->json($members);
    }

    public function store(ProjectMemberStoreRequest $request, Project $project): RedirectResponse
    {
        $this->authorize('manageMembers', $project);

        $data = $request->validated();
        $role = $data['role'] ?? 'member';
        $user = User::findOrFail($data['user_id']);

        $project->addMember($user, $role);
        AuditLogger::log($request->user(), 'project.member.add', $project, [
            'member_id' => $user->id,
            'role' => $role,
        ]);

        return redirect()
            ->to(route('projects.show', $project) . '#project-assistant')
            ->with('success', 'Miembro agregado al proyecto.');
    }

    public function update(ProjectMemberUpdateRequest $request, Project $project, User $user): RedirectResponse
    {
        $this->authorize('manageMembers', $project);

        $data = $request->validated();
        $project->updateMemberRole($user, $data['role']);
        AuditLogger::log($request->user(), 'project.member.role', $project, [
            'member_id' => $user->id,
            'role' => $data['role'],
        ]);

        return redirect()
            ->route('projects.show', $project)
            ->with('success', 'Rol actualizado para el miembro.');
    }

    public function destroy(Project $project, User $user): RedirectResponse
    {
        $this->authorize('manageMembers', $project);

        $role = $project->getUserRole($user);
        if ($role === 'owner') {
            $ownersCount = $project->members()
                ->wherePivot('role', 'owner')
                ->count();

            if ($ownersCount <= 1) {
                return redirect()
                    ->route('projects.show', $project)
                    ->withErrors(['user_id' => 'No puedes remover al último owner del proyecto.']);
            }
        }

        $project->removeMember($user);
        AuditLogger::log(request()->user(), 'project.member.remove', $project, [
            'member_id' => $user->id,
        ]);

        return redirect()
            ->route('projects.show', $project)
            ->with('success', 'Miembro removido del proyecto.');
    }
}
