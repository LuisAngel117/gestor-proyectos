<?php

namespace App\Http\Requests\TimeAggregation;

use Illuminate\Foundation\Http\FormRequest;

class SprintTimeSummaryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'include_running' => ['sometimes', 'boolean'],
            'group_by_user' => ['sometimes', 'boolean'],
        ];
    }
}
