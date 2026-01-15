<?php

namespace App\Http\Requests;

use App\Support\Catalog;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBacklogItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'priority' => ['required', Rule::in(Catalog::projectPriorities())],
            'status' => ['required', Rule::in(['backlog', 'refinado', 'archivado'])],
        ];
    }
}
