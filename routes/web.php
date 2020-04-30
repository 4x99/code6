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

Route::get('/', 'IndexController@view');
Route::resource('/api/index', 'IndexController');

Route::get('/codeLeak', 'CodeLeakController@view');
Route::resource('/api/codeLeak', 'CodeLeakController');

Route::get('/configJob', 'ConfigJobController@view');
Route::resource('/api/configJob', 'ConfigJobController');

Route::get('/configToken', 'ConfigTokenController@view');
Route::resource('/api/configToken', 'ConfigTokenController');

Route::get('/configWhitelist', 'ConfigWhitelistController@view');
Route::resource('/api/configWhitelist', 'ConfigWhitelistController');
