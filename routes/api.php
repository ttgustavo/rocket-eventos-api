<?php

use App\Domain\Model\UserPermissions;
use App\Presenter\Http\Controllers\Api\Admin\Events\CreateEventController;
use App\Presenter\Http\Controllers\Api\Admin\Events\DeleteEventController;
use App\Presenter\Http\Controllers\Api\Admin\Events\GetEventController;
use App\Presenter\Http\Controllers\Api\Admin\Events\UpdateEventController;
use App\Presenter\Http\Controllers\Api\Client\Attendee\RegisterAttendeeController;
use App\Presenter\Http\Controllers\Api\Client\Attendee\RemoveAttendeeController;
use App\Presenter\Http\Controllers\Api\Client\Auth\AuthLoginController;
use App\Presenter\Http\Controllers\Api\Client\Auth\AuthRegisterController;
use App\Presenter\Http\Controllers\Api\Client\User\UpdateUserController;
use App\Presenter\Http\Controllers\Api\HomeController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class);

// ---- Authentication routes
Route::post('/register', AuthRegisterController::class);
Route::post('/login', AuthLoginController::class);

// ----- Authenticated routes
Route::middleware('auth:sanctum')->group(function() {
    // User routes
    Route::prefix('/users')->group(function() {
        Route::patch('/', UpdateUserController::class);
    });

    // Attendee routes
    Route::post('/events/{eventId}/attendees', RegisterAttendeeController::class);
    Route::delete('/events/{eventId}/attendees', RemoveAttendeeController::class);
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
