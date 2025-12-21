<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\EpisodePageController;
use App\Http\Controllers\Web\AuthController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/episodes', [EpisodePageController::class, 'index'])->name('episodes.index');
Route::get('/episodes/{id}', [EpisodePageController::class, 'show'])->name('episodes.show');

Route::get('/login', [AuthController::class, 'showLogin'])->name('login.show');
Route::post('/login', [AuthController::class, 'doLogin'])->name('login.do');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
