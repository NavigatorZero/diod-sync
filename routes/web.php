<?php

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

//Route::get('/', function () {
//    return view('welcome');
//});

Route::get('/getStocks', [\App\Http\Controllers\ApiController::class, 'getStocks'])->name('api.get-stocks');
Route::get('/calc', [\App\Http\Controllers\ApiController::class, 'calc']);
Route::get('/', [\App\Http\Controllers\ApiController::class, 'index']);
