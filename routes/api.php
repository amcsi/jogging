<?php

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
    Route::put('/jogging-times/{joggingTime}', 'JoggingTimeController@update');
    Route::delete('/jogging-times/{joggingTime}', 'JoggingTimeController@destroy');

    Route::get('/users', 'UserController@index');
    Route::get('/users/me', 'UserController@me');
    Route::prefix('/users/{user}')->group(function () {
        Route::get('/', 'UserController@show');
        Route::prefix('jogging-times')->group(function () {
            Route::get('/', 'JoggingTimeController@index');
            Route::get('/by-week', 'JoggingTime\JoggingTimeByWeekController@index');
            Route::post('/', 'JoggingTimeController@store');
        });
    });
    Route::put('/users/{user}', 'UserController@update');
    Route::delete('/users/{user}', 'UserController@destroy');
});

Route::post('/users', 'UserController@store');

Route::post('/login', 'LoginController@login');
Route::post('/login/refresh', 'LoginController@refresh');
