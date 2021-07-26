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
Route::middleware('auth:api')->get('/party_customer_list', 'API\BackendController@partyCustomerList');
Route::middleware('auth:api')->get('/party_supplier_list', 'API\BackendController@partySupplierList');
Route::middleware('auth:api')->post('/party_create', 'API\BackendController@partyCreate');
Route::middleware('auth:api')->post('/party_details', 'API\BackendController@partyDetails');
Route::middleware('auth:api')->post('/party_update', 'API\BackendController@partyUpdate');
Route::middleware('auth:api')->post('/party_delete', 'API\BackendController@partyDelete');

// customer panel
Route::middleware('auth:api')->post('/customer_virtual_balance', 'API\BackendController@customerVirtualBalance');
Route::middleware('auth:api')->post('/customer_sale_information', 'API\BackendController@customerSaleInformation');
Route::middleware('auth:api')->post('/customer_sale_details_information', 'API\BackendController@customerSaleDetailsInformation');

Route::middleware('auth:api')->post('/customer_sale_by_customer_id', 'API\BackendController@customerSaleByCustomerId');
Route::middleware('auth:api')->post('/customer_sale_details_by_sale_id', 'API\BackendController@customerSaleDetailsBySaleId');


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

// product vat
Route::middleware('auth:api')->get('/product_vat_list', 'API\BackendController@productVatList');
Route::middleware('auth:api')->post('/product_vat_create', 'API\BackendController@productVatCreate');
Route::middleware('auth:api')->post('/product_vat_edit', 'API\BackendController@productVatEdit');
Route::middleware('auth:api')->post('/product_vat_delete', 'API\BackendController@productVatDelete');

// product
Route::middleware('auth:api')->get('/product_list', 'API\BackendController@productList');
Route::middleware('auth:api')->post('/barcode-products', 'API\BackendController@barcodeProductList');
Route::middleware('auth:api')->get('/all_active_product_list', 'API\BackendController@allActiveProductList');
Route::middleware('auth:api')->post('/all_active_product_list_barcode', 'API\BackendController@allActiveProductListBarcode');
Route::middleware('auth:api')->post('/all_active_product_list_item_code', 'API\BackendController@allActiveProductListItemcode');
Route::middleware('auth:api')->post('/product_create', 'API\BackendController@productCreate');
Route::middleware('auth:api')->post('/product_edit', 'API\BackendController@productEdit');
Route::middleware('auth:api')->post('/product_delete', 'API\BackendController@productDelete');
Route::middleware('auth:api')->post('/product_image', 'API\BackendController@productImage');

// pagination
//Route::middleware('auth:api')->get('/product_list_pagination/{cursor}/{limit}', 'API\BackendController@productListPagination');
Route::get('/product_list_pagination', 'API\BackendController@productListPagination');
Route::post('/product_list_pagination_barcode', 'API\BackendController@productListPaginationBarcode');
Route::post('/product_list_pagination_item_code', 'API\BackendController@productListPaginationItemcode');
Route::post('/product_list_pagination_product_name', 'API\BackendController@productListPaginationProductname');

Route::middleware('auth:api')->post('/warehouse_current_stock_list_pagination', 'API\BackendController@warehouseCurrentStockListPagination');
Route::middleware('auth:api')->post('/warehouse_current_stock_list_pagination_barcode', 'API\BackendController@warehouseCurrentStockListPaginationBarcode');
Route::middleware('auth:api')->post('/warehouse_current_stock_list_pagination_item_code', 'API\BackendController@warehouseCurrentStockListPaginationItemcode');
Route::middleware('auth:api')->post('/warehouse_current_stock_list_pagination_product_name', 'API\BackendController@warehouseCurrentStockListPaginationProductName');

Route::middleware('auth:api')->post('/store_current_stock_list_pagination', 'API\BackendController@storeCurrentStockListPagination');
Route::middleware('auth:api')->post('/store_current_stock_list_pagination_barcode', 'API\BackendController@storeCurrentStockListPaginationBarcode');
Route::middleware('auth:api')->post('/store_current_stock_list_pagination_item_code', 'API\BackendController@storeCurrentStockListPaginationItemcode');
Route::middleware('auth:api')->post('/store_current_stock_list_pagination_product_name', 'API\BackendController@storeCurrentStockListPaginationProductName');




