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
Route::get('/test_helper', 'HomeController@test_helper')->name('test_helper');

Route::middleware('auth:api')->get('/get_firebase_token', 'API\FrontendController@getFirebaseToken');
Route::middleware('auth:api')->get('/get_chat_user_list', 'API\FrontendController@getChatUserList');
Route::middleware('auth:api')->post('/get_chat_user_identity_list', 'API\FrontendController@getChatUserIdentityList');

// warehouse
Route::middleware('auth:api')->get('/warehouse_list', 'API\WarehouseController@warehouseList');
Route::middleware('auth:api')->post('/warehouse_create', 'API\WarehouseController@warehouseCreate');
Route::middleware('auth:api')->post('/warehouse_edit', 'API\WarehouseController@warehouseEdit');
Route::middleware('auth:api')->post('/warehouse_delete', 'API\WarehouseController@warehouseDelete');


// store
Route::middleware('auth:api')->get('/store_list', 'API\StoreController@storeList');
Route::middleware('auth:api')->post('/store_create', 'API\StoreController@storeCreate');
Route::middleware('auth:api')->post('/store_edit', 'API\StoreController@storeEdit');
Route::middleware('auth:api')->post('/store_delete', 'API\StoreController@storeDelete');



// first permission
Route::middleware('auth:api')->get('/permission_list_show', 'API\PermissionController@permissionListShow');
Route::middleware('auth:api')->post('/permission_list_create', 'API\PermissionController@permissionListCreate');
Route::middleware('auth:api')->post('/permission_list_details', 'API\PermissionController@permissionListDetails');
Route::middleware('auth:api')->post('/permission_list_update', 'API\PermissionController@permissionListUpdate');

// second role
Route::middleware('auth:api')->get('/roles', 'API\RoleController@roleList');
Route::middleware('auth:api')->post('/role_permission_create', 'API\RoleController@rolePermissionCreate');
Route::middleware('auth:api')->post('/role_permission_update', 'API\RoleController@rolePermissionUpdate');

// third user
Route::middleware('auth:api')->post('/user_create', 'API\UserController@userCreate');
Route::middleware('auth:api')->get('/user_list', 'API\UserController@userList');
Route::middleware('auth:api')->post('/user_details', 'API\UserController@userDetails');
Route::middleware('auth:api')->post('/user_edit', 'API\UserController@userEdit');
Route::middleware('auth:api')->post('/user_delete', 'API\UserController@userDelete');
Route::middleware('auth:api')->post('/changed_password', 'API\UserController@changedPassword');
Route::middleware('auth:api')->post('/password_reset', 'API\UserController@passwordEeset');

// party
Route::middleware('auth:api')->get('/party_list', 'API\PartyController@partyList');
//Route::middleware('auth:api')->get('/party_customer_list', 'API\PartyController@partyCustomerList');
//Route::middleware('auth:api')->get('/party_supplier_list', 'API\PartyController@partySupplierList');
Route::middleware('auth:api')->post('/party_create', 'API\PartyController@partyCreate');
Route::middleware('auth:api')->post('/party_details', 'API\PartyController@partyDetails');
Route::middleware('auth:api')->post('/party_update', 'API\PartyController@partyUpdate');
Route::middleware('auth:api')->post('/party_delete', 'API\PartyController@partyDelete');

//supplier
Route::middleware('auth:api')->get('/party_supplier_list', 'API\PartyController@partySupplierList');
Route::middleware('auth:api')->post('/supplier_create', 'API\PartyController@supplierCreate');
Route::middleware('auth:api')->post('/supplier_details', 'API\PartyController@supplierDetails');
Route::middleware('auth:api')->post('/supplier_update', 'API\PartyController@supplierUpdate');
Route::middleware('auth:api')->post('/supplier_delete', 'API\PartyController@supplierDelete');

// pos customer
Route::middleware('auth:api')->get('/party_customer_list', 'API\PartyController@partyCustomerList');
Route::middleware('auth:api')->post('/pos_customer_create', 'API\PartyController@posCustomerCreate');
Route::middleware('auth:api')->post('/customer_details', 'API\PartyController@customerDetails');
Route::middleware('auth:api')->post('/customer_update', 'API\PartyController@customerUpdate');
Route::middleware('auth:api')->post('/customer_delete', 'API\PartyController@customerDelete');

// whole customer
Route::middleware('auth:api')->post('/whole_customer_create', 'API\PartyController@wholeCustomerCreate');

// customer panel
Route::middleware('auth:api')->post('/customer_virtual_balance', 'API\PartyController@customerVirtualBalance');
Route::middleware('auth:api')->post('/customer_sale_information', 'API\PartyController@customerSaleInformation');
Route::middleware('auth:api')->post('/customer_sale_details_information', 'API\PartyController@customerSaleDetailsInformation');

Route::middleware('auth:api')->post('/customer_sale_by_customer_id', 'API\PartyController@customerSaleByCustomerId');
Route::middleware('auth:api')->post('/customer_sale_details_by_sale_id', 'API\PartyController@customerSaleDetailsBySaleId');


