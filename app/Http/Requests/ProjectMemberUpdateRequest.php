<?php

namespace App\Http\Requests;

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProjectMemberUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        $project = $this->route('project');

        return $project instanceof Project
            && $this->user()->can('manageMembers', $project);
    }

    public function rules(): array
    {
        return [
            'role' => ['required', Rule::in(config('acl.roles.project'))],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $project = $this->route('project');
            $member = $this->route('user');

            if (!$project instanceof Project || !$member instanceof User) {
                return;
            }

            $isMember = $project->members()->where('user_id', $member->id)->exists();
            if (!$isMember) {
                $validator->errors()->add('user_id', 'El usuario no pertenece al proyecto.');
            }

            $newRole = $this->input('role');
            if ($newRole !== 'owner') {
                $isOwner = $project->members()
                    ->wherePivot('role', 'owner')
                    ->where('user_id', $member->id)
                    ->exists();

                if ($isOwner) {
                    $ownersCount = $project->members()
                        ->wherePivot('role', 'owner')
                        ->count();

                    if ($ownersCount <= 1) {
                        $validator->errors()->add('role', 'No puedes degradar al último owner del proyecto.');
                    }
                }
            }
        });
    }
}
