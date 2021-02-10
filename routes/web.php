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


/* use command line*/
#php artisan cache:clear
#php artisan config:cache
#php artisan view:clear
#php artisan config:cache


/*use browser*/
Route::get('/clear-cache', function() {
    $exitCode = Artisan::call('cache:clear');
    return 'cache clear';
});
Route::get('/config-cache', function() {
    $exitCode = Artisan::call('config:cache');
    return 'config:cache';
});
Route::get('/view-cache', function() {
    $exitCode = Artisan::call('view:cache');
    return 'view:cache';
});
Route::get('/view-clear', function() {
    $exitCode = Artisan::call('view:clear');
    return 'view:clear';
});


// stock_sync
Route::get('/stock_sync', 'StockSyncController@stock_sync')->name('stock_sync');


Route::get('/', function () {
    //return view('welcome');
    return redirect()->route('login');
});

//Route::group(['middleware' => ['auth']], function() {
    //Route::resource('roles','RoleController');
    //Route::resource('users','UserController');
//});



Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');

Route::group(['middleware' => ['auth']], function() {
    Route::get('change-password/{id}', 'UserController@changedPassword')->name('password.change_password');
    Route::post('change-password-update', 'UserController@changedPasswordUpdated')->name('password.change_password_update');

    Route::resource('roles', 'RoleController');
    Route::resource('users', 'UserController');
});
