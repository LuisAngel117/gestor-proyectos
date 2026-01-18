<?php

namespace App\Http\Requests\TimeAggregation;

use Illuminate\Foundation\Http\FormRequest;

class TaskTimeSummaryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'include_subtasks' => ['sometimes', 'boolean'],
            'include_running' => ['sometimes', 'boolean'],
        ];
    }
}
