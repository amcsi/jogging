<?php

use App\Http\Controllers\LoginController;
use Illuminate\Http\Request;

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

Route::middleware('auth:api')->get('/users', function (Request $request) {
    return $request->user();
});

Route::post('/users', 'UserController@store');

Route::post('/login', 'LoginController@login');
Route::post('/login/refresh', 'LoginController@refresh');
