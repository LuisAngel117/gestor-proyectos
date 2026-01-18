<?php

namespace App\Http\Requests\TimeEntries;

use App\Services\TimeTracking\TimeEntryValidationService;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateTaskTimeEntryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => ['sometimes', 'integer', 'exists:users,id'],
            'started_at' => ['required', 'date'],
            'stopped_at' => ['required', 'date', 'after:started_at'],
            'note' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $task = $this->route('task');
            $entry = $this->route('timeEntry');
            $user = $this->user();

            if (!$task || !$entry || !$user) {
                return;
            }

            if ($entry->stopped_at === null) {
                $validator->errors()->add('stopped_at', 'No se puede editar una entrada con temporizador activo.');
                return;
            }

            $userId = (int) $this->input('user_id', $entry->user_id);
            $teamRole = $user->roleInTeam($task->project->team_id);
            if (
                $user->roleInProject($task->project_id) === 'member'
                && !in_array($teamRole, ['owner', 'admin'], true)
                && $userId !== $user->id
            ) {
                $validator->errors()->add('user_id', 'Solo puedes editar tu propio tiempo.');
                return;
            }

            $start = Carbon::parse($this->input('started_at'));
            $end = Carbon::parse($this->input('stopped_at'));

            $validationService = app(TimeEntryValidationService::class);

            if ($validationService->hasOverlap($userId, $start, $end, $entry->id)) {
                $validator->errors()->add('started_at', 'La entrada se solapa con otra existente.');
            }

            $duration = $validationService->calculateDurationSeconds($start, $end);
            if (!$validationService->isDurationWithinLimits($duration)) {
                $validator->errors()->add('stopped_at', 'La duracion debe estar entre 1 minuto y 12 horas.');
            }
        });
    }
}
