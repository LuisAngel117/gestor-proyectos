<?php

namespace App\Http\Requests;

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProjectMemberStoreRequest extends FormRequest
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
            'user_id' => ['required', 'exists:users,id'],
            'role' => ['nullable', Rule::in(config('acl.roles.project'))],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $project = $this->route('project');
            $userId = $this->input('user_id');

            if (!$project instanceof Project) {
                return;
            }

            $user = User::find($userId);
            if (!$user) {
                return;
            }

            if (!$project->team->hasMember($user)) {
                $validator->errors()->add('user_id', 'El usuario debe pertenecer al team del proyecto.');
            }

            $exists = $project->members()->where('user_id', $userId)->exists();
            if ($exists) {
                $validator->errors()->add('user_id', 'El usuario ya es miembro del proyecto.');
            }
        });
    }
}
