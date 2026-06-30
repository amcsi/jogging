<?php

use App\Http\Controllers\JoggingTime\JoggingTimeByWeekController;
use App\Http\Controllers\JoggingTimeController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->group(function () {
    Route::put('/jogging-times/{joggingTime}', [JoggingTimeController::class, 'update']);
    Route::delete('/jogging-times/{joggingTime}', [JoggingTimeController::class, 'destroy']);

    Route::get('/users', [UserController::class, 'index']);
    Route::get('/users/me', [UserController::class, 'me']);
    Route::prefix('/users/{user}')->group(function () {
        Route::get('/', [UserController::class, 'show']);
        Route::prefix('jogging-times')->group(function () {
            Route::get('/', [JoggingTimeController::class, 'index']);
            Route::get('/by-week', [JoggingTimeByWeekController::class, 'index']);
            Route::post('/', [JoggingTimeController::class, 'store']);
        });
    });
    Route::put('/users/{user}', [UserController::class, 'update']);
    Route::delete('/users/{user}', [UserController::class, 'destroy']);
});

Route::post('/users', [UserController::class, 'store']);

Route::post('/login', [LoginController::class, 'login']);
Route::post('/login/refresh', [LoginController::class, 'refresh']);
