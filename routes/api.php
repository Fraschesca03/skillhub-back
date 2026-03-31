<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

// Test API
Route::get('/test', function () {
    return response()->json([
        'message' => 'API SkillHub OK'
    ]);
});

// Auth simple
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/profile', [AuthController::class, 'profile']);
Route::post('/logout', [AuthController::class, 'logout']);