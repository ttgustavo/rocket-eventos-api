<?php

use App\Domain\Model\UserPermissions;
use App\Presenter\Http\Controllers\Api\Auth\AuthLoginController;
use App\Presenter\Http\Controllers\Api\Auth\AuthRegisterController;
use App\Presenter\Http\Controllers\Api\Events\CreateEventController;
use App\Presenter\Http\Controllers\Api\Events\DeleteEventController;
use App\Presenter\Http\Controllers\Api\Events\GetEventController;
use App\Presenter\Http\Controllers\Api\Events\UpdateEventController;
use App\Presenter\Http\Controllers\Api\HomeController;
use Illuminate\Support\Facades\Route;

// Authentication routes
Route::post('/register', AuthRegisterController::class);
Route::post('/login', AuthLoginController::class);

// User routes
Route::get('/', HomeController::class);

// Admin routes
$adminOrSuperAbility = UserPermissions::getStringPermissionForAdminAndSuper();

// Events route
Route::middleware(['auth:sanctum', "ability:$adminOrSuperAbility"])->prefix('/admin')->group(function() {
    Route::post('/events', CreateEventController::class);
    Route::patch('/events/{id}', UpdateEventController::class);
    Route::delete('/events/{id}', DeleteEventController::class);
    Route::get('/events/{slug}', GetEventController::class);
});

Route::get('/admin')->middleware('auth:sanctum');
