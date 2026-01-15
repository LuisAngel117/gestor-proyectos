<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProjectOwnershipTransferRequest;
use App\Models\Project;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class ProjectOwnershipController extends Controller
{
    public function store(ProjectOwnershipTransferRequest $request, Project $project): RedirectResponse
    {
        $this->authorize('transferOwnership', $project);

        $data = $request->validated();
        $newOwnerId = $data['user_id'];

        DB::transaction(function () use ($project, $newOwnerId) {
            $currentOwner = $project->members()
                ->wherePivot('role', 'owner')
                ->first();

            if ($currentOwner && $currentOwner->id !== $newOwnerId) {
                $project->members()->updateExistingPivot($currentOwner->id, ['role' => 'admin']);
            }

            $project->members()->updateExistingPivot($newOwnerId, ['role' => 'owner']);
        });

        return redirect()
            ->route('projects.show', $project)
            ->with('success', 'Ownership transferido correctamente.');
    }
}
