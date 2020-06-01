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

Route::get('/login', 'LoginController@view');
Route::post('/api/login', 'LoginController@login');

Route::group(['middleware' => ['auth']], function () {
    Route::get('/', 'IndexController@view');
    Route::resource('/api/index', 'IndexController');

    Route::put('/api/user', 'UserController@update');

    Route::post('/api/logout', 'LoginController@logout');

    Route::get('/home', 'HomeController@view');
    Route::get('/api/home/metric', 'HomeController@metric');
    Route::get('/api/home/load', 'HomeController@load');
    Route::get('/api/home/disk', 'HomeController@disk');
    Route::get('/api/home/memory', 'HomeController@memory');
    Route::get('/api/home/tokenQuota', 'HomeController@tokenQuota');
    Route::get('/api/home/jobCount', 'HomeController@jobCount');
    Route::get('/api/home/tokenCount', 'HomeController@tokenCount');

    Route::get('/codeLeak', 'CodeLeakController@view');
    Route::put('/api/codeLeak/batchUpdate', 'CodeLeakController@batchUpdate');
    Route::delete('/api/codeLeak/batchDestroy', 'CodeLeakController@batchDestroy');
    Route::resource('/api/codeLeak', 'CodeLeakController');

    Route::get('/configJob', 'ConfigJobController@view');
    Route::resource('/api/configJob', 'ConfigJobController');

    Route::get('/configToken', 'ConfigTokenController@view');
    Route::resource('/api/configToken', 'ConfigTokenController');

    Route::get('/configWhitelist', 'ConfigWhitelistController@view');
    Route::resource('/api/configWhitelist', 'ConfigWhitelistController');

    Route::resource('/api/codeFragment', 'CodeFragmentController');
});
