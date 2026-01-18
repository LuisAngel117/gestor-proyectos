<?php

namespace Database\Seeders\Demo;

use App\Models\Attachment;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DemoAttachmentsSeeder extends Seeder
{
    public function run(): void
    {
        $projects = Project::query()
            ->whereIn('slug', ['proyecto-demo-a', 'proyecto-demo-b', 'proyecto-demo-c'])
            ->get();

        if ($projects->isEmpty()) {
            $this->command->warn('Demo: proyectos demo no encontrados para adjuntos.');
            return;
        }

        $disk = config('attachments.disk', 'local');
        $basePath = trim(config('attachments.base_path', 'attachments'), '/');

        foreach ($projects as $project) {
            $task = Task::where('project_id', $project->id)->orderBy('id')->first();
            if (!$task) {
                continue;
            }

            $uploader = User::find($project->created_by);
            $uploadedBy = $uploader?->id ?? User::where('email', 'admin@gestor.test')->value('id');

            $directory = $basePath . '/' . $project->id . '/' . $task->id;
            $storedName = (string) Str::uuid() . '.pdf';
            $storedPath = $directory . '/' . $storedName;
            $contents = "%PDF-1.4\n1 0 obj\n<<>>\nendobj\ntrailer\n<<>>\n%%EOF\n";

            if (!Storage::disk($disk)->exists($storedPath)) {
                Storage::disk($disk)->put($storedPath, $contents);
            }

            $size = Storage::disk($disk)->size($storedPath) ?? strlen($contents);

            Attachment::updateOrCreate(
                [
                    'task_id' => $task->id,
                    'original_name' => 'demo-adjunto.pdf',
                ],
                [
                    'project_id' => $project->id,
                    'uploaded_by' => $uploadedBy,
                    'stored_path' => $storedPath,
                    'extension' => 'pdf',
                    'mime_type' => 'application/pdf',
                    'size_bytes' => $size,
                    'checksum_sha256' => hash('sha256', $contents),
                    'disk' => $disk,
                ]
            );
        }

        $this->command->info('Demo: adjuntos creados.');
    }
}
