<?php

namespace App\Http\Controllers;

use App\Http\Requests\Attachments\DeleteTaskAttachmentRequest;
use App\Http\Requests\Attachments\StoreTaskAttachmentRequest;
use App\Models\Attachment;
use App\Models\Project;
use App\Models\Task;
use App\Services\Attachments\LocalAttachmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TaskAttachmentController extends Controller
{
    public function __construct(private LocalAttachmentService $attachmentService)
    {
    }

    public function index(Project $project, Task $task): JsonResponse
    {
        $this->ensureProjectTaskConsistency($project, $task);
        $this->authorize('view', $task);

        $attachments = $task->attachments()
            ->with(['uploader:id,name,apellido'])
            ->latest()
            ->get()
            ->map(function (Attachment $attachment) use ($project, $task) {
                return [
                    'id' => $attachment->id,
                    'original_name' => $attachment->original_name,
                    'extension' => $attachment->extension,
                    'mime_type' => $attachment->mime_type,
                    'size_bytes' => $attachment->size_bytes,
                    'uploaded_by' => $attachment->uploaded_by,
                    'uploader_name' => $attachment->uploader
                        ? trim($attachment->uploader->name . ' ' . $attachment->uploader->apellido)
                        : null,
                    'created_at' => $attachment->created_at,
                    'download_url' => route('tasks.attachments.download', [$project, $task, $attachment]),
                ];
            });

        return response()->json([
            'task_id' => $task->id,
            'attachments' => $attachments,
        ]);
    }

    public function store(
        StoreTaskAttachmentRequest $request,
        Project $project,
        Task $task
    ): JsonResponse|RedirectResponse {
        $this->ensureProjectTaskConsistency($project, $task);
        $this->authorize('update', $task);

        $file = $request->file('file');
        $attachment = $this->attachmentService->storeForTask($project, $task, $file, $request->user());

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Adjunto cargado.',
                'attachment' => $attachment,
            ], 201);
        }

        return back()->with('success', 'Adjunto cargado.');
    }

    public function download(Project $project, Task $task, Attachment $attachment): StreamedResponse
    {
        $this->ensureProjectTaskConsistency($project, $task);
        $this->ensureTaskAttachmentConsistency($task, $attachment);
        $this->authorize('view', $task);

        return $this->attachmentService->download($attachment);
    }

    public function destroy(
        DeleteTaskAttachmentRequest $request,
        Project $project,
        Task $task,
        Attachment $attachment
    ): JsonResponse|RedirectResponse {
        $this->ensureProjectTaskConsistency($project, $task);
        $this->ensureTaskAttachmentConsistency($task, $attachment);
        $this->authorize('update', $task);

        $this->attachmentService->delete($attachment);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Adjunto eliminado.',
            ]);
        }

        return back()->with('success', 'Adjunto eliminado.');
    }

    private function ensureProjectTaskConsistency(Project $project, Task $task): void
    {
        if ($task->project_id !== $project->id) {
            abort(404);
        }
    }

    private function ensureTaskAttachmentConsistency(Task $task, Attachment $attachment): void
    {
        if ($attachment->task_id !== $task->id || $attachment->project_id !== $task->project_id) {
            abort(404);
        }
    }
}