// product brand
Route::middleware('auth:api')->get('/product_brand_list', 'API\ProductBrandController@productBrandList');
Route::middleware('auth:api')->post('/product_brand_create', 'API\ProductBrandController@productBrandCreate');
Route::middleware('auth:api')->post('/product_brand_edit', 'API\ProductBrandController@productBrandEdit');
Route::middleware('auth:api')->post('/product_brand_delete', 'API\ProductBrandController@productBrandDelete');

// product unit
Route::middleware('auth:api')->get('/product_unit_list', 'API\ProductUnitController@productUnitList');
Route::middleware('auth:api')->post('/product_unit_create', 'API\ProductUnitController@productUnitCreate');
Route::middleware('auth:api')->post('/product_unit_edit', 'API\ProductUnitController@productUnitEdit');
Route::middleware('auth:api')->post('/product_unit_delete', 'API\ProductUnitController@productUnitDelete');

// product vat
Route::middleware('auth:api')->get('/product_vat_list', 'API\ProductVatController@productVatList');
Route::middleware('auth:api')->post('/product_vat_create', 'API\ProductVatController@productVatCreate');
Route::middleware('auth:api')->post('/product_vat_edit', 'API\ProductVatController@productVatEdit');
Route::middleware('auth:api')->post('/product_vat_delete', 'API\ProductVatController@productVatDelete');

// product
// changed product_list to product_list_barcode
Route::middleware('auth:api')->get('/product_list_barcode', 'API\ProductController@productListBarcode');
Route::middleware('auth:api')->get('/product_list', 'API\ProductController@productList');
Route::middleware('auth:api')->post('/product_list_with_search', 'API\ProductController@productListWithSearch');
Route::middleware('auth:api')->post('/barcode-products', 'API\ProductController@barcodeProductList');
Route::middleware('auth:api')->get('/all_active_product_list', 'API\ProductController@allActiveProductList');
Route::middleware('auth:api')->post('/all_active_product_list_barcode', 'API\ProductController@allActiveProductListBarcode');
Route::middleware('auth:api')->post('/all_active_product_list_item_code', 'API\ProductController@allActiveProductListItemcode');
Route::middleware('auth:api')->post('/product_create', 'API\ProductController@productCreate');
Route::middleware('auth:api')->post('/product_edit', 'API\ProductController@productEdit');
Route::middleware('auth:api')->post('/product_delete', 'API\ProductController@productDelete');
Route::middleware('auth:api')->post('/product_image', 'API\ProductController@productImage');

// pagination
//Route::middleware('auth:api')->get('/product_list_pagination/{cursor}/{limit}', 'API\BackendController@productListPagination');
Route::get('/product_list_pagination', 'API\PaginationController@productListPagination');
Route::post('/product_list_pagination_barcode', 'API\PaginationController@productListPaginationBarcode');
Route::post('/product_list_pagination_item_code', 'API\PaginationController@productListPaginationItemcode');
Route::post('/product_list_pagination_product_name', 'API\PaginationController@productListPaginationProductname');

Route::middleware('auth:api')->post('/warehouse_current_stock_list_pagination', 'API\PaginationController@warehouseCurrentStockListPagination');
Route::middleware('auth:api')->post('/warehouse_current_stock_list_pagination_two', 'API\PaginationController@warehouseCurrentStockListPaginationTwo');
Route::middleware('auth:api')->post('/warehouse_current_stock_list_pagination_two_with_search', 'API\PaginationController@warehouseCurrentStockListPaginationTwoWithSearch');
Route::middleware('auth:api')->post('/warehouse_current_stock_list_pagination_barcode', 'API\PaginationController@warehouseCurrentStockListPaginationBarcode');
Route::middleware('auth:api')->post('/warehouse_current_stock_list_pagination_item_code', 'API\PaginationController@warehouseCurrentStockListPaginationItemcode');
Route::middleware('auth:api')->post('/warehouse_current_stock_list_pagination_product_name', 'API\PaginationController@warehouseCurrentStockListPaginationProductName');

Route::middleware('auth:api')->post('/store_current_stock_list_pagination', 'API\PaginationController@storeCurrentStockListPagination');
Route::middleware('auth:api')->post('/store_current_stock_list_pagination_barcode', 'API\PaginationController@storeCurrentStockListPaginationBarcode');
Route::middleware('auth:api')->post('/store_current_stock_list_pagination_item_code', 'API\PaginationController@storeCurrentStockListPaginationItemcode');
Route::middleware('auth:api')->post('/store_current_stock_list_pagination_product_name', 'API\PaginationController@storeCurrentStockListPaginationProductName');
Route::middleware('auth:api')->get('/product_pos_sale_pagination', 'API\PaginationController@productPOSSaleListPagination');
Route::middleware('auth:api')->post('/product_pos_sale_list_pagination_with_search', 'API\PaginationController@productPOSSaleListPaginationWithSearch');

