<?php

use App\Http\Controllers\LocationsController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('admin/Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/admin/locations', [LocationsController::class, 'index'])->name('admin.locations.index');
});

Route::post('/locale', function () {
    $locale = request('locale');

    if (array_key_exists($locale, config('app.website_locales'))) {
        session(['locale' => $locale]);
    }

    return response()->noContent();
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
