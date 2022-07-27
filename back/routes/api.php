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

//Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//    return $request->user();
//});
Route::resource('movie', \App\Http\Controllers\MovieController::class);
Route::resource('distributor', \App\Http\Controllers\DistributorController::class);
Route::resource('sala', \App\Http\Controllers\SalaController::class);
Route::resource('cui', \App\Http\Controllers\CuiController::class);
Route::resource('activity', \App\Http\Controllers\ActivityController::class);