Route::middleware('auth:api')->post('/warehouse_current_stock_list_without_zero_pagination', 'API\PaginationController@warehouseCurrentStockListPaginationWithOutZero');
Route::middleware('auth:api')->post('/store_current_stock_list_without_zero_pagination', 'API\PaginationController@storeCurrentStockListPaginationWithOutZero');




// delivery service
//Route::middleware('auth:api')->get('/delivery_service_list', 'API\BackendController@deliveryServiceList');
//Route::middleware('auth:api')->post('/delivery_service_create', 'API\BackendController@deliveryServiceCreate');
//Route::middleware('auth:api')->post('/delivery_service_edit', 'API\BackendController@deliveryServiceEdit');
//Route::middleware('auth:api')->post('/delivery_service_delete', 'API\BackendController@deliveryServiceDelete');

// product purchase whole
Route::middleware('auth:api')->post('/product_unit_and_brand', 'API\ProductPurchaseController@productUnitAndBrand');
Route::middleware('auth:api')->get('/product_whole_purchase_list', 'API\ProductPurchaseController@productWholePurchaseList');
Route::middleware('auth:api')->get('/product_whole_purchase_list_pagination', 'API\ProductPurchaseController@productWholePurchaseListPagination');
Route::middleware('auth:api')->post('/product_whole_purchase_list_pagination_with_search', 'API\ProductPurchaseController@productWholePurchaseListPaginationWithSearch');
Route::middleware('auth:api')->post('/product_whole_purchase_details', 'API\ProductPurchaseController@productWholePurchaseDetails');
Route::middleware('auth:api')->post('/product_whole_purchase_create', 'API\ProductPurchaseController@productWholePurchaseCreate');
Route::middleware('auth:api')->post('/product_whole_purchase_edit', 'API\ProductPurchaseController@productWholePurchaseEdit');
Route::middleware('auth:api')->post('/product_whole_purchase_delete', 'API\ProductPurchaseController@productWholePurchaseDelete');
Route::middleware('auth:api')->post('/product_whole_purchase_single_product_remove', 'API\ProductPurchaseController@productWholePurchaseSingleProductRemove');
Route::middleware('auth:api')->post('/product_whole_purchase_list_search', 'API\ProductPurchaseController@productWholePurchaseListSearch');

// product purchase pos
Route::middleware('auth:api')->get('/product_pos_purchase_list', 'API\ProductPurchaseController@productPOSPurchaseList');
Route::middleware('auth:api')->post('/product_pos_purchase_details', 'API\ProductPurchaseController@productPOSPurchaseDetails');
Route::middleware('auth:api')->post('/product_pos_purchase_create', 'API\ProductPurchaseController@productPOSPurchaseCreate');
Route::middleware('auth:api')->post('/product_pos_purchase_edit', 'API\ProductPurchaseController@productPOSPurchaseEdit');
Route::middleware('auth:api')->post('/product_pos_purchase_delete', 'API\ProductPurchaseController@productPOSPurchaseDelete');

// product purchase/warehouse stock remove
Route::middleware('auth:api')->post('/product_purchase_remove', 'API\ProductPurchaseController@productPurchaseRemove');

// product purchase return
Route::middleware('auth:api')->get('/product_purchase_invoice_list', 'API\ProductPurchaseController@productPurchaseInvoiceList');
Route::middleware('auth:api')->get('/product_purchase_invoice_list_pagination', 'API\ProductPurchaseController@productPurchaseInvoiceListPagination');
Route::middleware('auth:api')->post('/product_purchase_invoice_list_pagination_with_search', 'API\ProductPurchaseController@productPurchaseInvoiceListPaginationWithSearch');
Route::middleware('auth:api')->post('/product_purchase_details', 'API\ProductPurchaseController@productPurchaseDetails');
Route::middleware('auth:api')->get('/product_purchase_return_list', 'API\ProductPurchaseController@productPurchaseReturnList');
Route::middleware('auth:api')->post('/product_purchase_return_details', 'API\ProductPurchaseController@productPurchaseReturnDetails');
Route::middleware('auth:api')->post('/product_purchase_return_details_pdf', 'API\ProductPurchaseController@productPurchaseReturnDetailsPdf');
Route::middleware('auth:api')->post('/product_purchase_return_create', 'API\ProductPurchaseController@productPurchaseReturnCreate');
Route::middleware('auth:api')->post('/product_whole_purchase_create_with_low_product', 'API\ProductPurchaseController@productWholePurchaseCreateWithLowProduct');

Route::middleware('auth:api')->post('/product_purchase_return_single_product_remove', 'API\ProductPurchaseController@productPurchaseReturnSingleProductRemove');


// warehouse stock list
Route::middleware('auth:api')->get('/warehouse_stock_list', 'API\StockController@warehouseStockList');
Route::middleware('auth:api')->post('/warehouse_current_stock_list_without_zero', 'API\StockController@warehouseCurrentStockListWithoutZero');
Route::middleware('auth:api')->get('/warehouse_stock_low_list', 'API\StockController@warehouseStockLowList');


// store stock list
//Route::middleware('auth:api')->post('/store_stock_list', 'API\BackendController@storeStockList');
//Route::middleware('auth:api')->get('/store_stock_low_list', 'API\BackendController@storeStockLowList');

