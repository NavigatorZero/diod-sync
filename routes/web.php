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

Route::get('/getStocks', [\App\Http\Controllers\ApiController::class, 'getStocks'])->name('api.get-stocks')->middleware('auth');
Route::post('/commission', [\App\Http\Controllers\ApiController::class, 'commission'])->name('api.post-commission')->middleware('auth');
Route::get('/calcPrice', [\App\Http\Controllers\ApiController::class, 'calcPrice'])->name('api.get-price')->middleware('auth');
Route::get('/calc', [\App\Http\Controllers\ApiController::class, 'calc'])->middleware('auth');
Route::get('/', [\App\Http\Controllers\ApiController::class, 'index'])->middleware('auth');

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
