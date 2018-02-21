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
    Route::get('/jogging-times', 'JoggingTimeController@index');
    Route::put('/jogging-times/{joggingTime}', 'JoggingTimeController@update');
    Route::post('/jogging-times', 'JoggingTimeController@store');
    Route::delete('/jogging-times/{joggingTime}', 'JoggingTimeController@destroy');
});

Route::post('/users', 'UserController@store');

Route::post('/login', 'LoginController@login');
Route::post('/login/refresh', 'LoginController@refresh');