// product brand
//Route::middleware('auth:api')->get('/delivery_service_list', 'API\BackendController@deliveryServiceList');
//Route::middleware('auth:api')->post('/delivery_service_create', 'API\BackendController@deliveryServiceCreate');
//Route::middleware('auth:api')->post('/delivery_service_edit', 'API\BackendController@deliveryServiceEdit');
//Route::middleware('auth:api')->post('/delivery_service_delete', 'API\BackendController@deliveryServiceDelete');

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

// product purchase/warehouse stock remove
Route::middleware('auth:api')->post('/product_purchase_remove', 'API\BackendController@productPurchaseRemove');

// product purchase return
Route::middleware('auth:api')->get('/product_purchase_invoice_list', 'API\BackendController@productPurchaseInvoiceList');
Route::middleware('auth:api')->post('/product_purchase_details', 'API\BackendController@productPurchaseDetails');
Route::middleware('auth:api')->get('/product_purchase_return_list', 'API\BackendController@productPurchaseReturnList');
Route::middleware('auth:api')->post('/product_purchase_return_details', 'API\BackendController@productPurchaseReturnDetails');
Route::middleware('auth:api')->post('/product_purchase_return_create', 'API\BackendController@productPurchaseReturnCreate');


// warehouse stock list
Route::middleware('auth:api')->get('/warehouse_stock_list', 'API\BackendController@warehouseStockList');
Route::middleware('auth:api')->post('/warehouse_current_stock_list_without_zero', 'API\BackendController@warehouseCurrentStockListWithoutZero');
Route::middleware('auth:api')->get('/warehouse_stock_low_list', 'API\BackendController@warehouseStockLowList');
Route::middleware('auth:api')->post('/product_whole_purchase_create_with_low_product', 'API\BackendController@productWholePurchaseCreateWithLowProduct');

// store stock list
//Route::middleware('auth:api')->post('/store_stock_list', 'API\BackendController@storeStockList');
//Route::middleware('auth:api')->get('/store_stock_low_list', 'API\BackendController@storeStockLowList');

// warehouse stock list
Route::middleware('auth:api')->post('/warehouse_current_stock_list', 'API\BackendController@warehouseCurrentStockList');
//Route::middleware('auth:api')->post('/check_warehouse_product_current_stock', 'API\BackendController@checkWarehouseProductCurrentStock');

// stock transfer request
Route::middleware('auth:api')->post('/store_to_warehouse_stock_request_create', 'API\BackendController@storeToWarehouseStockRequestCreate');
Route::middleware('auth:api')->post('/store_to_warehouse_stock_request_edit', 'API\BackendController@storeToWarehouseStockRequestEdit');
Route::middleware('auth:api')->get('/store_to_warehouse_stock_request_list', 'API\BackendController@storeToWarehouseStockRequestList');
Route::middleware('auth:api')->post('/store_to_warehouse_stock_request_details', 'API\BackendController@storeToWarehouseStockRequestDetails');
Route::middleware('auth:api')->post('/store_to_warehouse_stock_request_delete', 'API\BackendController@storeToWarehouseStockRequestDelete');

// stock transfer
Route::middleware('auth:api')->post('/warehouse_to_store_stock_create', 'API\BackendController@warehouseToStoreStockCreate');
Route::middleware('auth:api')->post('/warehouse_to_store_stock_edit', 'API\BackendController@warehouseToStoreStockEdit');
Route::middleware('auth:api')->post('/warehouse_to_store_stock_remove', 'API\BackendController@warehouseToStoreStockRemove');
Route::middleware('auth:api')->post('/store_current_stock_list', 'API\BackendController@storeCurrentStockList');
Route::middleware('auth:api')->post('/store_current_product_stock', 'API\BackendController@storeCurrentProductStock');
Route::middleware('auth:api')->post('/store_current_stock_list_without_zero', 'API\BackendController@storeCurrentStockListWithoutZero');
Route::middleware('auth:api')->get('/stock_transfer_list', 'API\BackendController@stockTransferList');
Route::middleware('auth:api')->post('/stock_transfer_details', 'API\BackendController@stockTransferDetails');

