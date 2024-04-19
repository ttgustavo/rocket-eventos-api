<?php

use App\Domain\Model\UserPermissions;
use App\Presenter\Http\Controllers\Api\Admin\Events\CreateEventController;
use App\Presenter\Http\Controllers\Api\Admin\Events\DeleteEventController;
use App\Presenter\Http\Controllers\Api\Admin\Events\GetEventController;
use App\Presenter\Http\Controllers\Api\Admin\Events\UpdateEventController;
use App\Presenter\Http\Controllers\Api\Client\Auth\AuthLoginController;
use App\Presenter\Http\Controllers\Api\Client\Auth\AuthRegisterController;
use App\Presenter\Http\Controllers\Api\Client\User\UpdateUserController;
use App\Presenter\Http\Controllers\Api\HomeController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class);

// ---- Authentication routes
Route::post('/register', AuthRegisterController::class);
Route::post('/login', AuthLoginController::class);

// ----- User routes
Route::middleware('auth:sanctum')->prefix('/users')->group(function() {
    Route::patch('/', UpdateUserController::class);
});

// ---- Admin routes
$adminOrSuperAbility = UserPermissions::getStringPermissionForAdminAndSuper();

Route::middleware(['auth:sanctum', "ability:$adminOrSuperAbility"])->prefix('/admin')->group(function() {
    // Events
    Route::post('/events', CreateEventController::class);
    Route::patch('/events/{id}', UpdateEventController::class);
    Route::delete('/events/{id}', DeleteEventController::class);
    Route::get('/events/{slug}', GetEventController::class);
});
