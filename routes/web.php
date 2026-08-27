<?php

use App\Http\Controllers\Admin\LocationsController;
use App\Http\Controllers\Admin\MiraclesController;
use App\Http\Controllers\Admin\PersonsController;
use App\Http\Controllers\Admin\UsersController;
use App\Http\Controllers\Frontend\MiraclesController as FrontendMiraclesController;
use App\Http\Controllers\Frontend\SaintsController as FrontendSaintsController;
use App\Http\Controllers\LocaleController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', [FrontendMiraclesController::class, 'index'])->name('home');
Route::get('/miracles/json', [FrontendMiraclesController::class, 'getJsonList'])->name('miracles.json_list');
Route::get('/sventieji', [FrontendSaintsController::class, 'index'])->name('saints_index_lt');
Route::get('/saints', [FrontendSaintsController::class, 'index'])->name('saints_index_en');

Route::post('/locale', [LocaleController::class, 'setLocale'])->name('locale.set');
Route::get('/get-locale', [LocaleController::class, 'getLocale'])->name('locale.get');

Route::get('dashboard', function () {
    return Inertia::render('admin/Dashboard');
})->middleware(['auth'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/admin/locations', [LocationsController::class, 'index'])->name('admin.locations.index');
    Route::get('/admin/locations/json-list', [LocationsController::class, 'getJsonList'])->name('admin.locations.json_list');
    Route::get('/admin/locations/create', [LocationsController::class, 'create'])->name('admin.locations.create');
    Route::post('/admin/locations/{locationId?}', [LocationsController::class, 'save'])->name('admin.locations.save');
    Route::get('/admin/locations/{locationId}/edit', [LocationsController::class, 'edit'])->name('admin.locations.edit');
    Route::delete('/admin/locations/{locationId}', [LocationsController::class, 'delete'])->name('admin.locations.delete');

    Route::get('/admin/miracles', [MiraclesController::class, 'index'])->name('admin.miracles.index');
    Route::get('/admin/miracles/json-list', [MiraclesController::class, 'getJsonList'])->name('admin.miracles.json_list');
    Route::get('/admin/miracles/{miracleId}/edit', [MiraclesController::class, 'edit'])->name('admin.miracles.edit');
    Route::post('/admin/miracles/save/{miracleId?}', [MiraclesController::class, 'save'])->name('admin.miracles.save');
    Route::delete('/admin/miracles/{miracleId}', [MiraclesController::class, 'delete'])->name('admin.miracles.delete');

    Route::get('/admin/persons', [PersonsController::class, 'index'])->name('admin.persons.index');
    Route::get('/admin/persons/json-list', [PersonsController::class, 'getJsonList'])->name('admin.persons.json_list');
    Route::get('/admin/persons/{personId}/edit', [PersonsController::class, 'edit'])->name('admin.persons.edit');
    Route::post('/admin/persons/save/{personId?}', [PersonsController::class, 'save'])->name('admin.persons.save');
    Route::delete('/admin/persons/{personId}', [PersonsController::class, 'delete'])->name('admin.persons.delete');

    Route::get('/admin/users', [UsersController::class, 'index'])->name('admin.users.index');
    Route::get('/admin/users/json-list', [UsersController::class, 'jsonList'])->name('admin.users.json_list');
    Route::post('/admin/users/{userId?}', [UsersController::class, 'save'])->name('admin.users.save');
    Route::get('/admin/users/{userId}/edit', [UsersController::class, 'edit'])->name('admin.users.edit');
    Route::delete('/admin/users/{userId}', [UsersController::class, 'delete'])->name('admin.users.delete');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
