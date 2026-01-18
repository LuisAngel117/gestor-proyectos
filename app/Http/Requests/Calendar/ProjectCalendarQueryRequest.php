<?php

namespace App\Http\Requests\Calendar;

use App\Services\Boards\ScrumBoardService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProjectCalendarQueryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'month' => ['nullable', 'date_format:Y-m'],
            'sprint' => ['nullable', 'string', 'max:50'],
            'status' => ['nullable', 'string', Rule::in(array_keys(ScrumBoardService::STATUSES))],
            'assignee' => ['nullable', 'integer', 'exists:users,id'],
        ];
    }
}
