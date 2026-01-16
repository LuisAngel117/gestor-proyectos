<?php

namespace App\Http\Requests\TaskChecklist;

use App\Models\Task;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class ReorderChecklistItemsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'ordered_ids' => ['required', 'array', 'min:1'],
            'ordered_ids.*' => [
                'integer',
                Rule::exists('task_checklist_items', 'id'),
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

            $ids = $this->input('ordered_ids', []);
            if (empty($ids)) {
                return;
            }

            $itemsCount = $task->checklistItems()->whereIn('id', $ids)->count();
            if ($itemsCount !== count($ids)) {
                $validator->errors()->add('ordered_ids', 'Los ítems no pertenecen a la tarea indicada.');
            }
        });
    }
}
