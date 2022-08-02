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
Route::post('/upload', [\App\Http\Controllers\UploadController::class,'upload']);

Route::resource('movie', \App\Http\Controllers\MovieController::class);
Route::resource('distributor', \App\Http\Controllers\DistributorController::class);
Route::resource('sala', \App\Http\Controllers\SalaController::class);
Route::resource('cui', \App\Http\Controllers\CuiController::class);
Route::resource('price', \App\Http\Controllers\PriceController::class);
Route::resource('programa', \App\Http\Controllers\ProgramaController::class);
Route::resource('cufd', \App\Http\Controllers\CufdController::class);
Route::resource('activity', \App\Http\Controllers\ActivityController::class);
Route::resource('sale', \App\Http\Controllers\SaleController::class);
Route::resource('rubro', \App\Http\Controllers\RubroController::class);
Route::post('movies', [\App\Http\Controllers\SaleController::class, 'movies']);
Route::post('hours', [\App\Http\Controllers\SaleController::class, 'hours']);
Route::post('mySeats', [\App\Http\Controllers\SaleController::class, 'mySeats']);
Route::post('upimagenrubro', [\App\Http\Controllers\RubroController::class, 'upimagenrubro']);
