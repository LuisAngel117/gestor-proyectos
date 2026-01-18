<?php

namespace App\Services\Attachments;

use App\Models\Attachment;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LocalAttachmentService
{
    public function storeForTask(Project $project, Task $task, UploadedFile $file, User $actor): Attachment
    {
        $disk = config('attachments.disk', 'local');
        $basePath = trim(config('attachments.base_path', 'attachments'), '/');

        $directory = $basePath . '/' . $project->id . '/' . $task->id;
        $extension = strtolower($file->getClientOriginalExtension() ?: $file->extension() ?: '');
        $storedName = (string) Str::uuid() . ($extension ? '.' . $extension : '');
        $originalName = Str::limit($file->getClientOriginalName(), 255, '');
        $checksum = $file->getRealPath() ? hash_file('sha256', $file->getRealPath()) : null;

        $storedPath = Storage::disk($disk)->putFileAs($directory, $file, $storedName);
        if (!$storedPath) {
            abort(500, 'No se pudo guardar el archivo.');
        }

        try {
            return Attachment::create([
                'project_id' => $project->id,
                'task_id' => $task->id,
                'uploaded_by' => $actor->id,
                'original_name' => $originalName,
                'stored_path' => $storedPath,
                'extension' => $extension,
                'mime_type' => $file->getMimeType() ?? 'application/octet-stream',
                'size_bytes' => $file->getSize() ?? 0,
                'checksum_sha256' => $checksum,
                'disk' => $disk,
            ]);
        } catch (\Throwable $exception) {
            Storage::disk($disk)->delete($storedPath);
            throw $exception;
        }
    }

    public function download(Attachment $attachment): StreamedResponse
    {
        $disk = $attachment->disk ?: config('attachments.disk', 'local');

        if (!Storage::disk($disk)->exists($attachment->stored_path)) {
            abort(404);
        }

        return Storage::disk($disk)->download($attachment->stored_path, $attachment->original_name);
    }

    public function delete(Attachment $attachment): void
    {
        $attachment->delete();
    }
}
