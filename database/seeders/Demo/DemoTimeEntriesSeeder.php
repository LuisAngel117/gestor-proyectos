<?php

namespace Database\Seeders\Demo;

use App\Models\Project;
use App\Models\Task;
use App\Models\TaskTimeEntry;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class DemoTimeEntriesSeeder extends Seeder
{
    public function run(): void
    {
        $projects = Project::query()
            ->whereIn('slug', ['proyecto-demo-a', 'proyecto-demo-b', 'proyecto-demo-c'])
            ->get();

        foreach ($projects as $project) {
            $tasks = Task::query()
                ->where('project_id', $project->id)
                ->whereNotNull('sprint_id')
                ->orderBy('id')
                ->get();

            $lastEndByUser = [];
            $created = 0;

            foreach ($tasks as $index => $task) {
                if ($created >= 8) {
                    break;
                }

                $assignee = $task->assignees()->orderBy('task_user.assigned_at')->first();
                if (!$assignee) {
                    continue;
                }

                $startBase = $task->sprint?->start_date
                    ? Carbon::parse($task->sprint->start_date)->setTime(9, 0)
                    : now()->subDays(5)->setTime(9, 0);

                $userKey = (string) $assignee->id;
                $start = $lastEndByUser[$userKey] ?? $startBase->copy()->addDays($index % 3);
                if (isset($lastEndByUser[$userKey])) {
                    $start = $lastEndByUser[$userKey]->copy()->addMinutes(30);
                }

                $duration = 1800 + (($index % 4) * 900);
                $end = $start->copy()->addSeconds($duration);

                $sprintEnd = $task->sprint?->end_date
                    ? Carbon::parse($task->sprint->end_date)->endOfDay()
                    : now()->endOfDay();

                if ($end->greaterThan($sprintEnd)) {
                    $end = $sprintEnd->copy();
                }

                if ($end->lessThanOrEqualTo($start)) {
                    continue;
                }

                $durationSeconds = max(60, $end->diffInSeconds($start));

                TaskTimeEntry::create([
                    'task_id' => $task->id,
                    'user_id' => $assignee->id,
                    'started_at' => $start,
                    'stopped_at' => $end,
                    'duration_seconds' => $durationSeconds,
                    'source' => $index % 2 === 0 ? 'manual' : 'timer',
                    'note' => 'Demo entry',
                    'created_by' => $assignee->id,
                ]);

                $lastEndByUser[$userKey] = $end->copy();
                $created++;
            }
        }

        $this->command->info('Demo: time entries creadas.');
    }
}
