<?php

namespace App\Http\Requests\TaskAssignees;

use Illuminate\Foundation\Http\FormRequest;

class UnassignTaskAssigneeRequest extends FormRequest
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