// stock to warehouse stock return
Route::middleware('auth:api')->post('/store_to_warehouse_stock_return_create', 'API\BackendController@storeToWarehouseStockReturnCreate');
Route::middleware('auth:api')->post('/store_to_warehouse_stock_return_edit', 'API\BackendController@storeToWarehouseStockReturnEdit');
Route::middleware('auth:api')->get('/store_to_warehouse_stock_return_list', 'API\BackendController@storeToWarehouseStockReturnList');
Route::middleware('auth:api')->post('/store_to_warehouse_stock_return_details', 'API\BackendController@storeToWarehouseStockReturnDetails');


// product sale whole
Route::middleware('auth:api')->get('/product_whole_sale_list', 'API\BackendController@productWholeSaleList');
Route::middleware('auth:api')->post('/product_whole_sale_details', 'API\BackendController@productWholeSaleDetails');
Route::middleware('auth:api')->post('/product_whole_sale_create', 'API\BackendController@productWholeSaleCreate');
Route::middleware('auth:api')->post('/product_whole_sale_edit', 'API\BackendController@productWholeSaleEdit');
Route::middleware('auth:api')->post('/product_whole_sale_delete', 'API\BackendController@productWholeSaleDelete');


// product sale pos
Route::middleware('auth:api')->get('/product_pos_sale_list', 'API\BackendController@productPOSSaleList');
Route::middleware('auth:api')->get('/product_pos_sale_pagination', 'API\BackendController@productPOSSaleListPagination');
Route::middleware('auth:api')->post('/product_pos_sale_details', 'API\BackendController@productPOSSaleDetails');
Route::middleware('auth:api')->post('/product_pos_sale_create', 'API\BackendController@productPOSSaleCreate');
Route::middleware('auth:api')->post('/product_pos_sale_edit', 'API\BackendController@productPOSSaleEdit');
Route::middleware('auth:api')->post('/product_pos_sale_delete', 'API\BackendController@productPOSSaleDelete');

// product sale return
Route::middleware('auth:api')->get('/product_sale_invoice_list', 'API\BackendController@productSaleInvoiceList');
Route::middleware('auth:api')->post('/product_sale_details', 'API\BackendController@productSaleDetails');
Route::middleware('auth:api')->get('/product_sale_return_list', 'API\BackendController@productSaleReturnList');
Route::middleware('auth:api')->post('/product_sale_return_details', 'API\BackendController@productSaleReturnDetails');
Route::middleware('auth:api')->post('/product_sale_return_create', 'API\BackendController@productSaleReturnCreate');

// product sale exchange
Route::middleware('auth:api')->get('/product_sale_exchange_list', 'API\BackendController@productSaleExchangeList');
Route::middleware('auth:api')->post('/product_sale_exchange_details', 'API\BackendController@productSaleExchangeDetails');
Route::middleware('auth:api')->post('/product_sale_exchange_create', 'API\BackendController@productSaleExchangeCreate');
Route::middleware('auth:api')->post('/product_sale_exchange_edit', 'API\BackendController@productSaleExchangeEdit');
Route::middleware('auth:api')->post('/product_sale_exchange_delete', 'API\BackendController@productSaleExchangeDelete');

// warehouse product damages
Route::middleware('auth:api')->get('/warehouse_product_damage_list', 'API\BackendController@warehouseProductDamageList');
Route::middleware('auth:api')->post('/warehouse_product_damage_details', 'API\BackendController@warehouseProductDamageDetails');
Route::middleware('auth:api')->post('/warehouse_product_damage_create', 'API\BackendController@warehouseProductDamageCreate');
Route::middleware('auth:api')->post('/warehouse_product_damage_edit', 'API\BackendController@warehouseProductDamageEdit');
Route::middleware('auth:api')->post('/warehouse_product_damage_delete', 'API\BackendController@warehouseProductDamageDelete');

// payment
Route::middleware('auth:api')->get('/supplier_list', 'API\BackendController@supplierList');
Route::middleware('auth:api')->get('/customer_list', 'API\BackendController@customerList');
Route::middleware('auth:api')->get('/whole_sale_customer_list', 'API\BackendController@wholeSaleCustomerList');
Route::middleware('auth:api')->get('/pos_sale_customer_list', 'API\BackendController@posSaleCustomerList');
Route::middleware('auth:api')->get('/payment_paid_due_list', 'API\BackendController@paymentPaidDueList');
Route::middleware('auth:api')->post('/payment_paid_due_list_by_supplier', 'API\BackendController@paymentPaidDueListBySupplier');
Route::middleware('auth:api')->post('/payment_paid_due_create', 'API\BackendController@paymentPaidDueCreate');
Route::middleware('auth:api')->get('/payment_collection_due_list', 'API\BackendController@paymentCollectionDueList');
Route::middleware('auth:api')->post('/payment_collection_due_list_by_customer', 'API\BackendController@paymentCollectionDueListByCustomer');
Route::middleware('auth:api')->post('/payment_collection_due_create', 'API\BackendController@paymentCollectionDueCreate');
Route::middleware('auth:api')->get('/store_due_paid_list', 'API\BackendController@storeDuePaidList');
Route::middleware('auth:api')->post('/store_due_paid_list_by_store_date_difference', 'API\BackendController@storeDuePaidListByStoreDateDifference');


