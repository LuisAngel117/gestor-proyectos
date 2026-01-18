<?php

namespace App\Http\Controllers;

use App\Http\Requests\Comments\StoreCommentRequest;
use App\Http\Requests\Comments\UpdateCommentRequest;
use App\Models\Comment;
use App\Models\CommentRevision;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class TaskCommentController extends Controller
{
    public function index(Project $project, Task $task): JsonResponse
    {
        $this->ensureProjectTaskConsistency($project, $task);
        $this->authorize('view', $task);

        $comments = $task->comments()
            ->with('author:id,name,apellido')
            ->get();

        return response()->json([
            'task_id' => $task->id,
            'comments' => $comments,
        ]);
    }

    public function store(
        Project $project,
        Task $task,
        StoreCommentRequest $request
    ): RedirectResponse|\Illuminate\Http\JsonResponse {
        $this->ensureProjectTaskConsistency($project, $task);
        $this->authorize('create', [Comment::class, $task]);

        $comment = $task->comments()->create([
            'body' => $request->string('body')->toString(),
            'created_by' => $request->user()->id,
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Comentario agregado.',
                'comment' => $comment->load('author:id,name,apellido'),
            ], 201);
        }

        return back()->with('success', 'Comentario agregado.');
    }

    public function update(
        Project $project,
        Task $task,
        Comment $comment,
        UpdateCommentRequest $request
    ): RedirectResponse|\Illuminate\Http\JsonResponse {
        $this->ensureProjectTaskConsistency($project, $task);
        $this->ensureTaskCommentConsistency($task, $comment);
        $this->authorize('update', $comment);

        return DB::transaction(function () use ($request, $comment) {
            $lockedComment = Comment::query()
                ->whereKey($comment->id)
                ->lockForUpdate()
                ->first();

            if (!$lockedComment) {
                abort(404);
            }

            $expectedVersion = (int) $request->input('lock_version');
            $currentVersion = (int) $lockedComment->lock_version;

            if ($expectedVersion !== $currentVersion) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'message' => 'El comentario fue modificado por otro usuario. Actualiza la pagina e intenta de nuevo.',
                    ], 409);
                }
                return back()->withErrors([
                    'lock_version' => 'El comentario fue modificado por otro usuario. Actualiza la pagina e intenta de nuevo.',
                ]);
            }

            $editedAt = now();

            CommentRevision::create([
                'comment_id' => $lockedComment->id,
                'body' => $lockedComment->body,
                'edited_by' => $request->user()->id,
                'edited_at' => $editedAt,
                'lock_version_before' => $currentVersion,
            ]);

            $lockedComment->update([
                'body' => $request->string('body')->toString(),
                'updated_by' => $request->user()->id,
                'edited_at' => $editedAt,
                'edit_count' => $lockedComment->edit_count + 1,
                'lock_version' => $currentVersion + 1,
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Comentario actualizado.',
                    'comment' => $lockedComment->refresh()->load('author:id,name,apellido'),
                ]);
            }

            return back()->with('success', 'Comentario actualizado.');
        });
    }

    public function destroy(Project $project, Task $task, Comment $comment): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $this->ensureProjectTaskConsistency($project, $task);
        $this->ensureTaskCommentConsistency($task, $comment);
        $this->authorize('delete', $comment);

        $comment->delete();

        if (request()->expectsJson()) {
            return response()->json([
                'message' => 'Comentario eliminado.',
            ]);
        }

        return back()->with('success', 'Comentario eliminado.');
    }

    public function revisions(
        Project $project,
        Task $task,
        Comment $comment
    ): JsonResponse {
        $this->ensureProjectTaskConsistency($project, $task);
        $this->ensureTaskCommentConsistency($task, $comment);
        $this->authorize('view', $comment);

        $revisions = $comment->revisions()
            ->with('editor:id,name,apellido')
            ->get();

        return response()->json([
            'comment_id' => $comment->id,
            'revisions' => $revisions,
        ]);
    }

    private function ensureProjectTaskConsistency(Project $project, Task $task): void
    {
        if ($task->project_id !== $project->id) {
            abort(404);
        }
    }

    private function ensureTaskCommentConsistency(Task $task, Comment $comment): void
    {
        if ($comment->task_id !== $task->id) {
            abort(404);
        }
    }
}
