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
// pay method
Route::any('/callback-url', 'App\Http\Controllers\HomeController@processPay')->name('processPay');

// test methods
Route::get('/callback-url-test', 'App\Http\Controllers\HomeController@processPayRequest')->name('processPayRequest');
Route::get('/callback-url-test-additional', 'App\Http\Controllers\HomeController@processPayRequestAdditional')->name('processPayRequestAdditional');
