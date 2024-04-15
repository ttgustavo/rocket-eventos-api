<?php

use App\Presenter\Http\Controllers\Api\Auth\AuthLoginController;
use App\Presenter\Http\Controllers\Api\Auth\AuthRegisterController;
use App\Presenter\Http\Controllers\Api\Events\CreateEventController;
use App\Presenter\Http\Controllers\Api\HomeController;
use Illuminate\Support\Facades\Route;

// Authentication routes
Route::post('/register', AuthRegisterController::class);
Route::post('/login', AuthLoginController::class);

// User routes
Route::get('/', HomeController::class);

// Admin routes
Route::get('/admin')->middleware('auth:sanctum');
Route::post('/admin/events', CreateEventController::class);