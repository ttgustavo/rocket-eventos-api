<?php

use App\Presenter\Http\Controllers\Auth\AuthLoginController;
use App\Presenter\Http\Controllers\HomeController;
use App\Presenter\Http\Controllers\Auth\AuthRegisterController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class);

// Authentication
Route::post('/register', AuthRegisterController::class);
Route::post('/login', AuthLoginController::class);