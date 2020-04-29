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

Route::get('/', 'IndexController@index');
Route::get('/codeLeak', 'CodeLeakController@index');
Route::get('/configJob', 'ConfigJobController@index');
Route::get('/configToken', 'ConfigTokenController@index');
Route::get('/configWhitelist', 'ConfigWhitelistController@index');
