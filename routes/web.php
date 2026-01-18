<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\BacklogItemController;
use App\Http\Controllers\ProjectCalendarController;
use App\Http\Controllers\ProjectDashboardController;
use App\Http\Controllers\ProjectExportController;
use App\Http\Controllers\ProjectPdfExportController;
use App\Http\Controllers\ProjectMemberController;
use App\Http\Controllers\ProjectOwnershipController;
use App\Http\Controllers\SprintController;
use App\Http\Controllers\SprintPlanningController;
use App\Http\Controllers\SprintStateController;
use App\Http\Controllers\TaskAssigneeController;
use App\Http\Controllers\TaskChecklistItemController;
use App\Http\Controllers\TaskCommentController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TaskDependencyController;
use App\Http\Controllers\TaskAttachmentController;
use App\Http\Controllers\TaskTimeEntryController;
use App\Http\Controllers\TaskTimerController;
use App\Http\Controllers\TimeAggregationController;
use App\Http\Controllers\ScrumBoardController;
use App\Http\Controllers\TeamMemberController;
use App\Models\Project;
use App\Models\Team;
use App\Models\User;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard', [
        'usersCount' => User::count(),
        'teamsCount' => Team::count(),
        'projectsCount' => Project::count(),
        'recentUsers' => User::orderByDesc('created_at')->take(5)->get(),
        'recentTeams' => Team::with('owner')->orderByDesc('created_at')->take(5)->get(),
        'recentProjects' => Project::with('team')->orderByDesc('created_at')->take(5)->get(),
    ]);
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/notifications', [NotificationController::class, 'index'])
        ->name('notifications.index');
    Route::patch('/notifications/{notification}/read', [NotificationController::class, 'markRead'])
        ->name('notifications.read');
    Route::patch('/notifications/read-all', [NotificationController::class, 'markAllRead'])
        ->name('notifications.read-all');
    // Rutas de perfil de Breeze (gestión de cuenta)
    Route::get('/profile/account', [ProfileController::class, 'edit'])
        ->middleware('password.confirm')
        ->name('profile.edit');
    Route::patch('/profile/account', [ProfileController::class, 'update'])
        ->middleware('password.confirm')
        ->name('profile.update');
    Route::delete('/profile/account', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Rutas de perfil extendido (datos adicionales del usuario)
    Route::get('/profile', [\App\Http\Controllers\ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile/edit', [\App\Http\Controllers\ProfileController::class, 'edit'])
        ->middleware('password.confirm')
        ->name('profile.edit.extended');
    Route::put('/profile', [\App\Http\Controllers\ProfileController::class, 'update'])
        ->middleware('password.confirm')
        ->name('profile.update.extended');

    // Admin (superadmin)
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/', [AdminDashboardController::class, 'index'])->name('index');
        Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
        Route::get('/users/create', [AdminUserController::class, 'create'])->name('users.create');
        Route::get('/users/{user}', [AdminUserController::class, 'show'])->name('users.show');
        Route::get('/users/{user}/edit', [AdminUserController::class, 'edit'])->name('users.edit');
        Route::post('/users', [AdminUserController::class, 'store'])->name('users.store');
        Route::post('/users/bulk-deactivate', [AdminUserController::class, 'bulkDeactivate'])->name('users.bulk-deactivate');
        Route::post('/users/export-pdf', [AdminUserController::class, 'exportPdf'])->name('users.export-pdf');
        Route::post('/users/{user}/deactivate', [AdminUserController::class, 'deactivate'])->name('users.deactivate');
        Route::patch('/users/{user}', [AdminUserController::class, 'update'])->name('users.update');
    });

    // Rutas de equipos
    Route::resource('teams', \App\Http\Controllers\TeamController::class);
    Route::get('teams/{team}/members', [TeamMemberController::class, 'index'])
        ->name('teams.members.index');
    Route::post('teams/{team}/members', [TeamMemberController::class, 'store'])
        ->name('teams.members.store');
    Route::patch('teams/{team}/members/{user}', [TeamMemberController::class, 'update'])
        ->name('teams.members.update');
    Route::delete('teams/{team}/members/{user}', [TeamMemberController::class, 'destroy'])
        ->name('teams.members.destroy');

    // Rutas de proyectos
    Route::middleware('team.context')->group(function () {
        Route::resource('projects', \App\Http\Controllers\ProjectController::class);
        Route::patch('projects/{project}/owner', [\App\Http\Controllers\ProjectController::class, 'transferOwner'])
            ->name('projects.transfer-owner');

        Route::scopeBindings()->group(function () {
            Route::get('projects/{project}/scrum-board', [ScrumBoardController::class, 'index'])
                ->name('projects.scrum-board.index');
            Route::patch('projects/{project}/tasks/{task}/scrum-board/move', [ScrumBoardController::class, 'move'])
                ->name('tasks.scrum-board.move');
            Route::get('projects/{project}/calendar', [ProjectCalendarController::class, 'index'])
                ->name('projects.calendar.index');
            Route::get('projects/{project}/dashboard', [ProjectDashboardController::class, 'index'])
                ->name('projects.dashboard.index');
            Route::get('projects/{project}/exports/tasks.csv', [ProjectExportController::class, 'tasks'])
                ->name('projects.exports.tasks');
            Route::get('projects/{project}/exports/time-entries.csv', [ProjectExportController::class, 'timeEntries'])
                ->name('projects.exports.time-entries');
            Route::get('projects/{project}/exports/workload.csv', [ProjectExportController::class, 'workload'])
                ->name('projects.exports.workload');
            Route::get('projects/{project}/exports/sprint-summary.pdf', [ProjectPdfExportController::class, 'sprintSummary'])
                ->name('projects.exports.sprint-summary');
            Route::get('projects/{project}/sprints', [SprintController::class, 'index'])
                ->name('sprints.index');
            Route::get('projects/{project}/sprints/create', [SprintController::class, 'create'])
                ->name('sprints.create');
            Route::post('projects/{project}/sprints', [SprintController::class, 'store'])
                ->name('sprints.store');
            Route::get('projects/{project}/sprints/{sprint}', [SprintController::class, 'show'])
                ->name('sprints.show');
            Route::get('projects/{project}/sprints/{sprint}/plan', [SprintPlanningController::class, 'show'])
                ->name('sprints.plan');
            Route::post('projects/{project}/sprints/{sprint}/plan/assign', [SprintPlanningController::class, 'assign'])
                ->name('sprints.plan.assign');
            Route::post('projects/{project}/sprints/{sprint}/plan/unassign', [SprintPlanningController::class, 'unassign'])
                ->name('sprints.plan.unassign');
            Route::post('projects/{project}/sprints/{sprint}/plan/reorder', [SprintPlanningController::class, 'reorder'])
                ->name('sprints.plan.reorder');
            Route::post('projects/{project}/sprints/{sprint}/start', [SprintStateController::class, 'start'])
                ->name('sprints.start');
            Route::post('projects/{project}/sprints/{sprint}/close', [SprintStateController::class, 'close'])
                ->name('sprints.close');

            Route::get('projects/{project}/backlog', [BacklogItemController::class, 'index'])
                ->name('backlog.index');
            Route::get('projects/{project}/backlog/create', [BacklogItemController::class, 'create'])
                ->name('backlog.create');
            Route::post('projects/{project}/backlog', [BacklogItemController::class, 'store'])
                ->name('backlog.store');
            Route::get('projects/{project}/backlog/{backlogItem}/edit', [BacklogItemController::class, 'edit'])
                ->name('backlog.edit');
            Route::put('projects/{project}/backlog/{backlogItem}', [BacklogItemController::class, 'update'])
                ->name('backlog.update');
            Route::delete('projects/{project}/backlog/{backlogItem}', [BacklogItemController::class, 'destroy'])
                ->name('backlog.destroy');
            Route::post('projects/{project}/backlog/reorder', [BacklogItemController::class, 'reorder'])
                ->name('backlog.reorder');

            Route::get('projects/{project}/tasks', [TaskController::class, 'index'])
                ->name('tasks.index');
            Route::post('projects/{project}/tasks', [TaskController::class, 'store'])
                ->name('tasks.store');
            Route::get('projects/{project}/tasks/{task}', [TaskController::class, 'show'])
                ->name('tasks.show');
            Route::patch('projects/{project}/tasks/{task}', [TaskController::class, 'update'])
                ->name('tasks.update');
            Route::delete('projects/{project}/tasks/{task}', [TaskController::class, 'destroy'])
                ->name('tasks.destroy');

            Route::get('projects/{project}/tasks/{task}/dependencies', [TaskDependencyController::class, 'index'])
                ->name('tasks.dependencies.index');
            Route::post('projects/{project}/tasks/{task}/dependencies', [TaskDependencyController::class, 'store'])
                ->name('tasks.dependencies.store');
            Route::delete('projects/{project}/tasks/{task}/dependencies/{dependsOnTask}', [TaskDependencyController::class, 'destroy'])
                ->name('tasks.dependencies.destroy');

            Route::post('projects/{project}/tasks/{task}/checklist', [TaskChecklistItemController::class, 'store'])
                ->name('tasks.checklist.store');
            Route::patch('projects/{project}/tasks/{task}/checklist/{item}', [TaskChecklistItemController::class, 'update'])
                ->name('tasks.checklist.update');
            Route::delete('projects/{project}/tasks/{task}/checklist/{item}', [TaskChecklistItemController::class, 'destroy'])
                ->name('tasks.checklist.destroy');
            Route::post('projects/{project}/tasks/{task}/checklist/reorder', [TaskChecklistItemController::class, 'reorder'])
                ->name('tasks.checklist.reorder');

            Route::get('projects/{project}/tasks/{task}/timer', [TaskTimerController::class, 'show'])
                ->name('tasks.timer.show');
            Route::post('projects/{project}/tasks/{task}/timer/start', [TaskTimerController::class, 'start'])
                ->name('tasks.timer.start');
            Route::post('projects/{project}/tasks/{task}/timer/stop', [TaskTimerController::class, 'stop'])
                ->name('tasks.timer.stop');

            Route::get('projects/{project}/tasks/{task}/comments', [TaskCommentController::class, 'index'])
                ->name('tasks.comments.index');
            Route::post('projects/{project}/tasks/{task}/comments', [TaskCommentController::class, 'store'])
                ->name('tasks.comments.store');
            Route::patch('projects/{project}/tasks/{task}/comments/{comment}', [TaskCommentController::class, 'update'])
                ->name('tasks.comments.update');
            Route::delete('projects/{project}/tasks/{task}/comments/{comment}', [TaskCommentController::class, 'destroy'])
                ->name('tasks.comments.destroy');
            Route::get('projects/{project}/tasks/{task}/comments/{comment}/revisions', [TaskCommentController::class, 'revisions'])
                ->name('tasks.comments.revisions');

            Route::get('projects/{project}/tasks/{task}/assignees', [TaskAssigneeController::class, 'index'])
                ->name('tasks.assignees.index');
            Route::post('projects/{project}/tasks/{task}/assignees', [TaskAssigneeController::class, 'store'])
                ->name('tasks.assignees.store');
            Route::delete('projects/{project}/tasks/{task}/assignees/{user}', [TaskAssigneeController::class, 'destroy'])
                ->name('tasks.assignees.destroy');

            Route::get('projects/{project}/tasks/{task}/attachments', [TaskAttachmentController::class, 'index'])
                ->name('tasks.attachments.index');
            Route::post('projects/{project}/tasks/{task}/attachments', [TaskAttachmentController::class, 'store'])
                ->name('tasks.attachments.store');
            Route::get('projects/{project}/tasks/{task}/attachments/{attachment}/download', [TaskAttachmentController::class, 'download'])
                ->name('tasks.attachments.download');
            Route::delete('projects/{project}/tasks/{task}/attachments/{attachment}', [TaskAttachmentController::class, 'destroy'])
                ->name('tasks.attachments.destroy');

            Route::get('projects/{project}/tasks/{task}/time-entries', [TaskTimeEntryController::class, 'index'])
                ->name('tasks.time-entries.index');
            Route::post('projects/{project}/tasks/{task}/time-entries', [TaskTimeEntryController::class, 'store'])
                ->name('tasks.time-entries.store');
            Route::patch('projects/{project}/tasks/{task}/time-entries/{timeEntry}', [TaskTimeEntryController::class, 'update'])
                ->name('tasks.time-entries.update');
            Route::delete('projects/{project}/tasks/{task}/time-entries/{timeEntry}', [TaskTimeEntryController::class, 'destroy'])
                ->name('tasks.time-entries.destroy');

            Route::get('projects/{project}/tasks/{task}/time-summary', [TimeAggregationController::class, 'taskSummary'])
                ->name('tasks.time-summary.show');
            Route::get('projects/{project}/sprints/{sprint}/time-summary', [TimeAggregationController::class, 'sprintSummary'])
                ->name('sprints.time-summary.show');
        });

        Route::get('projects/{project}/members', [ProjectMemberController::class, 'index'])
            ->name('projects.members.index');
        Route::post('projects/{project}/members', [ProjectMemberController::class, 'store'])
            ->name('projects.members.store');
        Route::patch('projects/{project}/members/{user}', [ProjectMemberController::class, 'update'])
            ->name('projects.members.update');
        Route::delete('projects/{project}/members/{user}', [ProjectMemberController::class, 'destroy'])
            ->name('projects.members.destroy');

        Route::post('projects/{project}/ownership', [ProjectOwnershipController::class, 'store'])
            ->name('projects.ownership.transfer');
    });
});

require __DIR__.'/auth.php';
