<?php

use App\Http\Controllers\ProfileController;
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
    Route::resource('projects', \App\Http\Controllers\ProjectController::class);
});

require __DIR__.'/auth.php';
