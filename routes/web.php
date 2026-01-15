<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectMemberController;
use App\Http\Controllers\ProjectOwnershipController;
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
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    // Rutas de perfil de Breeze (gestión de cuenta)
    Route::get('/profile/account', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile/account', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile/account', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Rutas de perfil extendido (datos adicionales del usuario)
    Route::get('/profile', [\App\Http\Controllers\ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile/edit', [\App\Http\Controllers\ProfileController::class, 'edit'])->name('profile.edit.extended');
    Route::put('/profile', [\App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update.extended');

    // Rutas de equipos
    Route::resource('teams', \App\Http\Controllers\TeamController::class);

    // Rutas de proyectos
    Route::middleware('team.context')->group(function () {
        Route::resource('projects', \App\Http\Controllers\ProjectController::class);
        Route::patch('projects/{project}/owner', [\App\Http\Controllers\ProjectController::class, 'transferOwner'])
            ->name('projects.transfer-owner');

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
