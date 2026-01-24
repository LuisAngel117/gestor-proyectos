<?php

namespace App\Http\Requests\Messages;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Validator;

class StoreMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'scope_type' => ['required', 'in:team,project'],
            'scope_id' => ['required', 'integer'],
            'recipient_id' => ['nullable', 'integer'],
            'body' => ['required', 'string', 'max:2000'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $user = $this->user();
            $scopeType = $this->input('scope_type');
            $scopeId = (int) $this->input('scope_id');
            $recipientId = $this->input('recipient_id');

            if (!$user) {
                $validator->errors()->add('scope_id', 'Usuario no valido.');
                return;
            }

            if ($scopeType === 'team') {
                if (!$user->belongsToTeam($scopeId)) {
                    $validator->errors()->add('scope_id', 'Equipo no valido.');
                    return;
                }

                $role = $user->roleInTeam($scopeId);
                if ($role === 'observer') {
                    $validator->errors()->add('scope_id', 'No tienes permisos para enviar mensajes en este equipo.');
                }

                if ($recipientId) {
                    $exists = DB::table('team_user')
                        ->where('team_id', $scopeId)
                        ->where('user_id', $recipientId)
                        ->exists();

                    if (!$exists) {
                        $validator->errors()->add('recipient_id', 'Destinatario no pertenece a este equipo.');
                    }
                }
            }

            if ($scopeType === 'project') {
                if (!$user->projects()->where('project_id', $scopeId)->exists()) {
                    $validator->errors()->add('scope_id', 'Proyecto no valido.');
                    return;
                }

                $role = $user->roleInProject($scopeId);
                if ($role === 'observer') {
                    $validator->errors()->add('scope_id', 'No tienes permisos para enviar mensajes en este proyecto.');
                }

                if ($recipientId) {
                    $exists = DB::table('project_user')
                        ->where('project_id', $scopeId)
                        ->where('user_id', $recipientId)
                        ->exists();

                    if (!$exists) {
                        $validator->errors()->add('recipient_id', 'Destinatario no pertenece a este proyecto.');
                    }
                }
            }
        });
    }
}
