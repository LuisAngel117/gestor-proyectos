<?php

namespace App\Services\Tasks;

use App\Models\Task;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class TaskDependencyService
{
    public function __construct(private TaskDependencyCycleDetector $cycleDetector)
    {
    }

    public function addDependency(Task $task, Task $dependsOn): void
    {
        if ($task->id === $dependsOn->id) {
            throw ValidationException::withMessages([
                'depends_on_task_id' => 'La tarea no puede depender de sí misma.',
            ]);
        }

        if ($task->project_id !== $dependsOn->project_id) {
            throw ValidationException::withMessages([
                'depends_on_task_id' => 'La tarea depende de un proyecto distinto.',
            ]);
        }

        if ($task->prerequisites()->whereKey($dependsOn->id)->exists()) {
            throw ValidationException::withMessages([
                'depends_on_task_id' => 'La dependencia ya existe.',
            ]);
        }

        if ($task->dependents()->whereKey($dependsOn->id)->exists()) {
            throw ValidationException::withMessages([
                'depends_on_task_id' => 'La dependencia inversa ya existe.',
            ]);
        }

        try {
            if ($this->cycleDetector->wouldCreateCycle($task, $dependsOn)) {
                throw ValidationException::withMessages([
                    'depends_on_task_id' => 'No se puede crear la dependencia porque generaría un ciclo.',
                ]);
            }
        } catch (RuntimeException $exception) {
            throw ValidationException::withMessages([
                'depends_on_task_id' => $exception->getMessage(),
            ]);
        }

        $task->prerequisites()->attach($dependsOn->id);
    }

    public function removeDependency(Task $task, Task $dependsOn): void
    {
        $task->prerequisites()->detach($dependsOn->id);
    }
}
