<?php

namespace App\Http\Requests\TaskChecklist;

use Illuminate\Foundation\Http\FormRequest;

class StoreChecklistItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'content' => ['required', 'string', 'max:255'],
            'is_completed' => ['sometimes', 'boolean'],
        ];
    }
}
