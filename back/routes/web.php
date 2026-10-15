<?php

use App\Http\Controllers\CineController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Web publica de la cartelera (Blade)
Route::middleware('cine.headers')->group(function () {
    Route::get('/', [CineController::class, 'home'])->name('cine.home');
    Route::get('/comprar/{id}', [CineController::class, 'comprar'])->whereNumber('id')->name('cine.comprar');

    Route::post('/cine/asientos', [CineController::class, 'asientos'])
        ->middleware(['cine.token', 'throttle:60,1'])
        ->name('cine.asientos');
});
