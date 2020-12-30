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
Route::get('test1', 'API\FrontendController@test1');

// only production user er jonno 1 bar e registration hobe
Route::post('register', 'API\FrontendController@register');
Route::post('login', 'API\FrontendController@login');




// After Login
//Route::middleware('auth:api')->get('/user', function (Request $request) {
//    return $request->user();
//});

//Route::middleware('auth:api')->get('/test', 'API\BackendController@test');
// first permission
Route::middleware('auth:api')->post('/permission_list_create', 'API\BackendController@permissionListCreate');
Route::middleware('auth:api')->post('/permission_list_details', 'API\BackendController@permissionListDetails');
Route::middleware('auth:api')->post('/permission_list_update', 'API\BackendController@permissionListUpdate');

// second role
Route::middleware('auth:api')->post('/role_permission_create', 'API\BackendController@rolePermissionCreate');
Route::middleware('auth:api')->post('/role_permission_update', 'API\BackendController@rolePermissionUpdate');
Route::middleware('auth:api')->get('/roles', 'API\BackendController@roleList');

// third user
Route::middleware('auth:api')->post('/user_create', 'API\BackendController@userCreate');
Route::middleware('auth:api')->get('/user_list', 'API\BackendController@userList');
Route::middleware('auth:api')->post('/user_details', 'API\BackendController@userDetails');

// party
Route::middleware('auth:api')->get('/party_list', 'API\BackendController@partyList');
Route::middleware('auth:api')->post('/party_create', 'API\BackendController@partyCreate');
Route::middleware('auth:api')->post('/party_details', 'API\BackendController@partyDetails');
Route::middleware('auth:api')->post('/party_update', 'API\BackendController@partyUpdate');
Route::middleware('auth:api')->post('/party_delete', 'API\BackendController@partyDelete');
