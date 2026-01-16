<?php

namespace App\Services\Tasks;

use App\Models\Task;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class TaskDependencyCycleDetector
{
    public function __construct(
        private int $maxNodes = 2000,
        private int $maxDepth = 200
    ) {
    }

    public function wouldCreateCycle(Task $task, Task $dependsOn): bool
    {
        if ($task->id === $dependsOn->id) {
            return true;
        }

        $visited = [];
        $frontier = [$dependsOn->id];
        $depth = 0;

        while (!empty($frontier)) {
            if ($depth > $this->maxDepth) {
                throw new RuntimeException('Se excedió el límite de profundidad al validar dependencias.');
            }

            $depth++;
            $neighbors = DB::table('task_dependencies')
                ->join('tasks as source_tasks', 'task_dependencies.task_id', '=', 'source_tasks.id')
                ->join('tasks as dependent_tasks', 'task_dependencies.depends_on_task_id', '=', 'dependent_tasks.id')
                ->where('source_tasks.project_id', $task->project_id)
                ->where('dependent_tasks.project_id', $task->project_id)
                ->whereIn('task_dependencies.task_id', $frontier)
                ->pluck('task_dependencies.depends_on_task_id')
                ->map(fn ($id) => (int) $id)
                ->all();

            $nextFrontier = [];

            foreach ($neighbors as $neighbor) {
                if ($neighbor === $task->id) {
                    return true;
                }

                if (isset($visited[$neighbor])) {
                    continue;
                }

                $visited[$neighbor] = true;
                $nextFrontier[] = $neighbor;

                if (count($visited) > $this->maxNodes) {
                    throw new RuntimeException('Se excedió el límite de nodos al validar dependencias.');
                }
            }

            $frontier = $nextFrontier;
        }

        return false;
    }
}
