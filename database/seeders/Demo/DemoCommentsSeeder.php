<?php

namespace Database\Seeders\Demo;

use App\Models\Comment;
use App\Models\CommentRevision;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class DemoCommentsSeeder extends Seeder
{
    public function run(): void
    {
        $projects = Project::query()
            ->whereIn('slug', ['proyecto-demo-a', 'proyecto-demo-b', 'proyecto-demo-c'])
            ->get();

        foreach ($projects as $project) {
            $authors = $project->members()
                ->wherePivotIn('role', ['owner', 'admin', 'member'])
                ->get();

            if ($authors->isEmpty()) {
                continue;
            }

            $tasks = Task::where('project_id', $project->id)
                ->orderBy('id')
                ->get()
                ->values()
                ->filter(fn (Task $task, int $index) => $index % 4 === 0)
                ->take(5);

            foreach ($tasks as $index => $task) {
                $author = $authors[$index % $authors->count()];

                $comment = Comment::create([
                    'task_id' => $task->id,
                    'body' => 'Comentario demo inicial.',
                    'created_by' => $author->id,
                    'lock_version' => 1,
                    'edit_count' => 0,
                ]);

                Comment::create([
                    'task_id' => $task->id,
                    'body' => 'Comentario demo adicional.',
                    'created_by' => $author->id,
                    'lock_version' => 1,
                    'edit_count' => 0,
                ]);

                $editedAt = Carbon::now()->subHours(2);
                CommentRevision::create([
                    'comment_id' => $comment->id,
                    'body' => $comment->body,
                    'edited_by' => $author->id,
                    'edited_at' => $editedAt,
                    'lock_version_before' => 1,
                ]);

                $comment->update([
                    'body' => 'Comentario demo editado.',
                    'updated_by' => $author->id,
                    'edited_at' => $editedAt,
                    'edit_count' => 1,
                    'lock_version' => 2,
                ]);
            }
        }

        $this->command->info('Demo: comentarios creados.');
    }
}
