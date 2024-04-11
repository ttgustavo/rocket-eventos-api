<?php

use Illuminate\Support\Facades\Route;

Route::get('/admin')->middleware('auth:sanctum');