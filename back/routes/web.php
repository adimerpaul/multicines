<?php

use App\Http\Controllers\CineAuthController;
use App\Http\Controllers\CineCompraController;
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

    // Login de clientes web (Google via Firebase; los datos quedan en web_clientes)
    Route::middleware(['cine.token', 'throttle:20,1'])->group(function () {
        Route::post('/cine/login', [CineAuthController::class, 'login'])->name('cine.login');
        Route::post('/cine/logout', [CineAuthController::class, 'logout'])->name('cine.logout');
        Route::post('/cine/cliente/datos', [CineAuthController::class, 'guardarDatos'])->name('cine.cliente.datos');
    });

    // Compra de entradas (reserva de asientos, pago y registro en sales)
    Route::get('/mis-compras', [CineCompraController::class, 'misCompras'])->name('cine.compras');
    Route::middleware(['cine.token', 'throttle:20,1'])->group(function () {
        Route::post('/cine/compra/iniciar', [CineCompraController::class, 'iniciar'])->name('cine.compra.iniciar');
        Route::post('/cine/compra/cancelar', [CineCompraController::class, 'cancelar'])->name('cine.compra.cancelar');
        Route::post('/cine/compra/pagar-demo', [CineCompraController::class, 'pagarDemo'])->name('cine.compra.demo');
    });
});
