<?php

namespace App\Http\Controllers;

use App\Http\Requests\SprintAssignBacklogItemsRequest;
use App\Http\Requests\SprintReorderBacklogItemsRequest;
use App\Http\Requests\SprintUnassignBacklogItemsRequest;
use App\Models\BacklogItem;
use App\Models\Project;
use App\Models\Sprint;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SprintPlanningController extends Controller
{
    public function show(Project $project, Sprint $sprint): View
    {
        $this->authorize('view', $sprint);

        $availableItems = $project->backlogItems()
            ->unassigned()
            ->where('status', '!=', 'archivado')
            ->orderBy('position')
            ->get();

        $sprintItems = $sprint->backlogItems()
            ->where('project_id', $project->id)
            ->orderBy('sprint_position')
            ->get();

        return view('sprints.plan', [
            'project' => $project,
            'sprint' => $sprint,
            'availableItems' => $availableItems,
            'sprintItems' => $sprintItems,
        ]);
    }

    public function assign(
        SprintAssignBacklogItemsRequest $request,
        Project $project,
        Sprint $sprint
    ): RedirectResponse {
        $this->authorize('plan', $sprint);

        $itemIds = $request->validated()['items'];
        $maxPosition = (int) $sprint->backlogItems()->max('sprint_position');

        DB::transaction(function () use ($itemIds, $sprint, &$maxPosition) {
            foreach ($itemIds as $itemId) {
                $maxPosition++;
                BacklogItem::where('id', $itemId)->update([
                    'sprint_id' => $sprint->id,
                    'sprint_position' => $maxPosition,
                ]);
            }
        });

        return redirect()
            ->route('sprints.plan', [$project, $sprint])
            ->with('success', 'Ítems asignados al sprint.');
    }

    public function unassign(
        SprintUnassignBacklogItemsRequest $request,
        Project $project,
        Sprint $sprint
    ): RedirectResponse {
        $this->authorize('plan', $sprint);

        $itemIds = $request->validated()['items'];
        $maxBacklogPosition = (int) $project->backlogItems()
            ->unassigned()
            ->max('position');

        DB::transaction(function () use ($itemIds, $project, &$maxBacklogPosition) {
            foreach ($itemIds as $itemId) {
                $maxBacklogPosition++;
                BacklogItem::where('id', $itemId)->update([
                    'sprint_id' => null,
                    'sprint_position' => null,
                    'position' => $maxBacklogPosition,
                ]);
            }
        });

        return redirect()
            ->route('sprints.plan', [$project, $sprint])
            ->with('success', 'Ítems devueltos al backlog.');
    }

    public function reorder(
        SprintReorderBacklogItemsRequest $request,
        Project $project,
        Sprint $sprint
    ): RedirectResponse {
        $this->authorize('reorderBacklog', $sprint);

        $positions = $request->validated()['positions'];
        asort($positions);

        DB::transaction(function () use ($positions) {
            $orderedIds = array_keys($positions);
            foreach ($orderedIds as $index => $itemId) {
                BacklogItem::where('id', $itemId)->update([
                    'sprint_position' => $index + 1,
                ]);
            }
        });

        return redirect()
            ->route('sprints.plan', [$project, $sprint])
            ->with('success', 'Orden del sprint actualizado.');
    }
}