// warehouse stock list
Route::middleware('auth:api')->post('/warehouse_current_stock_list', 'API\StockController@warehouseCurrentStockList');
//Route::middleware('auth:api')->post('/check_warehouse_product_current_stock', 'API\BackendController@checkWarehouseProductCurrentStock');

// stock transfer request
Route::middleware('auth:api')->post('/store_to_warehouse_stock_request_create', 'API\StockController@storeToWarehouseStockRequestCreate');
Route::middleware('auth:api')->post('/store_to_warehouse_stock_request_edit', 'API\StockController@storeToWarehouseStockRequestEdit');
Route::middleware('auth:api')->get('/store_to_warehouse_stock_request_list', 'API\StockController@storeToWarehouseStockRequestList');
Route::middleware('auth:api')->post('/store_to_warehouse_stock_request_details', 'API\StockController@storeToWarehouseStockRequestDetails');
Route::middleware('auth:api')->post('/store_to_warehouse_stock_request_delete', 'API\StockController@storeToWarehouseStockRequestDelete');
Route::middleware('auth:api')->post('/store_to_warehouse_stock_request_view_update', 'API\StockController@storeToWarehouseStockRequestViewUpdate');
Route::middleware('auth:api')->post('/store_to_warehouse_stock_request_single_product_remove', 'API\StockController@storeToWarehouseStockRequestSingleProductRemove');

// stock transfer
Route::middleware('auth:api')->post('/warehouse_to_store_stock_create', 'API\StockController@warehouseToStoreStockCreate');
Route::middleware('auth:api')->post('/warehouse_to_store_stock_edit', 'API\StockController@warehouseToStoreStockEdit');
Route::middleware('auth:api')->post('/warehouse_to_store_stock_remove', 'API\StockController@warehouseToStoreStockRemove');
Route::middleware('auth:api')->post('/store_current_stock_list', 'API\StockController@storeCurrentStockList');
Route::middleware('auth:api')->post('/store_current_product_stock', 'API\StockController@storeCurrentProductStock');
Route::middleware('auth:api')->post('/store_current_stock_list_without_zero', 'API\StockController@storeCurrentStockListWithoutZero');
Route::middleware('auth:api')->get('/stock_transfer_list', 'API\StockController@stockTransferList');
Route::middleware('auth:api')->post('/stock_transfer_list_with_search', 'API\StockController@stockTransferListWithSearch');
Route::middleware('auth:api')->post('/stock_transfer_details', 'API\StockController@stockTransferDetails');
Route::middleware('auth:api')->post('/stock_transfer_single_product_remove', 'API\StockController@stockTransferSingleProductRemove');

Route::middleware('auth:api')->post('/universal_search_store_current_product_stock', 'API\StockController@universalSearchStoreCurrentProductStock');

// stock to warehouse stock return
Route::middleware('auth:api')->post('/store_to_warehouse_stock_return_create', 'API\StockController@storeToWarehouseStockReturnCreate');
Route::middleware('auth:api')->post('/store_to_warehouse_stock_return_edit', 'API\StockController@storeToWarehouseStockReturnEdit');
Route::middleware('auth:api')->get('/store_to_warehouse_stock_return_list', 'API\StockController@storeToWarehouseStockReturnList');
Route::middleware('auth:api')->post('/store_to_warehouse_stock_return_details', 'API\StockController@storeToWarehouseStockReturnDetails');

// stock_sync
Route::middleware('auth:api')->get('/stock_sync', 'API\StockController@stock_sync');


// product sale whole
Route::middleware('auth:api')->get('/product_sale_invoice_no', 'API\ProductSaleController@productSaleInvoiceNo');
Route::middleware('auth:api')->get('/product_whole_sale_list', 'API\ProductSaleController@productWholeSaleList');
Route::middleware('auth:api')->post('/product_whole_sale_list_with_search', 'API\ProductSaleController@productWholeSaleListWithSearch');
Route::middleware('auth:api')->get('/product_whole_sale_list_pagination', 'API\ProductSaleController@productWholeSaleListPagination');
Route::middleware('auth:api')->post('/product_whole_sale_list_pagination_with_search', 'API\ProductSaleController@productWholeSaleListPaginationWithSearch');
Route::middleware('auth:api')->post('/product_whole_sale_details', 'API\ProductSaleController@productWholeSaleDetails');
Route::middleware('auth:api')->post('/product_whole_sale_create', 'API\ProductSaleController@productWholeSaleCreate');
Route::middleware('auth:api')->post('/product_whole_sale_edit', 'API\ProductSaleController@productWholeSaleEdit');
Route::middleware('auth:api')->post('/product_whole_sale_delete', 'API\ProductSaleController@productWholeSaleDelete');
Route::middleware('auth:api')->post('/product_whole_sale_single_product_remove', 'API\ProductSaleController@productWholeSaleSingleProductRemove');
Route::middleware('auth:api')->post('/product_whole_sale_list_search', 'API\ProductSaleController@productWholeSaleListSearch');


