<?php

namespace App\Http\Requests\TaskTimer;

use Illuminate\Foundation\Http\FormRequest;

class StartTaskTimerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [];
    }
}
