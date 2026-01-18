<?php

namespace App\Http\Requests\TaskAssignees;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Validator;

class AssignTaskAssigneesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_ids' => ['required', 'array', 'min:1'],
            'user_ids.*' => ['integer', 'distinct', 'exists:users,id'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $task = $this->route('task');
            $project = $this->route('project');

            if (!is_object($task) || !is_object($project)) {
                return;
            }

            if ($task->project_id !== $project->id) {
                $validator->errors()->add('project_id', 'La tarea no pertenece al proyecto.');
                return;
            }

            $userIds = array_map('intval', $this->input('user_ids', []));
            if (empty($userIds)) {
                return;
            }

            $projectMemberIds = DB::table('project_user')
                ->where('project_id', $project->id)
                ->whereIn('user_id', $userIds)
                ->pluck('user_id')
                ->map(fn ($id) => (int) $id)
                ->all();

            $teamAdminIds = DB::table('team_user')
                ->where('team_id', $project->team_id)
                ->whereIn('user_id', $userIds)
                ->whereIn('role', ['owner', 'admin'])
                ->pluck('user_id')
                ->map(fn ($id) => (int) $id)
                ->all();

            $teamOwnerId = DB::table('teams')
                ->where('id', $project->team_id)
                ->value('owner_id');

            $ownerIds = $teamOwnerId ? [(int) $teamOwnerId] : [];
            $allowedIds = array_unique(array_merge($projectMemberIds, $teamAdminIds, $ownerIds));
            $invalidIds = array_diff($userIds, $allowedIds);

            if (!empty($invalidIds)) {
                $validator->errors()->add('user_ids', 'Uno o mas usuarios no pertenecen al proyecto.');
            }
        });
    }
}
