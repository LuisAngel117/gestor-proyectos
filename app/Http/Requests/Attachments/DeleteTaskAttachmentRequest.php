<?php

namespace App\Http\Requests\Attachments;

use Illuminate\Foundation\Http\FormRequest;

class DeleteTaskAttachmentRequest extends FormRequest
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