// transaction history
Route::middleware('auth:api')->post('/changed_password', 'API\BackendController@changedPassword');

// report
Route::middleware('auth:api')->post('/date_wise_sales_report', 'API\BackendController@dateWiseSalesReport');

// dashboard history
Route::middleware('auth:api')->get('/today_purchase', 'API\BackendController@todayPurchase');
Route::middleware('auth:api')->get('/total_purchase', 'API\BackendController@totalPurchase');
Route::middleware('auth:api')->get('/today_purchase_return', 'API\BackendController@todayPurchaseReturn');
Route::middleware('auth:api')->get('/total_purchase_return', 'API\BackendController@totalPurchaseReturn');
Route::middleware('auth:api')->get('/today_sale', 'API\BackendController@todaySale');
Route::middleware('auth:api')->get('/total_sale', 'API\BackendController@totalSale');
Route::middleware('auth:api')->get('/today_sale_return', 'API\BackendController@todaySaleReturn');
Route::middleware('auth:api')->get('/total_sale_return', 'API\BackendController@totalSaleReturn');
Route::middleware('auth:api')->get('/today_profit', 'API\BackendController@todayProfit');
Route::middleware('auth:api')->get('/total_profit', 'API\BackendController@totalProfit');


Route::middleware('auth:api')->post('/password_reset', 'API\BackendController@passwordEeset');



// stock_sync
Route::middleware('auth:api')->get('/stock_sync', 'API\BackendController@stock_sync');

// sslcommerz
Route::post('/checkout/ssl/pay', 'API\PublicSslCommerzPaymentController@index');
Route::POST('/success', 'API\PublicSslCommerzPaymentController@success');
Route::POST('/fail', 'API\PublicSslCommerzPaymentController@fail');
Route::POST('/cancel', 'API\PublicSslCommerzPaymentController@cancel');
Route::POST('/ipn', 'API\PublicSslCommerzPaymentController@ipn');

Route::get('/ssl/redirect/{status}','API\PublicSslCommerzPaymentController@status');




// start HRM + Accounting

// department
Route::middleware('auth:api')->get('/department_list', 'API\BackendController@departmentList');
Route::middleware('auth:api')->post('/department_create', 'API\BackendController@departmentCreate');
Route::middleware('auth:api')->post('/department_edit', 'API\BackendController@departmentEdit');
Route::middleware('auth:api')->post('/department_delete', 'API\BackendController@departmentDelete');

// designation
Route::middleware('auth:api')->get('/designation_list', 'API\BackendController@designationList');
Route::middleware('auth:api')->post('/designation_create', 'API\BackendController@designationCreate');
Route::middleware('auth:api')->post('/designation_edit', 'API\BackendController@designationEdit');
Route::middleware('auth:api')->post('/designation_delete', 'API\BackendController@designationDelete');

// holiday
Route::middleware('auth:api')->get('/holiday_list', 'API\BackendController@holidayList');
Route::middleware('auth:api')->post('/holiday_create', 'API\BackendController@holidayCreate');
Route::middleware('auth:api')->post('/holiday_edit', 'API\BackendController@holidayEdit');
Route::middleware('auth:api')->post('/holiday_delete', 'API\BackendController@holidayDelete');

// holiday
Route::middleware('auth:api')->get('/weekend_list', 'API\BackendController@weekendList');
Route::middleware('auth:api')->post('/weekend_create', 'API\BackendController@weekendCreate');
Route::middleware('auth:api')->post('/weekend_edit', 'API\BackendController@weekendEdit');
Route::middleware('auth:api')->post('/weekend_delete', 'API\BackendController@weekendDelete');

