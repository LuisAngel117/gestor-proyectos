<?php

namespace App\Http\Requests;

use App\Models\BacklogItem;
use App\Models\Project;
use App\Models\Sprint;
use Illuminate\Foundation\Http\FormRequest;

class SprintUnassignBacklogItemsRequest extends FormRequest
{
    public function authorize(): bool
    {
        $sprint = $this->route('sprint');

        return $sprint instanceof Sprint
            && $this->user()->can('plan', $sprint);
    }

    public function rules(): array
    {
        return [
            'items' => ['required', 'array', 'min:1'],
            'items.*' => ['integer', 'exists:backlog_items,id'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $project = $this->route('project');
            $sprint = $this->route('sprint');

            if (!$project instanceof Project || !$sprint instanceof Sprint) {
                return;
            }

            $items = BacklogItem::query()
                ->whereIn('id', $this->input('items', []))
                ->get();

            foreach ($items as $item) {
                if ($item->project_id !== $project->id || $item->sprint_id !== $sprint->id) {
                    $validator->errors()->add('items', 'Todos los ítems deben pertenecer al sprint seleccionado.');
                    break;
                }
            }
        });
    }
}
