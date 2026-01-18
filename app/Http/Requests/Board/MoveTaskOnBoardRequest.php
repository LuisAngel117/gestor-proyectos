<?php

namespace App\Http\Requests\Board;

use Illuminate\Foundation\Http\FormRequest;

class MoveTaskOnBoardRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', 'string', 'in:todo,en_progreso,hecho'],
        ];
    }
}
