<?php

namespace App\Http\Requests;

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class ProjectOwnershipTransferRequest extends FormRequest
{
    public function authorize(): bool
    {
        $project = $this->route('project');

        return $project instanceof Project
            && $this->user()->can('transferOwnership', $project);
    }

    public function rules(): array
    {
        return [
            'user_id' => ['required', 'exists:users,id'],
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

            $isMember = $project->members()->where('user_id', $userId)->exists();
            if (!$isMember) {
                $validator->errors()->add('user_id', 'El usuario debe ser miembro del proyecto para transferir ownership.');
            }
        });
    }
}
