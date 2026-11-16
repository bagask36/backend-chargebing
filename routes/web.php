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

// Interactive Swagger UI & OpenAPI Specification
Route::get('/docs/api', function () {
    return view('docs.swagger');
})->name('docs.api');

Route::get('/docs/api/openapi.json', function () {
    $path = public_path('docs/openapi.json');
    if (! file_exists($path)) {
        abort(404);
    }

    return response()->file($path, [
        'Content-Type' => 'application/json',
    ]);
})->name('docs.api.json');

// Authenticated Admin Routes
Route::middleware('auth')->group(function (): void {
    Route::get('/', Dashboard::class)->name('dashboard');
    Route::get('/stations', StationIndex::class)->name('stations.index');
    Route::get('/sessions', SessionIndex::class)->name('sessions.index');
    Route::get('/ev-models', EvModelIndex::class)->name('ev-models.index');

    Route::post('/logout', function (Request $request) {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    })->name('logout');
});
