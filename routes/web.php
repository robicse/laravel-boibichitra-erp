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
Route::get('/warehouse_stock_sync', 'StockSyncController@warehouse_stock_sync')->name('warehouse_stock_sync');
Route::get('/warehouse_store_stock_sync', 'StockSyncController@warehouse_store_stock_sync')->name('warehouse_store_stock_sync');

// test
Route::get('/test', 'HomeController@test')->name('test');
Route::get('/manually_pos_sale_update', 'HomeController@manually_pos_sale_update')->name('manually_pos_sale_update');
Route::get('/manually_stock_transfer_vat_update', 'HomeController@manually_stock_transfer_vat_update')->name('manually_stock_transfer_vat_update');
Route::get('/manually_discount_update', 'HomeController@manually_discount_update')->name('manually_discount_update');
Route::get('/manually_purchase_price_update', 'HomeController@manually_purchase_price_update')->name('manually_purchase_price_update');
Route::get('/backup_database', 'HomeController@backup_database')->name('backup_database');


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
