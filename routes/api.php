<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\ChargingSessionController;
use App\Http\Controllers\Api\V1\StationConnectorController;
use App\Http\Controllers\Api\V1\StationController;
use App\Http\Controllers\Api\V1\StatsController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    // Public Auth endpoints (with rate limiting on login)
    Route::post('/auth/login', [AuthController::class, 'login'])
        ->middleware('throttle:5,1')
        ->name('api.v1.auth.login');

    // Protected API endpoints
    Route::middleware('auth:sanctum')->group(function (): void {
        // Auth management
        Route::post('/auth/logout', [AuthController::class, 'logout'])->name('api.v1.auth.logout');
        Route::get('/auth/me', [AuthController::class, 'me'])->name('api.v1.auth.me');
        Route::get('/me', [AuthController::class, 'me'])->name('api.v1.me');

        // Charging Stations
        Route::apiResource('stations', StationController::class)->names('api.v1.stations');

        // Nested Connectors under Stations: stations/{station}/connectors
        Route::apiResource('stations.connectors', StationConnectorController::class)
            ->names('api.v1.stations.connectors');

        // Charging Sessions
        Route::apiResource('charging-sessions', ChargingSessionController::class)
            ->names('api.v1.charging-sessions');

        // Statistics Summary
        Route::get('/stats/summary', [StatsController::class, 'summary'])
            ->name('api.v1.stats.summary');
    });
});
