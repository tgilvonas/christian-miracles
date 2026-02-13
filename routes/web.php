<?php

use App\Http\Controllers\Admin\LocationsController;
use App\Http\Controllers\LocaleController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::post('/locale', [LocaleController::class, 'setLocale'])->name('locale.set');
Route::get('/get-locale', [LocaleController::class, 'getLocale'])->name('locale.get');

Route::get('dashboard', function () {
    return Inertia::render('admin/Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/admin/locations', [LocationsController::class, 'index'])->name('admin.locations.index');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
