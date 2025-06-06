<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\EmpleadoController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\BibliotecaController;
use Laravel\Sanctum\Http\Controllers\CsrfCookieController;

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

    Route::apiResource('empleados', EmpleadoController::class);
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
