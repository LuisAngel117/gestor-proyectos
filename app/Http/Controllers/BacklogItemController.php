<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReorderBacklogRequest;
use App\Http\Requests\StoreBacklogItemRequest;
use App\Http\Requests\UpdateBacklogItemRequest;
use App\Models\BacklogItem;
use App\Models\Project;
use App\Support\Catalog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class BacklogItemController extends Controller
{
    public function index(Project $project): View
    {
        $this->authorize('viewAny', [BacklogItem::class, $project]);

        $items = $project->backlogItems()
            ->orderBy('position')
            ->paginate(20);

        return view('backlog.index', [
            'project' => $project,
            'items' => $items,
            'priorities' => Catalog::projectPriorityLabels(),
            'priorityColors' => [
                'baja' => 'success',
                'media' => 'info',
                'alta' => 'warning',
                'urgente' => 'danger',
            ],
        ]);
    }

    public function create(Project $project): View
    {
        $this->authorize('create', [BacklogItem::class, $project]);

        return view('backlog.create', [
            'project' => $project,
            'priorities' => Catalog::projectPriorityLabels(),
        ]);
    }

    public function store(StoreBacklogItemRequest $request, Project $project): RedirectResponse
    {
        $this->authorize('create', [BacklogItem::class, $project]);

        $position = (int) $project->backlogItems()->max('position');
        $position = $position > 0 ? $position + 1 : 1;

        $project->backlogItems()->create([
            'name' => $request->string('name')->toString(),
            'description' => $request->input('description'),
            'priority' => $request->string('priority')->toString(),
            'status' => 'backlog',
            'position' => $position,
            'created_by' => Auth::id(),
        ]);

        return redirect()
            ->route('backlog.index', $project)
            ->with('success', 'Ítem de backlog creado.');
    }

    public function edit(Project $project, BacklogItem $backlogItem): View
    {
        $this->authorize('update', $backlogItem);

        return view('backlog.edit', [
            'project' => $project,
            'item' => $backlogItem,
            'priorities' => Catalog::projectPriorityLabels(),
        ]);
    }

    public function update(UpdateBacklogItemRequest $request, Project $project, BacklogItem $backlogItem): RedirectResponse
    {
        $this->authorize('update', $backlogItem);

        $backlogItem->update([
            'name' => $request->string('name')->toString(),
            'description' => $request->input('description'),
            'priority' => $request->string('priority')->toString(),
            'status' => $request->string('status')->toString(),
        ]);

        return redirect()
            ->route('backlog.index', $project)
            ->with('success', 'Ítem de backlog actualizado.');
    }

    public function destroy(Project $project, BacklogItem $backlogItem): RedirectResponse
    {
        $this->authorize('delete', $backlogItem);

        $backlogItem->delete();

        return redirect()
            ->route('backlog.index', $project)
            ->with('success', 'Ítem de backlog archivado.');
    }

    public function reorder(ReorderBacklogRequest $request, Project $project): RedirectResponse
    {
        $this->authorize('reorder', [BacklogItem::class, $project]);

        $positions = $request->validated()['positions'];
        $items = $project->backlogItems()->orderBy('position')->get();

        $itemIds = $items->pluck('id')->map(fn ($id) => (string) $id)->all();
        $providedIds = array_keys($positions);

        if (count($itemIds) !== count($providedIds) || array_diff($itemIds, $providedIds)) {
            return back()->withErrors([
                'positions' => 'El reordenamiento debe incluir todos los ítems del backlog.',
            ]);
        }

        asort($positions);

        DB::transaction(function () use ($positions, $items) {
            $orderedIds = array_keys($positions);
            foreach ($orderedIds as $index => $itemId) {
                $item = $items->firstWhere('id', (int) $itemId);
                if ($item) {
                    $item->update(['position' => $index + 1]);
                }
            }
        });

        return redirect()
            ->route('backlog.index', $project)
            ->with('success', 'Backlog reordenado.');
    }
}
