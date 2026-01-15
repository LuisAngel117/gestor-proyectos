<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReorderBacklogRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'positions' => ['required', 'array'],
            'positions.*' => ['required', 'integer', 'min:1'],
        ];
    }
}
