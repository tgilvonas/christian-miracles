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
    Route::get('/admin/locations/json-list', [LocationsController::class, 'getJsonList'])->name('admin.locations.json_list');
    Route::get('/admin/locations/create', [LocationsController::class, 'create'])->name('admin.locations.create');
    Route::post('/admin/locations/{locationId?}', [LocationsController::class, 'save'])->name('admin.locations.save');
    Route::get('/admin/locations/{locationId}/edit', [LocationsController::class, 'edit'])->name('admin.locations.edit');
    Route::delete('/admin/locations/{locationId}', [LocationsController::class, 'delete'])->name('admin.locations.delete');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