// product sale pos
Route::middleware('auth:api')->get('/product_pos_sale_list', 'API\ProductSaleController@productPOSSaleList');
Route::middleware('auth:api')->post('/product_pos_sale_list_search', 'API\ProductSaleController@productPOSSaleListSearch');
Route::middleware('auth:api')->post('/product_pos_sale_details', 'API\ProductSaleController@productPOSSaleDetails');
Route::middleware('auth:api')->post('/product_pos_sale_create', 'API\ProductSaleController@productPOSSaleCreate');
Route::middleware('auth:api')->post('/product_pos_sale_edit', 'API\ProductSaleController@productPOSSaleEdit');
Route::middleware('auth:api')->post('/product_pos_sale_delete', 'API\ProductSaleController@productPOSSaleDelete');
Route::middleware('auth:api')->post('/product_pos_sale_single_product_remove', 'API\ProductSaleController@productPOSSaleSingleProductRemove');

// product sale return
Route::middleware('auth:api')->get('/product_sale_invoice_list', 'API\ProductSaleController@productSaleInvoiceList');
Route::middleware('auth:api')->get('/product_sale_invoice_list_pagination', 'API\ProductSaleController@productSaleInvoiceListPagination');
Route::middleware('auth:api')->post('/product_sale_invoice_list_pagination_with_search', 'API\ProductSaleController@productSaleInvoiceListPaginationWithSearch');
Route::middleware('auth:api')->post('/product_sale_details', 'API\ProductSaleController@productSaleDetails');
Route::middleware('auth:api')->get('/product_sale_return_list', 'API\ProductSaleController@productSaleReturnList');
Route::middleware('auth:api')->post('/product_sale_return_details', 'API\ProductSaleController@productSaleReturnDetails');
Route::middleware('auth:api')->post('/product_sale_return_create', 'API\ProductSaleController@productSaleReturnCreate');
Route::middleware('auth:api')->post('/product_sale_return_edit', 'API\ProductSaleController@productSaleReturnEdit');

//Route::middleware('auth:api')->post('/product_sale_return_single_product_remove', 'API\ProductSaleController@productSaleReturnSingleProductRemove');

// product sale exchange
Route::middleware('auth:api')->get('/product_sale_exchange_list', 'API\ProductSaleExchangeController@productSaleExchangeList');
Route::middleware('auth:api')->post('/product_sale_exchange_details', 'API\ProductSaleExchangeController@productSaleExchangeDetails');
Route::middleware('auth:api')->post('/product_sale_exchange_create', 'API\ProductSaleExchangeController@productSaleExchangeCreate');
Route::middleware('auth:api')->post('/product_sale_exchange_edit', 'API\ProductSaleExchangeController@productSaleExchangeEdit');
Route::middleware('auth:api')->post('/product_sale_exchange_delete', 'API\ProductSaleExchangeController@productSaleExchangeDelete');

// warehouse product damages
Route::middleware('auth:api')->get('/warehouse_product_damage_list', 'API\WarehouseController@warehouseProductDamageList');
Route::middleware('auth:api')->post('/warehouse_product_damage_details', 'API\WarehouseController@warehouseProductDamageDetails');
Route::middleware('auth:api')->post('/warehouse_product_damage_create', 'API\WarehouseController@warehouseProductDamageCreate');
Route::middleware('auth:api')->post('/warehouse_product_damage_edit', 'API\WarehouseController@warehouseProductDamageEdit');
Route::middleware('auth:api')->post('/warehouse_product_damage_delete', 'API\WarehouseController@warehouseProductDamageDelete');

// store product damages
Route::middleware('auth:api')->get('/store_product_damage_list', 'API\StoreController@storeProductDamageList');
Route::middleware('auth:api')->post('/store_product_damage_details', 'API\StoreController@storeProductDamageDetails');
Route::middleware('auth:api')->post('/store_product_damage_create', 'API\StoreController@storeProductDamageCreate');
Route::middleware('auth:api')->post('/store_product_damage_edit', 'API\StoreController@storeProductDamageEdit');
Route::middleware('auth:api')->post('/store_product_damage_delete', 'API\StoreController@storeProductDamageDelete');

