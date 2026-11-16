<?php

declare(strict_types=1);

use App\Livewire\Auth\Login;
use App\Livewire\ChargingSessions\SessionIndex;
use App\Livewire\Dashboard;
use App\Livewire\EvModels\EvModelIndex;
use App\Livewire\Stations\StationIndex;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Guest Authentication
Route::get('/login', Login::class)->name('login');

// Authenticated Admin Routes
Route::middleware('auth')->group(function (): void {
    Route::get('/', Dashboard::class)->name('dashboard');
    Route::get('/stations', StationIndex::class)->name('stations.index');
    Route::get('/sessions', SessionIndex::class)->name('sessions.index');
    Route::get('/ev-models', EvModelIndex::class)->name('ev-models.index');

    Route::get('/docs/api', function () {
        $path = base_path('docs/API.md');
        if (! file_exists($path)) {
            abort(404);
        }

        return response(file_get_contents($path), 200, [
            'Content-Type' => 'text/markdown; charset=UTF-8',
        ]);
    })->name('docs.api');

    Route::post('/logout', function (Request $request) {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    })->name('logout');
});
