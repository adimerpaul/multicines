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
Route::resource('producto', \App\Http\Controllers\ProductoController::class);
Route::resource('momentaneo', \App\Http\Controllers\MomentaneoController::class);
Route::resource('document', \App\Http\Controllers\DocumentController::class);
Route::resource('client', \App\Http\Controllers\ClientController::class);
Route::resource('eventoSignificativo', \App\Http\Controllers\EventoSignificativoController::class);
Route::resource('rental', \App\Http\Controllers\RentalController::class);
Route::resource('prevalorada', \App\Http\Controllers\PrevaloradaController::class);
Route::post('rentalConsulta', [\App\Http\Controllers\RentalController::class,'rentalConsulta']);
Route::post('prevaloradaConsulta', [\App\Http\Controllers\PrevaloradaController::class,'prevaloradaConsulta']);
Route::post('listleyenda', [\App\Http\Controllers\ActivityController::class,'listleyenda']);
Route::get('motivoanular', [\App\Http\Controllers\ActivityController::class,'motivoanular']);

Route::post('recepcionPaqueteFactura', [\App\Http\Controllers\EventoSignificativoController::class,'recepcionPaqueteFactura']);
Route::post('cantidadFacturas', [\App\Http\Controllers\EventoSignificativoController::class,'cantidadFacturas']);
Route::post('validarPaquete', [\App\Http\Controllers\EventoSignificativoController::class,'validarPaquete']);

Route::resource('salecandy', \App\Http\Controllers\SaleCandyController::class);
Route::resource('listaVentaBoleteria', \App\Http\Controllers\ListaVentaBoleteriaController::class);
Route::resource('listaVentaCandy', \App\Http\Controllers\ListaVentaCandyController::class);
Route::resource('event', \App\Http\Controllers\EventController::class);

Route::post('searchClient', [\App\Http\Controllers\ClientController::class,'searchClient']);
Route::get('datocine', [\App\Http\Controllers\SaleController::class,'datocine']);
Route::post('totalventa', [\App\Http\Controllers\SaleController::class,'totalventa']);
Route::post('anularSale', [\App\Http\Controllers\SaleController::class,'anularSale']);

Route::post('momentaneoDelete', [\App\Http\Controllers\MomentaneoController::class,'momentaneoDelete']);
Route::post('momentaneoDeleteUser', [\App\Http\Controllers\MomentaneoController::class,'momentaneoDeleteUser']);
Route::post('momentaneoDeleteall', [\App\Http\Controllers\MomentaneoController::class,'momentaneoDeleteall']);
Route::post('movies', [\App\Http\Controllers\SaleController::class, 'movies']);
Route::post('movietotal', [\App\Http\Controllers\SaleController::class, 'movietotal']);
Route::post('hours', [\App\Http\Controllers\SaleController::class, 'hours']);
Route::post('mySeats', [\App\Http\Controllers\SaleController::class, 'mySeats']);
Route::post('eventSearch', [\App\Http\Controllers\SaleController::class, 'eventSearch']);
Route::post('disponibleSeats', [\App\Http\Controllers\SaleController::class, 'disponibleSeats']);
Route::post('upimagenrubro', [\App\Http\Controllers\RubroController::class, 'upimagenrubro']);
Route::post('upimagenmovie', [\App\Http\Controllers\MovieController::class, 'upimagenmovie']);
Route::post('upimagenproducto', [\App\Http\Controllers\ProductoController::class, 'upimagenproducto']);
Route::post('listadoprod', [\App\Http\Controllers\RubroController::class, 'listadoprod']);