// leave Category
Route::middleware('auth:api')->get('/leave_category_list', 'API\BackendController@leaveCategoryList');
Route::middleware('auth:api')->post('/leave_category_create', 'API\BackendController@leaveCategoryCreate');
Route::middleware('auth:api')->post('/leave_category_edit', 'API\BackendController@leaveCategoryEdit');
Route::middleware('auth:api')->post('/leave_category_delete', 'API\BackendController@leaveCategoryDelete');

// Employee
Route::middleware('auth:api')->get('/employee_list', 'API\BackendController@employeeList');
Route::middleware('auth:api')->post('/employee_create', 'API\BackendController@employeeCreate');
Route::middleware('auth:api')->post('/employee_edit', 'API\BackendController@employeeEdit');
Route::middleware('auth:api')->post('/employee_delete', 'API\BackendController@employeeDelete');
Route::middleware('auth:api')->post('/employee_image', 'API\BackendController@employeeImage');

// Employee Office Information
Route::middleware('auth:api')->get('/employee_office_information_list', 'API\BackendController@employeeOfficeInformationList');
Route::middleware('auth:api')->post('/employee_office_information_create', 'API\BackendController@employeeOfficeInformationCreate');
Route::middleware('auth:api')->post('/employee_office_information_edit', 'API\BackendController@employeeOfficeInformationEdit');
Route::middleware('auth:api')->post('/employee_office_information_delete', 'API\BackendController@employeeOfficeInformationDelete');

// Employee Salary Information
Route::middleware('auth:api')->get('/employee_salary_information_list', 'API\BackendController@employeeSalaryInformationList');
Route::middleware('auth:api')->post('/employee_salary_information_create', 'API\BackendController@employeeSalaryInformationCreate');
Route::middleware('auth:api')->post('/employee_salary_information_edit', 'API\BackendController@employeeSalaryInformationEdit');
Route::middleware('auth:api')->post('/employee_salary_information_delete', 'API\BackendController@employeeSalaryInformationDelete');


// Leave Application
Route::middleware('auth:api')->get('/leave_application_list', 'API\BackendController@leaveApplicationList');
Route::middleware('auth:api')->post('/leave_application_create', 'API\BackendController@leaveApplicationCreate');
Route::middleware('auth:api')->post('/leave_application_edit', 'API\BackendController@leaveApplicationEdit');
Route::middleware('auth:api')->post('/leave_application_delete', 'API\BackendController@leaveApplicationDelete');

// Attendance Log
Route::middleware('auth:api')->get('/attendance_list', 'API\BackendController@attendanceList');
Route::middleware('auth:api')->post('/attendance_create', 'API\BackendController@attendanceCreate');
Route::middleware('auth:api')->post('/attendance_edit', 'API\BackendController@attendanceEdit');
//Route::middleware('auth:api')->post('/attendance_delete', 'API\BackendController@attendanceDelete');
Route::middleware('auth:api')->post('/attendance_report', 'API\BackendController@attendanceReport');

// payroll/salary sheet
Route::middleware('auth:api')->post('/total_absent_by_employee', 'API\BackendController@totalAbsentByEmployee');
Route::middleware('auth:api')->post('/total_late_by_employee', 'API\BackendController@totalLateByEmployee');
Route::middleware('auth:api')->post('/total_working_day', 'API\BackendController@totalWorkingDay');
//Route::middleware('auth:api')->post('/employee_details_department_wise', 'API\BackendController@employeeDetailsDepartmentWise');
Route::middleware('auth:api')->post('/employee_details_employee_wise', 'API\BackendController@employeeDetailsEmployeeWise');
Route::middleware('auth:api')->post('/payroll_create', 'API\BackendController@payrollCreate');
Route::middleware('auth:api')->post('/payroll_edit', 'API\BackendController@payrollEdit');
Route::middleware('auth:api')->get('/payroll_list', 'API\BackendController@payrollList');

// payslip
Route::middleware('auth:api')->post('/payslip_create', 'API\BackendController@payslipCreate');
Route::middleware('auth:api')->get('/payslip_list', 'API\BackendController@payslipList');


// accounts

// expense category
Route::middleware('auth:api')->get('/expense_category_list', 'API\BackendController@expenseCategoryList');
Route::middleware('auth:api')->post('/expense_category_create', 'API\BackendController@expenseCategoryCreate');
Route::middleware('auth:api')->post('/expense_category_edit', 'API\BackendController@expenseCategoryEdit');
Route::middleware('auth:api')->post('/expense_category_delete', 'API\BackendController@expenseCategoryDelete');

