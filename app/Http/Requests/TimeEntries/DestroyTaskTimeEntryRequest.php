<?php

namespace App\Http\Requests\TimeEntries;

use Illuminate\Foundation\Http\FormRequest;

class DestroyTaskTimeEntryRequest extends FormRequest
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
