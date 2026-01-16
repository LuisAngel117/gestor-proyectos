<?php

namespace App\Http\Requests\TaskDependencies;

use App\Models\Task;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreTaskDependencyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'depends_on_task_id' => [
                'required',
                'integer',
                Rule::exists('tasks', 'id')->whereNull('deleted_at'),
            ],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $task = $this->route('task');
            if (!$task instanceof Task) {
                return;
            }

            $dependsOnId = $this->input('depends_on_task_id');
            if (!$dependsOnId) {
                return;
            }

            $dependsOnTask = Task::query()->find($dependsOnId);
            if (!$dependsOnTask) {
                return;
            }

            if ($task->id === $dependsOnTask->id) {
                $validator->errors()->add('depends_on_task_id', 'La tarea no puede depender de sí misma.');
            }

            if ($task->project_id !== $dependsOnTask->project_id) {
                $validator->errors()->add('depends_on_task_id', 'La tarea depende de un proyecto distinto.');
            }

            if ($task->prerequisites()->whereKey($dependsOnTask->id)->exists()) {
                $validator->errors()->add('depends_on_task_id', 'La dependencia ya existe.');
            }

            if ($task->dependents()->whereKey($dependsOnTask->id)->exists()) {
                $validator->errors()->add('depends_on_task_id', 'La dependencia inversa ya existe.');
            }
        });
    }
}
