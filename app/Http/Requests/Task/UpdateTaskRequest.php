<?php

namespace App\Http\Requests\Task;

use App\Models\BacklogItem;
use App\Models\Sprint;
use App\Models\Task;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'project_id' => ['required', 'integer', 'exists:projects,id'],
            'sprint_id' => ['nullable', 'integer', 'exists:sprints,id'],
            'backlog_item_id' => ['nullable', 'integer', 'exists:backlog_items,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'string', 'max:50'],
            'priority' => ['required', Rule::in(['baja', 'media', 'alta', 'urgente'])],
            'parent_id' => ['nullable', 'integer', Rule::exists('tasks', 'id')->whereNull('deleted_at')],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $projectId = (int) $this->input('project_id');

            if ($this->filled('sprint_id')) {
                $sprint = Sprint::query()->find($this->input('sprint_id'));
                if ($sprint && $sprint->project_id !== $projectId) {
                    $validator->errors()->add('sprint_id', 'El sprint no pertenece al proyecto indicado.');
                }
            }

            if ($this->filled('backlog_item_id')) {
                $backlogItem = BacklogItem::query()->find($this->input('backlog_item_id'));
                if ($backlogItem && $backlogItem->project_id !== $projectId) {
                    $validator->errors()->add('backlog_item_id', 'El backlog no pertenece al proyecto indicado.');
                }
            }

            if ($this->filled('sprint_id') && $this->filled('backlog_item_id')) {
                $backlogItem = BacklogItem::query()->find($this->input('backlog_item_id'));
                if ($backlogItem && $backlogItem->sprint_id && (int) $backlogItem->sprint_id !== (int) $this->input('sprint_id')) {
                    $validator->errors()->add('backlog_item_id', 'El backlog está asignado a otro sprint.');
                }
            }

            if ($this->filled('parent_id')) {
                $parent = Task::query()->find($this->input('parent_id'));
                if ($parent) {
                    if ($parent->project_id !== $projectId) {
                        $validator->errors()->add('parent_id', 'La tarea padre debe pertenecer al mismo proyecto.');
                    }
                    if ($parent->parent_id !== null) {
                        $validator->errors()->add('parent_id', 'Solo se permite un nivel de subtareas.');
                    }
                }
            }

            $task = $this->route('task');
            if ($task instanceof Task && $this->filled('parent_id')) {
                if ((int) $task->id === (int) $this->input('parent_id')) {
                    $validator->errors()->add('parent_id', 'La tarea no puede ser su propia tarea padre.');
                }
            }
        });
    }
}
