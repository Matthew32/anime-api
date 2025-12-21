<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Session\Middleware\StartSession;
use App\Http\Controllers\Api\EpisodeController;
use App\Http\Controllers\Api\ProgressController;

Route::middleware([EncryptCookies::class, AddQueuedCookiesToResponse::class, StartSession::class])->group(function () {
    Route::get('/episodes', [EpisodeController::class, 'index']);
    Route::get('/episodes/{id}', [EpisodeController::class, 'show']);

    Route::get('/progress', [ProgressController::class, 'get']);
    Route::post('/progress', [ProgressController::class, 'set']);
});