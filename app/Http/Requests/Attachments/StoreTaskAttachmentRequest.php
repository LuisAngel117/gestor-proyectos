<?php

namespace App\Http\Requests\Attachments;

use Illuminate\Foundation\Http\FormRequest;

class StoreTaskAttachmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $maxMb = (int) config('attachments.max_size_mb', 10);
        $maxKb = $maxMb * 1024;
        $extensions = (array) config('attachments.allowed_extensions', []);
        $mimetypes = (array) config('attachments.allowed_mimetypes', []);

        $fileRules = ['required', 'file', 'max:' . $maxKb];

        if (!empty($extensions)) {
            $fileRules[] = 'mimes:' . implode(',', $extensions);
        }

        if (!empty($mimetypes)) {
            $fileRules[] = 'mimetypes:' . implode(',', $mimetypes);
        }

        return [
            'file' => $fileRules,
        ];
    }
}
