<?php

namespace App\Http\Requests\TimeEntries;

use App\Services\TimeTracking\TimeEntryValidationService;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreTaskTimeEntryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'started_at' => ['required', 'date'],
            'stopped_at' => ['required', 'date', 'after:started_at'],
            'note' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $task = $this->route('task');
            $user = $this->user();

            if (!$task || !$user) {
                return;
            }

            $userId = (int) $this->input('user_id');
            $teamRole = $user->roleInTeam($task->project->team_id);
            if (
                $user->roleInProject($task->project_id) === 'member'
                && !in_array($teamRole, ['owner', 'admin'], true)
                && $userId !== $user->id
            ) {
                $validator->errors()->add('user_id', 'Solo puedes registrar tu propio tiempo.');
                return;
            }

            $start = Carbon::parse($this->input('started_at'));
            $end = Carbon::parse($this->input('stopped_at'));

            $validationService = app(TimeEntryValidationService::class);

            if ($validationService->hasActiveTimerForUser($userId)) {
                $validator->errors()->add('user_id', 'Tienes un temporizador activo para este usuario.');
            }

            if ($validationService->hasOverlap($userId, $start, $end)) {
                $validator->errors()->add('started_at', 'La entrada se solapa con otra existente.');
            }

            $duration = $validationService->calculateDurationSeconds($start, $end);
            if (!$validationService->isDurationWithinLimits($duration)) {
                $validator->errors()->add('stopped_at', 'La duracion debe estar entre 1 minuto y 12 horas.');
            }
        });
    }
}
