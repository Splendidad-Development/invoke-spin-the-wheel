<?php

use App\Http\Controllers\GameController;
use App\Http\Controllers\PlayerController;
use App\Http\Controllers\SpinController;
use Illuminate\Support\Facades\Route;

Route::get('/', [GameController::class, 'welcome'])->name('game.welcome');
Route::post('/play', [GameController::class, 'start'])->name('game.start');

Route::get('/games/{game}', [GameController::class, 'show'])->name('game.show');
Route::post('/games/{game}/restart', [GameController::class, 'restart'])->name('game.restart');
Route::post('/games/{game}/spins', [SpinController::class, 'store'])->name('spins.store');
Route::put('/games/{game}/players', [PlayerController::class, 'update'])->name('players.update');
