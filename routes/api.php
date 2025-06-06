<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\BibliotecaController;
use Laravel\Sanctum\Http\Controllers\CsrfCookieController;
use App\Http\Controllers\AuditLogController;

Route::get('/', function () {
    return "welcome to the API";
});

// rutas para la autenticación
Route::post('/login', [AuthController::class, 'login']);
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
});

Route::get('/sanctum/csrf-cookie', [CsrfCookieController::class, 'show']);

Route::middleware('auth:sanctum')->group(function () {

    Route::apiResource('usuarios', UserController::class);
    Route::apiResource('roles', RoleController::class);

});

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('bibliotecas', BibliotecaController::class);
});

Route::get('/dashboard', function () {
    return response()->json([
        'message' => 'Welcome to the API',
    ]);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/audit-logs', [AuditLogController::class, 'index']);
    Route::get('/audit-logs/export', [AuditLogController::class, 'export']);
    Route::get('/audit-logs/user-stats', [AuditLogController::class, 'userStats']);
    Route::get('/audit-logs/daily-stats', [AuditLogController::class, 'dailyStats']);
    
});

