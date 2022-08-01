<?php

use App\Http\Controllers\ApiController;
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

Route::get('/getStocks', [ApiController::class, 'getStocks'])->name('api.get-stocks')->middleware('auth');
Route::post('/commission', [ApiController::class, 'commission'])->name('api.post-commission')->middleware('auth');
Route::post('/stocks', [ApiController::class, 'stocks'])->name('api.post-stocks')->middleware('auth');
Route::post('/changeSyncSettings', [ApiController::class, 'changeSyncSettings'])->name('api.post-sync-settings')->middleware('auth');
Route::get('/calcPrice', [ApiController::class, 'calcPrice'])->name('api.get-price')->middleware('auth');
Route::get('/calc', [ApiController::class, 'calc'])->middleware('auth');
Route::get('/', [ApiController::class, 'index'])->middleware('auth');

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
