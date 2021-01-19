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

// warehouse
Route::middleware('auth:api')->get('/warehouse_list', 'API\BackendController@warehouseList');
Route::middleware('auth:api')->post('/warehouse_create', 'API\BackendController@warehouseCreate');
Route::middleware('auth:api')->post('/warehouse_edit', 'API\BackendController@warehouseEdit');
Route::middleware('auth:api')->post('/warehouse_delete', 'API\BackendController@warehouseDelete');


// store
Route::middleware('auth:api')->get('/store_list', 'API\BackendController@storeList');
Route::middleware('auth:api')->post('/store_create', 'API\BackendController@storeCreate');
Route::middleware('auth:api')->post('/store_edit', 'API\BackendController@storeEdit');
Route::middleware('auth:api')->post('/store_delete', 'API\BackendController@storeDelete');



// first permission
Route::middleware('auth:api')->get('/permission_list_show', 'API\BackendController@permissionListShow');
Route::middleware('auth:api')->post('/permission_list_create', 'API\BackendController@permissionListCreate');
Route::middleware('auth:api')->post('/permission_list_details', 'API\BackendController@permissionListDetails');
Route::middleware('auth:api')->post('/permission_list_update', 'API\BackendController@permissionListUpdate');

// second role
Route::middleware('auth:api')->get('/roles', 'API\BackendController@roleList');
Route::middleware('auth:api')->post('/role_permission_create', 'API\BackendController@rolePermissionCreate');
Route::middleware('auth:api')->post('/role_permission_update', 'API\BackendController@rolePermissionUpdate');

// third user
Route::middleware('auth:api')->post('/user_create', 'API\BackendController@userCreate');
Route::middleware('auth:api')->get('/user_list', 'API\BackendController@userList');
Route::middleware('auth:api')->post('/user_details', 'API\BackendController@userDetails');
Route::middleware('auth:api')->post('/user_edit', 'API\BackendController@userEdit');
Route::middleware('auth:api')->post('/user_delete', 'API\BackendController@userDelete');

// party
Route::middleware('auth:api')->get('/party_list', 'API\BackendController@partyList');
Route::middleware('auth:api')->post('/party_create', 'API\BackendController@partyCreate');
Route::middleware('auth:api')->post('/party_details', 'API\BackendController@partyDetails');
Route::middleware('auth:api')->post('/party_update', 'API\BackendController@partyUpdate');
Route::middleware('auth:api')->post('/party_delete', 'API\BackendController@partyDelete');


// product brand
Route::middleware('auth:api')->get('/product_brand_list', 'API\BackendController@productBrandList');
Route::middleware('auth:api')->post('/product_brand_create', 'API\BackendController@productBrandCreate');
Route::middleware('auth:api')->post('/product_brand_edit', 'API\BackendController@productBrandEdit');
Route::middleware('auth:api')->post('/product_brand_delete', 'API\BackendController@productBrandDelete');

// product unit
Route::middleware('auth:api')->get('/product_unit_list', 'API\BackendController@productUnitList');
Route::middleware('auth:api')->post('/product_unit_create', 'API\BackendController@productUnitCreate');
Route::middleware('auth:api')->post('/product_unit_edit', 'API\BackendController@productUnitEdit');
Route::middleware('auth:api')->post('/product_unit_delete', 'API\BackendController@productUnitDelete');

// product
Route::middleware('auth:api')->get('/product_list', 'API\BackendController@productList');
Route::middleware('auth:api')->post('/product_create', 'API\BackendController@productCreate');
Route::middleware('auth:api')->post('/product_edit', 'API\BackendController@productEdit');
Route::middleware('auth:api')->post('/product_delete', 'API\BackendController@productDelete');

// product purchase whole
Route::middleware('auth:api')->post('/product_unit_and_brand', 'API\BackendController@productUnitAndBrand');
Route::middleware('auth:api')->get('/product_whole_purchase_list', 'API\BackendController@productWholePurchaseList');
Route::middleware('auth:api')->post('/product_whole_purchase_details', 'API\BackendController@productWholePurchaseDetails');
Route::middleware('auth:api')->post('/product_whole_purchase_create', 'API\BackendController@productWholePurchaseCreate');
Route::middleware('auth:api')->post('/product_whole_purchase_edit', 'API\BackendController@productWholePurchaseEdit');
Route::middleware('auth:api')->post('/product_whole_purchase_delete', 'API\BackendController@productWholePurchaseDelete');

