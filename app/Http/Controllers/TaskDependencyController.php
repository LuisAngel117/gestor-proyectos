<?php

namespace App\Http\Controllers;

use App\Http\Requests\TaskDependencies\StoreTaskDependencyRequest;
use App\Models\Project;
use App\Models\Task;
use App\Services\Tasks\TaskDependencyService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;

class TaskDependencyController extends Controller
{
    public function __construct(private TaskDependencyService $dependencyService)
    {
    }

    public function index(Project $project, Task $task): JsonResponse
    {
        $this->ensureProjectTaskConsistency($project, $task);
        $this->authorize('view', $task);

        return response()->json([
            'task_id' => $task->id,
            'dependencies' => $task->prerequisites()->get(['tasks.id', 'tasks.title']),
            'dependents' => $task->dependents()->get(['tasks.id', 'tasks.title']),
        ]);
    }

    public function store(
        Project $project,
        Task $task,
        StoreTaskDependencyRequest $request
    ): RedirectResponse {
        $this->ensureProjectTaskConsistency($project, $task);
        $this->authorize('manageDependencies', $task);

        $dependsOn = Task::query()->findOrFail($request->validated()['depends_on_task_id']);
        $this->dependencyService->addDependency($task, $dependsOn);

        return back()->with('success', 'Dependencia agregada.');
    }

    public function destroy(Project $project, Task $task, Task $dependsOnTask): RedirectResponse
    {
        $this->ensureProjectTaskConsistency($project, $task);
        $this->ensureProjectTaskConsistency($project, $dependsOnTask);
        $this->authorize('manageDependencies', $task);

        $this->dependencyService->removeDependency($task, $dependsOnTask);

        return back()->with('success', 'Dependencia eliminada.');
    }

    private function ensureProjectTaskConsistency(Project $project, Task $task): void
    {
        if ($task->project_id !== $project->id) {
            abort(404);
        }
    }
}
