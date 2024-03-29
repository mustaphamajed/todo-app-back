<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\StatisticsController;
use App\Http\Controllers\TaskController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {

    // User routes
    Route::get('/me', [AuthController::class, 'getAuthenticatedUser']);
    Route::get('/users', [AuthController::class, 'getAllUsers']);

    // Task routes
    Route::get('/tasks', [TaskController::class, 'index']);
    Route::get('/tasks/{id}', [TaskController::class, 'show']);
    Route::put('/tasks/{id}', [TaskController::class, 'update']);
    Route::post('/tasks', [TaskController::class, 'store']);
    Route::put('/tasks/markAsCompleted/{id}', [TaskController::class, 'markAsCompleted']);
    Route::put('/tasks/assign/{id}', [TaskController::class, 'assignTaskToUser']);
    // Route::delete('/tasks/{id}', [TaskController::class, 'destroy']);

    Route::get('/statistics', [StatisticsController::class, 'getStats']);
});
