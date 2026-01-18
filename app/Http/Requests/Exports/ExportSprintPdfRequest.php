<?php

namespace App\Http\Requests\Exports;

use Illuminate\Foundation\Http\FormRequest;

class ExportSprintPdfRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'sprint' => ['nullable', 'string', 'max:50'],
        ];
    }
}
