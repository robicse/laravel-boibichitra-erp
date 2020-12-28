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


//Route::get('/clear-cache', function() {
//    $exitCode = Artisan::call('cache:clear');
//    return 'cache clear';
//});
//Route::get('/config-cache', function() {
//    $exitCode = Artisan::call('config:cache');
//    return 'config:cache';
//});
//Route::get('/view-cache', function() {
//    $exitCode = Artisan::call('view:cache');
//    return 'view:cache';
//});
//Route::get('/view-clear', function() {
//    $exitCode = Artisan::call('view:clear');
//    return 'view:clear';
//});






// Before Login
// good way
// http://localhost/boibichitra-accounts/public/api/test
//Route::get('test', 'API\FrontendController@test');

Route::post('register', 'API\FrontendController@register');
Route::post('login', 'API\FrontendController@login');




// After Login
//Route::middleware('auth:api')->get('/user', function (Request $request) {
//    return $request->user();
//});

Route::middleware('auth:api')->get('/test', 'API\BackendController@test');
// role
Route::middleware('auth:api')->get('/roles', 'API\BackendController@roleList');
// party
Route::middleware('auth:api')->get('/party_list', 'API\BackendController@partyList');
Route::middleware('auth:api')->post('/party_create', 'API\BackendController@partyCreate');
Route::middleware('auth:api')->post('/party_details', 'API\BackendController@partyDetails');
Route::middleware('auth:api')->post('/party_update', 'API\BackendController@partyUpdate');
Route::middleware('auth:api')->post('/party_delete', 'API\BackendController@partyDelete');