// payment
Route::middleware('auth:api')->get('/supplier_list', 'API\PaymentController@supplierList');
Route::middleware('auth:api')->get('/customer_list', 'API\PaymentController@customerList');
Route::middleware('auth:api')->get('/whole_sale_customer_list', 'API\PaymentController@wholeSaleCustomerList');
Route::middleware('auth:api')->get('/whole_sale_customer_list_pagination', 'API\PaymentController@wholeSaleCustomerListPagination');
Route::middleware('auth:api')->post('/whole_sale_customer_list_pagination_with_search', 'API\PaymentController@wholeSaleCustomerListPaginationWithSearch');
Route::middleware('auth:api')->get('/pos_sale_customer_list', 'API\PaymentController@posSaleCustomerList');
Route::middleware('auth:api')->get('/pos_sale_customer_list_pagination', 'API\PaymentController@posSaleCustomerListPagination');
Route::middleware('auth:api')->post('/pos_sale_customer_list_pagination_with_search', 'API\PaymentController@posSaleCustomerListPaginationWithSearch');
Route::middleware('auth:api')->get('/payment_paid_due_list', 'API\PaymentController@paymentPaidDueList');
Route::middleware('auth:api')->post('/payment_paid_due_list_by_supplier', 'API\PaymentController@paymentPaidDueListBySupplier');
Route::middleware('auth:api')->post('/payment_paid_due_create', 'API\PaymentController@paymentPaidDueCreate');
Route::middleware('auth:api')->get('/payment_invoice_no', 'API\PaymentController@getPaymentInvoiceNo');
Route::middleware('auth:api')->get('/supplier_due_payment_list', 'API\PaymentController@supplierDuePaymentList');
Route::middleware('auth:api')->post('/supplier_due_payment_create', 'API\PaymentController@SupplierDuePaymentCreate');
Route::middleware('auth:api')->post('/supplier_due_payment_edit', 'API\PaymentController@SupplierDuePaymentEdit');
Route::middleware('auth:api')->get('/payment_collection_due_list', 'API\PaymentController@paymentCollectionDueList');
Route::middleware('auth:api')->post('/payment_collection_due_list_by_customer', 'API\PaymentController@paymentCollectionDueListByCustomer');
Route::middleware('auth:api')->post('/payment_collection_due_create', 'API\PaymentController@paymentCollectionDueCreate');
Route::middleware('auth:api')->get('/store_due_paid_list', 'API\PaymentController@storeDuePaidList');
Route::middleware('auth:api')->post('/store_due_paid_list_by_store_date_difference', 'API\PaymentController@storeDuePaidListByStoreDateDifference');

// report
Route::middleware('auth:api')->post('/date_wise_sales_report', 'API\ReportController@dateWiseSalesReport');
Route::middleware('auth:api')->post('/date_wise_vats_report', 'API\ReportController@dateWiseVatsReport');
Route::middleware('auth:api')->post('/date_and_supplier_wise_purchase_report', 'API\ReportController@dateAndSupplierWisePurchaseReport');
Route::middleware('auth:api')->post('/date_and_customer_wise_whole_sale_report', 'API\ReportController@dateAndCustomerWiseWholeSaleReport');

// dashboard history
Route::middleware('auth:api')->get('/today_purchase', 'API\DashboardController@todayPurchase');
Route::middleware('auth:api')->get('/total_purchase', 'API\DashboardController@totalPurchase');
Route::middleware('auth:api')->get('/today_purchase_return', 'API\DashboardController@todayPurchaseReturn');
Route::middleware('auth:api')->get('/total_purchase_return', 'API\DashboardController@totalPurchaseReturn');
Route::middleware('auth:api')->get('/today_sale', 'API\DashboardController@todaySale');
Route::middleware('auth:api')->get('/total_sale', 'API\DashboardController@totalSale');
Route::middleware('auth:api')->get('/today_sale_return', 'API\DashboardController@todaySaleReturn');
Route::middleware('auth:api')->get('/total_sale_return', 'API\DashboardController@totalSaleReturn');
Route::middleware('auth:api')->get('/today_profit', 'API\DashboardController@todayProfit');
Route::middleware('auth:api')->get('/total_profit', 'API\DashboardController@totalProfit');

// sslcommerz
Route::post('/checkout/ssl/pay', 'API\PublicSslCommerzPaymentController@index');
Route::POST('/success', 'API\PublicSslCommerzPaymentController@success');
Route::POST('/fail', 'API\PublicSslCommerzPaymentController@fail');
Route::POST('/cancel', 'API\PublicSslCommerzPaymentController@cancel');
Route::POST('/ipn', 'API\PublicSslCommerzPaymentController@ipn');

Route::get('/ssl/redirect/{status}','API\PublicSslCommerzPaymentController@status');




// start HRM + Accounting

// department
Route::middleware('auth:api')->get('/department_list', 'API\DepartmentController@departmentList');
Route::middleware('auth:api')->get('/department_list_active', 'API\DepartmentController@departmentListActive');
Route::middleware('auth:api')->post('/department_create', 'API\DepartmentController@departmentCreate');
Route::middleware('auth:api')->post('/department_edit', 'API\DepartmentController@departmentEdit');
Route::middleware('auth:api')->post('/department_delete', 'API\DepartmentController@departmentDelete');

// designation
Route::middleware('auth:api')->get('/designation_list', 'API\DesignationController@designationList');
Route::middleware('auth:api')->get('/designation_list_active', 'API\DesignationController@designationListActive');
Route::middleware('auth:api')->post('/designation_create', 'API\DesignationController@designationCreate');
Route::middleware('auth:api')->post('/designation_edit', 'API\DesignationController@designationEdit');
Route::middleware('auth:api')->post('/designation_delete', 'API\DesignationController@designationDelete');

