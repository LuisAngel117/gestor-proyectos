<?php

namespace App\Http\Requests;

use App\Models\Project;
use App\Models\Sprint;
use Illuminate\Foundation\Http\FormRequest;

class SprintReorderBacklogItemsRequest extends FormRequest
{
    public function authorize(): bool
    {
        $sprint = $this->route('sprint');

        return $sprint instanceof Sprint
            && $this->user()->can('reorderBacklog', $sprint);
    }

    public function rules(): array
    {
        return [
            'positions' => ['required', 'array', 'min:1'],
            'positions.*' => ['integer', 'min:1'],
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

            $expectedIds = $sprint->backlogItems()
                ->where('project_id', $project->id)
                ->pluck('id')
                ->map(fn ($id) => (string) $id)
                ->all();

            $providedIds = array_keys($this->input('positions', []));

            sort($expectedIds);
            sort($providedIds);

            if ($expectedIds !== $providedIds) {
                $validator->errors()->add('positions', 'El reordenamiento debe incluir todos los ítems del sprint.');
            }
        });
    }
}
