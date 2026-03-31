<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\FormationController;
use Illuminate\Support\Facades\Route;

// Test API
Route::get('/test', function () {
    return response()->json([
        'message' => 'API SkillHub OK'
    ]);
});

// Auth
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/profile', [AuthController::class, 'profile']);
Route::post('/logout', [AuthController::class, 'logout']);

// Formations
Route::get('/formations', [FormationController::class, 'index']);
Route::get('/formations/{id}', [FormationController::class, 'show']);
Route::post('/formations', [FormationController::class, 'store']);
Route::put('/formations/{id}', [FormationController::class, 'update']);
Route::delete('/formations/{id}', [FormationController::class, 'destroy']);