// shop expense
Route::middleware('auth:api')->get('/store_expense_list', 'API\BackendController@storeExpenseList');
Route::middleware('auth:api')->post('/store_expense_create', 'API\BackendController@storeExpenseCreate');
Route::middleware('auth:api')->post('/store_expense_edit', 'API\BackendController@storeExpenseEdit');
Route::middleware('auth:api')->post('/store_expense_delete', 'API\BackendController@storeExpenseDelete');

// voucher type
Route::middleware('auth:api')->get('/tangible_asset_list', 'API\BackendController@tangibleAssetList');
Route::middleware('auth:api')->post('/tangible_asset_create', 'API\BackendController@tangibleAssetCreate');
Route::middleware('auth:api')->post('/tangible_asset_edit', 'API\BackendController@tangibleAssetEdit');
Route::middleware('auth:api')->post('/tangible_asset_delete', 'API\BackendController@tangibleAssetDelete');

// voucher type
Route::middleware('auth:api')->get('/voucher_type_list', 'API\BackendController@voucherTypeList');
Route::middleware('auth:api')->post('/voucher_type_create', 'API\BackendController@voucherTypeCreate');
Route::middleware('auth:api')->post('/voucher_type_edit', 'API\BackendController@voucherTypeEdit');
Route::middleware('auth:api')->post('/voucher_type_delete', 'API\BackendController@voucherTypeDelete');

// chart of account
Route::middleware('auth:api')->get('/chart_of_account_list', 'API\BackendController@chartOfAccountList');
Route::middleware('auth:api')->post('/chart_of_account_list_by_head_name', 'API\BackendController@chartOfAccountListByName');
//Route::middleware('auth:api')->get('/chart_of_account_recursive_list', 'API\BackendController@chartOfAccountRecursiveList');
Route::middleware('auth:api')->get('/chart_of_account_active_list', 'API\BackendController@chartOfAccountActiveList');
Route::middleware('auth:api')->get('/chart_of_account_is_transaction_list', 'API\BackendController@chartOfAccountIsTransactionList');
Route::middleware('auth:api')->get('/chart_of_account_is_general_ledger_list', 'API\BackendController@chartOfAccountIsGeneralLedgerList');
Route::middleware('auth:api')->post('/chart_of_account_details', 'API\BackendController@chartOfAccountDetails');
Route::middleware('auth:api')->post('/chart_of_account_generate_head_code', 'API\BackendController@chartOfAccountGenerateHeadCode');
Route::middleware('auth:api')->post('/chart_of_account_parent_head_details', 'API\BackendController@chartOfAccountParentHeadDetails');
Route::middleware('auth:api')->post('/chart_of_account_create', 'API\BackendController@chartOfAccountCreate');
Route::middleware('auth:api')->post('/chart_of_account_edit', 'API\BackendController@chartOfAccountEdit');
Route::middleware('auth:api')->post('/chart_of_account_delete', 'API\BackendController@chartOfAccountDelete');

// chart of account transaction
Route::middleware('auth:api')->get('/chart_of_account_transaction_list', 'API\BackendController@chartOfAccountTransactionList');
Route::middleware('auth:api')->post('/chart_of_account_transaction_details', 'API\BackendController@chartOfAccountTransactionDetails');
Route::middleware('auth:api')->post('/chart_of_account_transaction_create', 'API\BackendController@chartOfAccountTransactionCreate');
Route::middleware('auth:api')->post('/chart_of_account_transaction_edit', 'API\BackendController@chartOfAccountTransactionEdit');
Route::middleware('auth:api')->post('/chart_of_account_transaction_delete', 'API\BackendController@chartOfAccountTransactionDelete');

// ledger
Route::middleware('auth:api')->post('/ledger', 'API\BackendController@ledger');
Route::middleware('auth:api')->post('/balance_sheet', 'API\BackendController@balanceSheet');

// database backup
Route::get('/backup_database', 'HomeController@backup_database')->name('backup_database');
//Route::middleware('auth:api')->get('/backup_database', 'API\BackendController@backupDatabase');
//Route::middleware('auth:api')->get('/sum_sub_total', 'API\BackendController@sum_sub_total');
