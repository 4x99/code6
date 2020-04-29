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
Route::resource('/rsc/index', 'IndexController');

Route::get('/codeLeak', 'CodeLeakController@view');
Route::resource('/rsc/codeLeak', 'CodeLeakController');

Route::get('/configJob', 'ConfigJobController@view');
Route::resource('/rsc/configJob', 'ConfigJobController');

Route::get('/configToken', 'ConfigTokenController@view');
Route::resource('/rsc/configToken', 'ConfigTokenController');

Route::get('/configWhitelist', 'ConfigWhitelistController@view');
Route::resource('/rsc/configWhitelist', 'ConfigWhitelistController');