// product purchase pos
Route::middleware('auth:api')->get('/product_pos_purchase_list', 'API\BackendController@productPOSPurchaseList');
Route::middleware('auth:api')->post('/product_pos_purchase_details', 'API\BackendController@productPOSPurchaseDetails');
Route::middleware('auth:api')->post('/product_pos_purchase_create', 'API\BackendController@productPOSPurchaseCreate');
Route::middleware('auth:api')->post('/product_pos_purchase_edit', 'API\BackendController@productPOSPurchaseEdit');
Route::middleware('auth:api')->post('/product_pos_purchase_delete', 'API\BackendController@productPOSPurchaseDelete');

// product purchase return
Route::middleware('auth:api')->get('/product_purchase_invoice_list', 'API\BackendController@productPurchaseInvoiceList');
Route::middleware('auth:api')->post('/product_purchase_return', 'API\BackendController@productPurchaseReturn');


// stock list
Route::middleware('auth:api')->get('/warehouse_stock_list', 'API\BackendController@warehouseStockList');
Route::middleware('auth:api')->get('/warehouse_stock_low_list', 'API\BackendController@warehouseStockLowList');
Route::middleware('auth:api')->post('/product_whole_purchase_create_with_low_product', 'API\BackendController@productWholePurchaseCreateWithLowProduct');

// stock transfer
Route::middleware('auth:api')->post('/warehouse_current_stock_list', 'API\BackendController@warehouseCurrentStockList');
Route::middleware('auth:api')->post('/check_warehouse_product_current_stock', 'API\BackendController@checkWarehouseProductCurrentStock');
Route::middleware('auth:api')->post('/warehouse_to_store_stock_create', 'API\BackendController@warehouseToStoreStockCreate');

// product sale whole
Route::middleware('auth:api')->get('/product_whole_sale_list', 'API\BackendController@productWholeSaleList');
Route::middleware('auth:api')->post('/product_whole_sale_details', 'API\BackendController@productWholeSaleDetails');
Route::middleware('auth:api')->post('/product_whole_sale_create', 'API\BackendController@productWholeSaleCreate');
Route::middleware('auth:api')->post('/product_whole_sale_edit', 'API\BackendController@productWholeSaleEdit');
Route::middleware('auth:api')->post('/product_whole_sale_delete', 'API\BackendController@productWholeSaleDelete');


// product sale pos
Route::middleware('auth:api')->get('/product_pos_sale_list', 'API\BackendController@productPOSSaleList');
Route::middleware('auth:api')->post('/product_pos_sale_details', 'API\BackendController@productPOSSaleDetails');
Route::middleware('auth:api')->post('/product_pos_sale_create', 'API\BackendController@productPOSSaleCreate');
Route::middleware('auth:api')->post('/product_pos_sale_edit', 'API\BackendController@productPOSSaleEdit');
Route::middleware('auth:api')->post('/product_pos_sale_delete', 'API\BackendController@productPOSSaleDelete');

// product sale return
Route::middleware('auth:api')->get('/product_sale_invoice_list', 'API\BackendController@productSaleInvoiceList');
Route::middleware('auth:api')->post('/product_sale_return', 'API\BackendController@productSaleReturn');

// sslcommerz
Route::post('/checkout/ssl/pay', 'API\PublicSslCommerzPaymentController@index');
Route::POST('/success', 'API\PublicSslCommerzPaymentController@success');
Route::POST('/fail', 'API\PublicSslCommerzPaymentController@fail');
Route::POST('/cancel', 'API\PublicSslCommerzPaymentController@cancel');
Route::POST('/ipn', 'API\PublicSslCommerzPaymentController@ipn');

Route::get('/ssl/redirect/{status}','API\PublicSslCommerzPaymentController@status');
