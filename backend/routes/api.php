<?php

use Illuminate\Http\Request;
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

Route::post('/login', 'UserController@login');
Route::post('/register', 'UserController@register');

Route::group([ 'middleware' => 'apiJwt'], function () {
    Route::get('/user', 'UserController@getUser');
    Route::post('/logout', 'UserController@logout');
    Route::post('/refresh', 'UserController@refresh');

    Route::resource('orders', 'TravelOrderController', ['only' => ['index', 'show', 'store', 'update', 'destroy']]);
    Route::put('orders/{order}/approve', 'TravelOrderController@updateStatus');
    Route::put('orders/{order}/cancel', 'TravelOrderController@updateStatus');
});

