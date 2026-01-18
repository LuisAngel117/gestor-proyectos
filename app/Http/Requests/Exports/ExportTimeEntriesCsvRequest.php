<?php

namespace App\Http\Requests\Exports;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ExportTimeEntriesCsvRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'from' => ['required', 'date'],
            'to' => ['required', 'date', 'after_or_equal:from'],
            'task_id' => ['nullable', 'integer', 'exists:tasks,id'],
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'source' => ['nullable', 'string', Rule::in(['manual', 'timer'])],
        ];
    }
}