// holiday
Route::middleware('auth:api')->get('/holiday_list', 'API\HolidayController@holidayList');
Route::middleware('auth:api')->post('/holiday_create', 'API\HolidayController@holidayCreate');
Route::middleware('auth:api')->post('/holiday_edit', 'API\HolidayController@holidayEdit');
Route::middleware('auth:api')->post('/holiday_delete', 'API\HolidayController@holidayDelete');

// weekend
Route::middleware('auth:api')->get('/weekend_list', 'API\WeekendController@weekendList');
Route::middleware('auth:api')->post('/weekend_create', 'API\WeekendController@weekendCreate');
Route::middleware('auth:api')->post('/weekend_edit', 'API\WeekendController@weekendEdit');
Route::middleware('auth:api')->post('/weekend_delete', 'API\WeekendController@weekendDelete');

// leave Category
Route::middleware('auth:api')->get('/leave_category_list', 'API\LeaveController@leaveCategoryList');
Route::middleware('auth:api')->post('/leave_category_create', 'API\LeaveController@leaveCategoryCreate');
Route::middleware('auth:api')->post('/leave_category_edit', 'API\LeaveController@leaveCategoryEdit');
Route::middleware('auth:api')->post('/leave_category_delete', 'API\LeaveController@leaveCategoryDelete');

// Employee
Route::middleware('auth:api')->get('/employee_list', 'API\EmployeeController@employeeList');
Route::middleware('auth:api')->get('/employee_list_active', 'API\EmployeeController@employeeListActive');
Route::middleware('auth:api')->post('/employee_create', 'API\EmployeeController@employeeCreate');
Route::middleware('auth:api')->post('/employee_edit', 'API\EmployeeController@employeeEdit');
Route::middleware('auth:api')->post('/employee_delete', 'API\EmployeeController@employeeDelete');
Route::middleware('auth:api')->post('/employee_image', 'API\EmployeeController@employeeImage');

// Employee Office Information
Route::middleware('auth:api')->get('/employee_office_information_list', 'API\EmployeeController@employeeOfficeInformationList');
Route::middleware('auth:api')->post('/employee_office_information_create', 'API\EmployeeController@employeeOfficeInformationCreate');
Route::middleware('auth:api')->post('/employee_office_information_edit', 'API\EmployeeController@employeeOfficeInformationEdit');
Route::middleware('auth:api')->post('/employee_office_information_delete', 'API\EmployeeController@employeeOfficeInformationDelete');

// Employee Salary Information
Route::middleware('auth:api')->get('/employee_salary_information_list', 'API\EmployeeController@employeeSalaryInformationList');
Route::middleware('auth:api')->post('/employee_salary_information_create', 'API\EmployeeController@employeeSalaryInformationCreate');
Route::middleware('auth:api')->post('/employee_salary_information_edit', 'API\EmployeeController@employeeSalaryInformationEdit');
Route::middleware('auth:api')->post('/employee_salary_information_delete', 'API\EmployeeController@employeeSalaryInformationDelete');


// Leave Application
Route::middleware('auth:api')->get('/leave_application_list', 'API\LeaveController@leaveApplicationList');
Route::middleware('auth:api')->post('/leave_application_create', 'API\LeaveController@leaveApplicationCreate');
Route::middleware('auth:api')->post('/leave_application_edit', 'API\LeaveController@leaveApplicationEdit');
Route::middleware('auth:api')->post('/leave_application_delete', 'API\LeaveController@leaveApplicationDelete');

// Attendance Log
Route::middleware('auth:api')->get('/attendance_list', 'API\AttendanceController@attendanceList');
Route::middleware('auth:api')->post('/attendance_create', 'API\AttendanceController@attendanceCreate');
Route::middleware('auth:api')->post('/attendance_edit', 'API\AttendanceController@attendanceEdit');
//Route::middleware('auth:api')->post('/attendance_delete', 'API\BackendController@attendanceDelete');
Route::middleware('auth:api')->post('/attendance_report', 'API\AttendanceController@attendanceReport');

// payroll/salary sheet
Route::middleware('auth:api')->post('/total_absent_by_employee', 'API\PayrollController@totalAbsentByEmployee');
Route::middleware('auth:api')->post('/total_late_by_employee', 'API\PayrollController@totalLateByEmployee');
Route::middleware('auth:api')->post('/total_weekend', 'API\PayrollController@totalWeekend');
Route::middleware('auth:api')->post('/total_holiday', 'API\PayrollController@totalHoliday');
Route::middleware('auth:api')->post('/total_working_day', 'API\PayrollController@totalWorkingDay');
//Route::middleware('auth:api')->post('/employee_details_department_wise', 'API\PayrollController@employeeDetailsDepartmentWise');
Route::middleware('auth:api')->post('/employee_details_employee_wise', 'API\PayrollController@employeeDetailsEmployeeWise');
Route::middleware('auth:api')->post('/payroll_create', 'API\PayrollController@payrollCreate');
Route::middleware('auth:api')->post('/payroll_edit', 'API\PayrollController@payrollEdit');
Route::middleware('auth:api')->get('/payroll_list', 'API\PayrollController@payrollList');

// payslip
Route::middleware('auth:api')->post('/payslip_create', 'API\PayrollController@payslipCreate');
Route::middleware('auth:api')->get('/payslip_list', 'API\PayrollController@payslipList');


// accounts

// expense category
Route::middleware('auth:api')->get('/expense_category_list', 'API\AccountController@expenseCategoryList');
Route::middleware('auth:api')->post('/expense_category_create', 'API\AccountController@expenseCategoryCreate');
Route::middleware('auth:api')->post('/expense_category_edit', 'API\AccountController@expenseCategoryEdit');
Route::middleware('auth:api')->post('/expense_category_delete', 'API\AccountController@expenseCategoryDelete');

// shop expense
Route::middleware('auth:api')->get('/store_expense_list', 'API\AccountController@storeExpenseList');
Route::middleware('auth:api')->post('/store_expense_create', 'API\AccountController@storeExpenseCreate');
Route::middleware('auth:api')->post('/store_expense_edit', 'API\AccountController@storeExpenseEdit');
Route::middleware('auth:api')->post('/store_expense_delete', 'API\AccountController@storeExpenseDelete');

// tangible asset
Route::middleware('auth:api')->get('/tangible_asset_list', 'API\AccountController@tangibleAssetList');
Route::middleware('auth:api')->post('/tangible_asset_create', 'API\AccountController@tangibleAssetCreate');
Route::middleware('auth:api')->post('/tangible_asset_edit', 'API\AccountController@tangibleAssetEdit');
Route::middleware('auth:api')->post('/tangible_asset_delete', 'API\AccountController@tangibleAssetDelete');

// voucher type
Route::middleware('auth:api')->get('/voucher_type_list', 'API\AccountController@voucherTypeList');
Route::middleware('auth:api')->post('/voucher_type_create', 'API\AccountController@voucherTypeCreate');
Route::middleware('auth:api')->post('/voucher_type_edit', 'API\AccountController@voucherTypeEdit');
Route::middleware('auth:api')->post('/voucher_type_delete', 'API\AccountController@voucherTypeDelete');

// chart of account
Route::middleware('auth:api')->get('/chart_of_account_list', 'API\AccountController@chartOfAccountList');
Route::middleware('auth:api')->post('/chart_of_account_list_by_head_name', 'API\AccountController@chartOfAccountListByName');
//Route::middleware('auth:api')->get('/chart_of_account_recursive_list', 'API\AccountController@chartOfAccountRecursiveList');
Route::middleware('auth:api')->get('/chart_of_account_active_list', 'API\AccountController@chartOfAccountActiveList');
Route::middleware('auth:api')->get('/chart_of_account_is_transaction_list', 'API\AccountController@chartOfAccountIsTransactionList');
Route::middleware('auth:api')->get('/chart_of_account_is_cash_book_list', 'API\AccountController@chartOfAccountIsCashBookList');
Route::middleware('auth:api')->get('/chart_of_account_is_general_ledger_list', 'API\AccountController@chartOfAccountIsGeneralLedgerList');
Route::middleware('auth:api')->post('/chart_of_account_details', 'API\AccountController@chartOfAccountDetails');
Route::middleware('auth:api')->post('/chart_of_account_generate_head_code', 'API\AccountController@chartOfAccountGenerateHeadCode');
Route::middleware('auth:api')->post('/chart_of_account_parent_head_details', 'API\AccountController@chartOfAccountParentHeadDetails');
Route::middleware('auth:api')->post('/chart_of_account_create', 'API\AccountController@chartOfAccountCreate');
Route::middleware('auth:api')->post('/chart_of_account_edit', 'API\AccountController@chartOfAccountEdit');
Route::middleware('auth:api')->post('/chart_of_account_delete', 'API\AccountController@chartOfAccountDelete');

// chart of account transaction
Route::middleware('auth:api')->get('/chart_of_account_transaction_list', 'API\AccountController@chartOfAccountTransactionList');
Route::middleware('auth:api')->post('/chart_of_account_transaction_details', 'API\AccountController@chartOfAccountTransactionDetails');
Route::middleware('auth:api')->post('/chart_of_account_transaction_create', 'API\AccountController@chartOfAccountTransactionCreate');
Route::middleware('auth:api')->post('/chart_of_account_transaction_edit', 'API\AccountController@chartOfAccountTransactionEdit');
Route::middleware('auth:api')->post('/chart_of_account_transaction_delete', 'API\AccountController@chartOfAccountTransactionDelete');

// ledger
Route::middleware('auth:api')->post('/ledger', 'API\AccountController@ledger');
Route::middleware('auth:api')->post('/cash_book_report', 'API\AccountController@cashBookReport');
Route::middleware('auth:api')->post('/ledger_report', 'API\AccountController@ledgerReport');
Route::middleware('auth:api')->post('/balance_sheet', 'API\AccountController@balanceSheet');

// database backup
Route::get('/backup_database', 'HomeController@backup_database')->name('backup_database');
//Route::middleware('auth:api')->get('/backup_database', 'API\BackendController@backupDatabase');
//Route::middleware('auth:api')->get('/sum_sub_total', 'API\BackendController@sum_sub_total');
