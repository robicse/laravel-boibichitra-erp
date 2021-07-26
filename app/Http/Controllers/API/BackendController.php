<?php

namespace App\Http\Controllers\API;

use App\Attendance;
use App\ChartOfAccount;
use App\ChartOfAccountTransaction;
use App\ChartOfAccountTransactionDetail;
use App\Department;
use App\Designation;
use App\Employee;
use App\EmployeeOfficeInformation;
use App\EmployeeSalaryInformation;
use App\ExpenseCategory;
use App\Helpers\UserInfo;
use App\Holiday;
use App\Http\Controllers\Controller;
use App\LeaveApplication;
use App\LeaveCategory;
use App\Party;
use App\PaymentCollection;
use App\PaymentPaid;
use App\Payroll;
use App\Payslip;
use App\Product;
use App\ProductBrand;
use App\ProductPurchase;
use App\ProductPurchaseDetail;
use App\ProductPurchaseReturn;
use App\ProductPurchaseReturnDetail;
use App\ProductSale;
use App\ProductSaleDetail;
use App\ProductSaleExchange;
use App\ProductSaleExchangeDetail;
use App\ProductSalePreviousDetail;
use App\ProductSaleReturn;
use App\ProductSaleReturnDetail;
use App\ProductUnit;
use App\ProductVat;
use App\Stock;
use App\StockTransfer;
use App\StockTransferDetail;
use App\StockTransferRequest;
use App\StockTransferRequestDetail;
use App\Store;
use App\StoreExpense;
use App\StoreStockReturn;
use App\StoreStockReturnDetail;
use App\TangibleAssets;
use App\Transaction;
use App\User;
use App\VoucherType;
use App\Warehouse;
use App\WarehouseCurrentStock;
use App\WarehouseProductDamage;
use App\WarehouseProductDamageDetail;
use App\WarehouseStoreCurrentStock;
use App\Weekend;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class BackendController extends Controller
{
    public $successStatus = 200;
    public $authStatus = 401;
    public $failStatus = 402;
    public $ExistsStatus = 403;
    public $validationStatus = 404;

    public function test()
    {
        //return 'test';
        return response()->json(['success'=>true,'response' => 'Test Action Api!'], $this->successStatus);
    }

    // delivery service
//    public function deliveryServiceList(){
//        $delivery_services = DB::table('delivery_services')->select('id','name','status')->get();
//
//        if($delivery_services)
//        {
//            $success['delivery_services'] =  $delivery_services;
//            return response()->json(['success'=>true,'response' => $success], $this->successStatus);
//        }else{
//            return response()->json(['success'=>false,'response'=>'No Delivery Services List Found!'], $this->failStatus);
//        }
//    }
//
//
//    public function deliveryServiceCreate(Request $request){
//
//        $validator = Validator::make($request->all(), [
//            'name' => 'required|unique:delivery_services,name',
//            'status'=> 'required',
//        ]);
//
//        if ($validator->fails()) {
//            $response = [
//                'success' => false,
//                'data' => 'Validation Error.',
//                'message' => $validator->errors()
//            ];
//
//            return response()->json($response, $this-> validationStatus);
//        }
//
//
//        $deliveryService = new deliveryService();
//        $deliveryService->name = $request->name;
//        $deliveryService->status = $request->status;
//        $deliveryService->save();
//        $insert_id = $deliveryService->id;
//
//        if($insert_id){
//            return response()->json(['success'=>true,'response' => $deliveryService], $this->successStatus);
//        }else{
//            return response()->json(['success'=>false,'response'=>'Delivery Service Not Created Successfully!'], $this->failStatus);
//        }
//    }
//
//    public function deliveryServiceEdit(Request $request){
//
//        $validator = Validator::make($request->all(), [
//            'delivery_service_id'=> 'required',
//            'name' => 'required|unique:delivery_services,name,'.$request->delivery_service_id,
//            'status'=> 'required',
//        ]);
//
//        if ($validator->fails()) {
//            $response = [
//                'success' => false,
//                'data' => 'Validation Error.',
//                'message' => $validator->errors()
//            ];
//
//            return response()->json($response, $this->validationStatus);
//        }
//
//        $check_exists_delivery_service = DB::table("delivery_services")->where('id',$request->delivery_service_id)->pluck('id')->first();
//        if($check_exists_delivery_service == null){
//            return response()->json(['success'=>false,'response'=>'No Delivery Service Found!'], $this->failStatus);
//        }
//
//        $deliveryService = deliveryService::find($request->delivery_service_id);
//        $deliveryService->name = $request->name;
//        $deliveryService->status = $request->status;
//        $update_delivery_service = $deliveryService->save();
//
//        if($update_delivery_service){
//            return response()->json(['success'=>true,'response' => $deliveryService], $this->successStatus);
//        }else{
//            return response()->json(['success'=>false,'response'=>'Delivery Service Not Created Successfully!'], $this->failStatus);
//        }
//    }
//
//    public function deliveryServiceDelete(Request $request){
//        $check_exists_delivery_service = DB::table("delivery_services")->where('id',$request->delivery_service_id)->pluck('id')->first();
//        if($check_exists_delivery_service == null){
//            return response()->json(['success'=>false,'response'=>'No Delivery Service Found!'], $this->failStatus);
//        }
//
//        //$delete_party = DB::table("product_brands")->where('id',$request->product_brand_id)->delete();
//        $soft_delete_delivery_service = deliveryService::find($request->delivery_service_id);
//        $soft_delete_delivery_service->status=0;
//        $affected_row = $soft_delete_delivery_service->update();
//        if($affected_row)
//        {
//            return response()->json(['success'=>true,'response' => 'Delivery Service Successfully Soft Deleted!'], $this->successStatus);
//        }else{
//            return response()->json(['success'=>false,'response'=>'No Product Brand Deleted!'], $this->failStatus);
//        }
//    }



    public function supplierList(){
        $supplier_lists = DB::table('parties')
            ->where('type','supplier')
            ->select('id','name')
            ->orderBy('id','desc')
            ->get();

        if(count($supplier_lists) > 0)
        {
            $success['supplier_lists'] =  $supplier_lists;
            return response()->json(['success'=>true,'response' => $success], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Supplier List Found!'], $this->failStatus);
        }
    }

    public function customerList(){
        $customer_lists = DB::table('parties')
            ->where('type','customer')
            ->select('id','name')
            ->orderBy('id','desc')
            ->get();

        if(count($customer_lists) > 0)
        {
            $success['customer_lists'] =  $customer_lists;
            return response()->json(['success'=>true,'response' => $success], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Customer List Found!'], $this->failStatus);
        }
    }

    public function wholeSaleCustomerList(){
        $customer_lists = DB::table('parties')
            ->where('type','customer')
            ->where('customer_type','Whole Sale')
            ->select('id','name','phone','address')
            ->orderBy('id','desc')
            ->get();

        if(count($customer_lists) > 0)
        {
            $success['customer_lists'] =  $customer_lists;
            return response()->json(['success'=>true,'response' => $success], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Customer List Found!'], $this->failStatus);
        }
    }

    public function posSaleCustomerList(){
        $customer_lists = DB::table('parties')
            ->where('type','customer')
            ->where('customer_type','POS Sale')
            ->select('id','name','phone','address')
            ->orderBy('id','desc')
            ->get();

        if(count($customer_lists) > 0)
        {
            $success['customer_lists'] =  $customer_lists;
            return response()->json(['success'=>true,'response' => $success], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Customer List Found!'], $this->failStatus);
        }
    }

    public function paymentPaidDueList(){
        $payment_paid_due_amount = DB::table('product_purchases')
            ->leftJoin('users','product_purchases.user_id','users.id')
            ->leftJoin('parties','product_purchases.party_id','parties.id')
            ->leftJoin('warehouses','product_purchases.warehouse_id','warehouses.id')
            ->where('product_purchases.due_amount','>',0)
            ->select('product_purchases.id','product_purchases.invoice_no','product_purchases.discount_type','product_purchases.discount_amount','product_purchases.total_amount','product_purchases.paid_amount','product_purchases.due_amount','product_purchases.purchase_date_time','users.name as user_name','parties.id as supplier_id','parties.name as supplier_name','warehouses.id as warehouse_id','warehouses.name as warehouse_name')
            ->orderBy('product_purchases.id','desc')
            ->paginate(12);

        if($payment_paid_due_amount)
        {
            $total_payment_paid_due_amount = 0;
            $sum_payment_paid_due_amount = DB::table('product_purchases')
                ->where('product_purchases.due_amount','>',0)
                ->select(DB::raw('SUM(due_amount) as total_payment_paid_due_amount'))
                ->first();
            if($sum_payment_paid_due_amount){
                $total_payment_paid_due_amount = $sum_payment_paid_due_amount->total_payment_paid_due_amount;
            }

            $success['payment_paid_due_amount'] =  $payment_paid_due_amount;
            return response()->json(['success'=>true,'response' => $success,'total_payment_paid_due_amount'=>$total_payment_paid_due_amount], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Payment Due List Found!'], $this->failStatus);
        }
    }

    public function paymentPaidDueListBySupplier(Request $request){
        $payment_paid_due_amount = DB::table('product_purchases')
            ->leftJoin('users','product_purchases.user_id','users.id')
            ->leftJoin('parties','product_purchases.party_id','parties.id')
            ->leftJoin('warehouses','product_purchases.warehouse_id','warehouses.id')
            ->where('product_purchases.due_amount','>',0)
            ->where('product_purchases.party_id',$request->supplier_id)
            ->select('product_purchases.id','product_purchases.invoice_no','product_purchases.discount_type','product_purchases.discount_amount','product_purchases.total_amount','product_purchases.paid_amount','product_purchases.due_amount','product_purchases.purchase_date_time','users.name as user_name','parties.id as supplier_id','parties.name as supplier_name','warehouses.id as warehouse_id','warehouses.name as warehouse_name')
            ->paginate(12);


        if($payment_paid_due_amount)
        {
            $total_payment_paid_due_amount = 0;
            $sum_payment_paid_due_amount = DB::table('product_purchases')
                ->where('product_purchases.due_amount','>',0)
                ->where('product_purchases.party_id',$request->supplier_id)
                ->select(DB::raw('SUM(due_amount) as total_payment_paid_due_amount'))
                ->first();
            if($sum_payment_paid_due_amount){
                $total_payment_paid_due_amount = $sum_payment_paid_due_amount->total_payment_paid_due_amount;
            }

            $success['payment_paid_due_amount'] =  $payment_paid_due_amount;
            return response()->json(['success'=>true,'response' => $success,'total_payment_paid_due_amount'=>$total_payment_paid_due_amount], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Payment Due List Found!'], $this->failStatus);
        }
    }

    public function paymentPaidDueCreate(Request $request){
        //dd($request->all());
        $this->validate($request, [
            'supplier_id'=> 'required',
            'warehouse_id'=> 'required',
            'paid_amount'=> 'required',
            'new_paid_amount'=> 'required',
            'due_amount'=> 'required',
            //'total_amount'=> 'required',
            'payment_type'=> 'required',
            'invoice_no'=> 'required',
        ]);

        $date = date('Y-m-d');
        $date_time = date('Y-m-d h:i:s');

        $user_id = Auth::user()->id;

        // product purchase
        $productPurchase = ProductPurchase::where('invoice_no',$request->invoice_no)->first();
        $productPurchase->paid_amount = $request->paid_amount;
        $productPurchase->due_amount = $request->due_amount;
        //$productPurchase ->total_amount = $request->total_amount;
        $affectedRow = $productPurchase->save();

        if($affectedRow) {
            // transaction
            $transaction = new Transaction();
            $transaction->ref_id = $productPurchase->id;
            $transaction->invoice_no = $request->invoice_no;
            $transaction->user_id = $user_id;
            $transaction->warehouse_id = $request->warehouse_id;
            $transaction->party_id = $request->supplier_id;
            $transaction->transaction_type = 'payment_paid';
            $transaction->payment_type = $request->payment_type;
            $transaction->amount = $request->new_paid_amount;
            $transaction->transaction_date = $date;
            $transaction->transaction_date_time = $date_time;
            $transaction->save();
            $transaction_id = $transaction->id;

            // payment paid
            $previous_current_paid_amount = PaymentPaid::where('invoice_no',$request->invoice_no)->latest()->pluck('current_paid_amount')->first();
            $payment_paid = new PaymentPaid();
            $payment_paid->invoice_no = $request->invoice_no;
            $payment_paid->product_purchase_id = $productPurchase->id;
            $payment_paid->product_purchase_return_id = NULL;
            $payment_paid->user_id = $user_id;
            $payment_paid->party_id = $request->supplier_id;
            $payment_paid->paid_type = 'Purchase';
            $payment_paid->paid_amount = $request->new_paid_amount;
            $payment_paid->due_amount = $request->due_amount;
            $payment_paid->current_paid_amount = $previous_current_paid_amount + $request->new_paid_amount;
            $payment_paid->paid_date = $date;
            $payment_paid->paid_date_time = $date_time;
            $payment_paid->save();


            if($request->payment_type == 'SSL Commerz'){
                return response()->json(['success'=>true,'transaction_id' => $transaction_id,'payment_type' => $request->payment_type], $this->successStatus);
            }else{
                return response()->json(['success'=>true,'response' => 'Inserted Successfully.'], $this->successStatus);
            }
        }else{
            return response()->json(['success'=>false,'response'=>'No Inserted Successfully!'], $this->failStatus);
        }
    }

    public function paymentCollectionDueCreate(Request $request){
        //dd($request->all());
        $this->validate($request, [
            'customer_id'=> 'required',
            'store_id'=> 'required',
            'paid_amount'=> 'required',
            'new_paid_amount'=> 'required',
            'due_amount'=> 'required',
            //'total_amount'=> 'required',
            'payment_type'=> 'required',
            'invoice_no'=> 'required',
        ]);

        $date = date('Y-m-d');
        $date_time = date('Y-m-d h:i:s');

        $user_id = Auth::user()->id;
        $store_id = $request->store_id;
        $warehouse_id = Store::where('id',$store_id)->latest('id')->pluck('warehouse_id')->first();

        // product sale return
        $productSale = ProductSale::where('invoice_no',$request->invoice_no)->first();
        $productSale ->paid_amount = $request->paid_amount;
        $productSale ->due_amount = $request->due_amount;
        //$productSale ->total_amount = $request->total_amount;
        $affectedRow = $productSale->save();

        if($affectedRow)
        {

            // transaction
            $transaction = new Transaction();
            $transaction->ref_id = $productSale->id;
            $transaction->invoice_no = $request->invoice_no;
            $transaction->user_id = $user_id;
            $transaction->warehouse_id = $warehouse_id;
            $transaction->store_id = $store_id;
            $transaction->party_id = $request->customer_id;
            $transaction->transaction_type = 'payment_collection';
            $transaction->payment_type = $request->payment_type;
            $transaction->amount = $request->new_paid_amount;
            $transaction->transaction_date = $date;
            $transaction->transaction_date_time = $date_time;
            $transaction->save();
            $transaction_id = $transaction->id;

            // payment paid
            $previous_current_collection_amount = PaymentCollection::where('invoice_no',$request->invoice_no)->latest()->pluck('current_collection_amount')->first();
            $payment_collection = new PaymentCollection();
            $payment_collection->invoice_no = $request->invoice_no;
            $payment_collection->product_sale_id = $productSale->id;
            $payment_collection->product_sale_return_id = NULL;
            $payment_collection->user_id = $user_id;
            $payment_collection->party_id = $request->customer_id;
            $payment_collection->warehouse_id = $request->warehouse_id;
            $payment_collection->store_id = $request->store_id;
            $payment_collection->collection_type = 'Sale';
            $payment_collection->collection_amount = $request->new_paid_amount;
            $payment_collection->due_amount = $productSale->due_amount;
            $payment_collection->current_collection_amount = $previous_current_collection_amount + $request->new_paid_amount;
            $payment_collection->collection_date = $date;
            $payment_collection->collection_date_time = $date_time;
            $payment_collection->save();

            if($request->payment_type == 'SSL Commerz'){
                return response()->json(['success'=>true,'transaction_id' => $transaction_id,'payment_type' => $request->payment_type], $this->successStatus);
            }else{
                return response()->json(['success'=>true,'response' => 'Inserted Successfully.'], $this->successStatus);
            }
        }else{
            return response()->json(['success'=>false,'response'=>'No Inserted Successfully!'], $this->failStatus);
        }
    }

    public function paymentCollectionDueList(){
        $payment_collection_due_list = DB::table('product_sales')
            ->leftJoin('users','product_sales.user_id','users.id')
            ->leftJoin('parties','product_sales.party_id','parties.id')
            ->leftJoin('warehouses','product_sales.warehouse_id','warehouses.id')
            ->leftJoin('stores','product_sales.store_id','stores.id')
            ->where('product_sales.due_amount','>',0)
            ->select('product_sales.id','product_sales.invoice_no','product_sales.discount_type','product_sales.discount_amount','product_sales.total_amount','product_sales.paid_amount','product_sales.due_amount','product_sales.sale_date_time','users.name as user_name','parties.id as customer_id','parties.name as customer_name','warehouses.id as warehouse_id','warehouses.name as warehouse_name','stores.id as store_id','stores.name as store_name')
            ->orderBy('product_sales.id','desc')
            ->paginate(12);

        if($payment_collection_due_list)
        {

            $total_payment_collection_due_amount = 0;
            $sum_payment_collection_due_amount = DB::table('product_sales')
                ->where('product_sales.due_amount','>',0)
                ->select(DB::raw('SUM(due_amount) as total_payment_collection_due_amount'))
                ->first();
            if($sum_payment_collection_due_amount){
                $total_payment_collection_due_amount = $sum_payment_collection_due_amount->total_payment_collection_due_amount;
            }

            $success['payment_collection_due_list'] =  $payment_collection_due_list;

            return response()->json(['success'=>true,'response' => $success,'total_payment_collection_due_amount'=>$total_payment_collection_due_amount], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Payment Collection Due List Found!'], $this->failStatus);
        }
    }

    public function paymentCollectionDueListByCustomer(Request $request){
        $payment_collection_due_list = DB::table('product_sales')
            ->leftJoin('users','product_sales.user_id','users.id')
            ->leftJoin('parties','product_sales.party_id','parties.id')
            ->leftJoin('warehouses','product_sales.warehouse_id','warehouses.id')
            ->leftJoin('stores','product_sales.store_id','stores.id')
            ->where('product_sales.due_amount','>',0)
            ->where('product_sales.party_id',$request->customer_id)
            ->select('product_sales.id','product_sales.invoice_no','product_sales.discount_type','product_sales.discount_amount','product_sales.total_amount','product_sales.paid_amount','product_sales.due_amount','product_sales.sale_date_time','users.name as user_name','parties.id as customer_id','parties.name as customer_name','warehouses.id as warehouse_id','warehouses.name as warehouse_name','stores.id as store_id','stores.name as store_name')
            ->paginate(12);

        if($payment_collection_due_list)
        {

            $total_payment_collection_due_amount = 0;
            $sum_payment_collection_due_amount = DB::table('product_sales')
                ->where('product_sales.due_amount','>',0)
                ->where('product_sales.party_id',$request->customer_id)
                ->select(DB::raw('SUM(due_amount) as total_payment_collection_due_amount'))
                ->first();
            if($sum_payment_collection_due_amount){
                $total_payment_collection_due_amount = $sum_payment_collection_due_amount->total_payment_collection_due_amount;
            }

            $success['payment_collection_due_list'] =  $payment_collection_due_list;

            return response()->json(['success'=>true,'response' => $success,'total_payment_collection_due_amount'=>$total_payment_collection_due_amount], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Payment Collection Due List Found!'], $this->failStatus);
        }
    }

    public function storeDuePaidList(){
        $store_due_paid_amount = DB::table('stock_transfers')
            ->select('id','invoice_no','issue_date','total_vat_amount','total_amount','paid_amount','due_amount')
            ->paginate(12);

        if($store_due_paid_amount)
        {
            $success['store_due_paid_amount'] =  $store_due_paid_amount;
            return response()->json(['success'=>true,'response' => $success], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Store Due Paid List Found!'], $this->failStatus);
        }
    }

    public function storeDuePaidListByStoreDateDifference(Request $request){
        $store_due_paid_amount = DB::table('stock_transfers')
            ->where('store_id',$request->store_id)
            ->where('issue_date','>=',$request->start_date)
            ->where('issue_date','<=',$request->end_date)
            ->select('id','invoice_no','issue_date','total_vat_amount','total_amount','paid_amount','due_amount')
            ->paginate(12);

        if($store_due_paid_amount)
        {
            $success['store_due_paid_amount'] =  $store_due_paid_amount;
            return response()->json(['success'=>true,'response' => $success], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Store Due Paid List Found!'], $this->failStatus);
        }
    }

//    public function storeToWarehouseStockRequestStockTransferCreate(Request $request){
//        $this->validate($request, [
//            'stock_transfer_request_id'=> 'required',
//            'store_id'=> 'required',
//            'warehouse_id'=> 'required',
//        ]);
//
//        $date = date('Y-m-d');
//        $date_time = date('Y-m-d h:i:s');
//
//        $user_id = Auth::user()->id;
//        $warehouse_id = $request->warehouse_id;
//        $store_id = $request->store_id;
//
//
//        $get_invoice_no = StockTransfer::latest()->pluck('invoice_no')->first();
//        if(!empty($get_invoice_no)){
//            $get_invoice = str_replace("Stock-transfer-","",$get_invoice_no);
//            $invoice_no = $get_invoice+1;
//        }else{
//            $invoice_no = 1;
//        }
//
//        $total_amount = 0;
//        $total_vat_amount = 0;
//        foreach ($request->products as $data) {
//            $product_id = $data['product_id'];
//            //$price = Product::where('id',$product_id)->pluck('purchase_price')->first();
//            $Product_info = Product::where('id',$product_id)->first();
//            $total_vat_amount += ($data['qty']*$Product_info->whole_sale_price);
//            //$total_amount += $Product_info->purchase_price;
//            $total_amount += ($data['qty']*$Product_info->whole_sale_price) + ($data['qty']*$Product_info->purchase_price);
//        }
//
//        // stock transfer request table update
//        $stock_transfer_request = StockTransferRequest::find($request->stock_transfer_request_id);
//        $stock_transfer_request->send_by_user_id=Auth::user()->id;
//        $stock_transfer_request->send_date=$date;
//        $stock_transfer_request->send_remarks=$request->send_remarks;
//        $stock_transfer_request->send_status='Delivered';
//        $stock_transfer_request->save();
//
//
//        // stock transfer
//        $final_invoice = 'Stock-transfer-'.$invoice_no;
//        $stock_transfer = new StockTransfer();
//        $stock_transfer->invoice_no=$final_invoice;
//        $stock_transfer->user_id=Auth::user()->id;
//        $stock_transfer->warehouse_id = $warehouse_id;
//        $stock_transfer->store_id = $store_id;
//        $stock_transfer->total_vat_amount = $total_vat_amount;
//        //$stock_transfer->total_amount = $total_amount;
//        $stock_transfer->paid_amount = 0;
//        $stock_transfer->due_amount = $total_amount;
//        $stock_transfer->issue_date = $date;
//        $stock_transfer->due_date = $date;
//        $stock_transfer->save();
//        $stock_transfer_insert_id = $stock_transfer->id;
//
//        $insert_id = false;
//
//        foreach ($request->products as $data) {
//
//            $product_id = $data['product_id'];
//            $product_info = Product::where('id',$product_id)->first();
//
//
//            // stock transfer request details
//            $stock_transfer_request_detail = StockTransferRequestDetail::find($request->stock_transfer_request_detail_id);
//            $stock_transfer_request_detail->send_qty = $data['send_qty'];
//            $stock_transfer_request_detail->save();
//
//
//            // stock transfer details
//            $stock_transfer_detail = new StockTransferDetail();
//            $stock_transfer_detail->stock_transfer_id = $stock_transfer_insert_id;
//            $stock_transfer_detail->product_unit_id = $data['product_unit_id'];
//            $stock_transfer_detail->product_brand_id = $data['product_brand_id'] ? $data['product_brand_id'] : NULL;
//            $stock_transfer_detail->product_id = $product_id;
//            $stock_transfer_detail->barcode = $product_info->barcode;
//            $stock_transfer_detail->qty = $data['qty'];
//            $stock_transfer_detail->vat_amount = $data['qty']*$product_info->whole_sale_price;
//            $stock_transfer_detail->price = $product_info->purchase_price;
//            $stock_transfer_detail->sub_total = ($data['qty']*$product_info->whole_sale_price) + ($data['qty']*$product_info->purchase_price);
//            $stock_transfer_detail->issue_date = $date;
//            $stock_transfer_detail->save();
//
//
//            $check_previous_warehouse_current_stock = Stock::where('warehouse_id',$warehouse_id)
//                ->where('product_id',$product_id)
//                ->where('stock_where','warehouse')
//                ->latest('id','desc')
//                ->pluck('current_stock')
//                ->first();
//
//            if($check_previous_warehouse_current_stock){
//                $previous_warehouse_current_stock = $check_previous_warehouse_current_stock;
//            }else{
//                $previous_warehouse_current_stock = 0;
//            }
//
//            // stock out warehouse product
//            $stock = new Stock();
//            $stock->ref_id = $stock_transfer_insert_id;
//            $stock->user_id = $user_id;
//            $stock->warehouse_id = $warehouse_id;
//            $stock->store_id = NULL;
//            $stock->product_id = $product_id;
//            $stock->product_unit_id = $data['product_unit_id'];
//            $stock->product_brand_id = $data['product_brand_id'] ? $data['product_brand_id'] : NULL;
//            $stock->stock_type = 'from_warehouse_to_store';
//            $stock->stock_where = 'warehouse';
//            $stock->stock_in_out = 'stock_out';
//            $stock->previous_stock = $previous_warehouse_current_stock;
//            $stock->stock_in = 0;
//            $stock->stock_out = $data['qty'];
//            $stock->current_stock = $previous_warehouse_current_stock - $data['qty'];
//            $stock->stock_date = $date;
//            $stock->stock_date_time = $date_time;
//            $stock->save();
//
//
//            $check_previous_store_current_stock = Stock::where('warehouse_id',$warehouse_id)
//                ->where('store_id',$store_id)
//                ->where('product_id',$product_id)
//                ->where('stock_where','store')
//                ->latest('id','desc')
//                ->pluck('current_stock')
//                ->first();
//
//            if($check_previous_store_current_stock){
//                $previous_store_current_stock = $check_previous_store_current_stock;
//            }else{
//                $previous_store_current_stock = 0;
//            }
//
//            // stock in store product
//            $stock = new Stock();
//            $stock->ref_id = $stock_transfer_insert_id;
//            $stock->user_id = $user_id;
//            $stock->warehouse_id = $warehouse_id;
//            $stock->store_id = $store_id;
//            $stock->product_id = $product_id;
//            $stock->product_unit_id = $data['product_unit_id'];
//            $stock->product_brand_id = $data['product_brand_id'] ? $data['product_brand_id'] : NULL;
//            $stock->stock_type = 'from_warehouse_to_store';
//            $stock->stock_where = 'store';
//            $stock->stock_in_out = 'stock_in';
//            $stock->previous_stock = $previous_store_current_stock;
//            $stock->stock_in = $data['qty'];
//            $stock->stock_out = 0;
//            $stock->current_stock = $previous_store_current_stock + $data['qty'];
//            $stock->stock_date = $date;
//            $stock->stock_date_time = $date_time;
//            $stock->save();
//            $insert_id = $stock->id;
//        }


//
//
//
//        if($insert_id){
//            return response()->json(['success'=>true,'response' => 'Warehouse To Store Stock Successfully Inserted.'], $this->successStatus);
//        }else{
//            return response()->json(['success'=>false,'response'=>'No Warehouse To Store Stock Successfully Inserted.!'], $this->failStatus);
//        }
//    }




//    public function storeStockList(Request $request){
//        $store_stock_list = DB::table('stocks')
//            ->leftJoin('users','stocks.user_id','users.id')
//            ->leftJoin('warehouses','stocks.warehouse_id','warehouses.id')
//            ->leftJoin('product_units','stocks.product_unit_id','product_units.id')
//            ->leftJoin('product_brands','stocks.product_brand_id','product_brands.id')
//            ->leftJoin('products','stocks.product_id','products.id')
//            ->where('stocks.stock_where','store')
//            ->where('stocks.store_id',$request->store_id)
//            ->select('stocks.id as stock_id','users.name as stock_by_user','warehouses.name as warehouse_name','product_units.name as product_unit_name','product_brands.name as product_brand_name','products.name as product_name','stocks.stock_type','stocks.stock_where','stocks.stock_in_out','stocks.previous_stock','stocks.stock_in','stocks.stock_out','stocks.current_stock','stocks.stock_date','stocks.stock_date_time')
//            ->latest('stocks.id','desc')
//            ->get();
//
//        if($store_stock_list)
//        {
//            $success['store_stock_list'] =  $store_stock_list;
//            return response()->json(['success'=>true,'response' => $success], $this->successStatus);
//        }else{
//            return response()->json(['success'=>false,'response'=>'No Store Stock List Found!'], $this->failStatus);
//        }
//    }

//    public function storeStockLowList(){
//
//        $store_stock_low_list = DB::table('stocks')
//            ->leftJoin('users','stocks.user_id','users.id')
//            ->leftJoin('warehouses','stocks.warehouse_id','warehouses.id')
//            ->leftJoin('product_units','stocks.product_unit_id','product_units.id')
//            ->leftJoin('product_brands','stocks.product_brand_id','product_brands.id')
//            ->leftJoin('products','stocks.product_id','products.id')
//            ->where('stocks.stock_where','store')
//            //->where('stocks.current_stock','<',2)
//            ->whereIn('stocks.id', function($query) {
//                $query->from('stocks')->where('current_stock','<', 2)->groupBy('product_id')->selectRaw('MAX(id)');
//            })
//            ->select('stocks.id as stock_id','users.name as stock_by_user','warehouses.name as warehouse_name','product_units.name as product_unit_name','product_brands.name as product_brand_name','products.id as product_id','products.name as product_name','stocks.stock_type','stocks.stock_where','stocks.previous_stock','stocks.stock_in','stocks.stock_out','stocks.current_stock','stocks.stock_date','stocks.stock_date_time')
//            ->latest('stocks.id','desc')
//            ->get();
//
//        if($store_stock_low_list)
//        {
//            $success['store_stock_low_list'] =  $store_stock_low_list;
//            return response()->json(['success'=>true,'response' => $success], $this->successStatus);
//        }else{
//            return response()->json(['success'=>false,'response'=>'No Store Stock Low List Found!'], $this->failStatus);
//        }
//    }

//    public function storeCurrentStockList(Request $request){
//        $store_stock_product_list = Stock::where('store_id',$request->store_id)
//            ->select('product_id')
//            ->groupBy('product_id')
//            ->latest('id')
//            ->get();
//
//        $store_stock_product = [];
//        foreach($store_stock_product_list as $data){
//
//            $stock_row = DB::table('stocks')
//                ->join('warehouses','stocks.warehouse_id','warehouses.id')
//                ->leftJoin('products','stocks.product_id','products.id')
//                ->leftJoin('product_units','stocks.product_unit_id','product_units.id')
//                ->leftJoin('product_brands','stocks.product_brand_id','product_brands.id')
//                ->where('stocks.stock_where','store')
//                ->where('stocks.product_id',$data->product_id)
//                ->where('stocks.store_id',$request->store_id)
//                ->select('stocks.*','warehouses.name as warehouse_name','products.name as product_name','products.purchase_price','products.whole_sale_price','products.selling_price','products.item_code','products.barcode','products.image','products.vat_status','products.vat_percentage','products.vat_amount','products.vat_whole_amount','product_units.name as product_unit_name','product_brands.name as product_brand_name')
//                ->orderBy('stocks.id','desc')
//                ->first();
//
//            if($stock_row){
//                $nested_data['stock_id'] = $stock_row->id;
//                $nested_data['warehouse_id'] = $stock_row->warehouse_id;
//                $nested_data['warehouse_name'] = $stock_row->warehouse_name;
//                $nested_data['product_id'] = $stock_row->product_id;
//                $nested_data['product_name'] = $stock_row->product_name;
//                $nested_data['purchase_price'] = $stock_row->purchase_price;
//                $nested_data['whole_sale_price'] = $stock_row->whole_sale_price;
//                $nested_data['selling_price'] = $stock_row->selling_price;
//                $nested_data['vat_status'] = $stock_row->vat_status;
//                $nested_data['vat_percentage'] = $stock_row->vat_percentage;
//                $nested_data['vat_amount'] = $stock_row->vat_amount;
//                $nested_data['vat_whole_amount'] = $stock_row->vat_whole_amount;
//                $nested_data['item_code'] = $stock_row->item_code;
//                $nested_data['barcode'] = $stock_row->barcode;
//                $nested_data['image'] = $stock_row->image;
//                $nested_data['product_unit_id'] = $stock_row->product_unit_id;
//                $nested_data['product_unit_name'] = $stock_row->product_unit_name;
//                $nested_data['product_brand_id'] = $stock_row->product_brand_id;
//                $nested_data['product_brand_name'] = $stock_row->product_brand_name;
//                $nested_data['current_stock'] = $stock_row->current_stock;
//
//                array_push($store_stock_product,$nested_data);
//            }
//        }
//
//        if($store_stock_product)
//        {
//            $success['store_current_stock_list'] =  $store_stock_product;
//            return response()->json(['success'=>true,'response' => $success], $this->successStatus);
//        }else{
//            return response()->json(['success'=>false,'response'=>'No Store Current Stock List Found!'], $this->failStatus);
//        }
//    }

    // product sale invoice list
    public function productSaleInvoiceList(){
        $product_sale_invoices = DB::table('product_sales')
            ->select('id','invoice_no')
            ->get();

        if($product_sale_invoices)
        {
            $success['product_sale_invoices'] =  $product_sale_invoices;
            return response()->json(['success'=>true,'response' => $success], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Product Sale List Found!'], $this->failStatus);
        }
    }

    public function productSaleDetails(Request $request){
        //dd($request->all());
        $this->validate($request, [
            'product_sale_invoice_no'=> 'required',
        ]);

        $product_sales = DB::table('product_sales')
            ->leftJoin('users','product_sales.user_id','users.id')
            ->leftJoin('parties','product_sales.party_id','parties.id')
            ->leftJoin('warehouses','product_sales.warehouse_id','warehouses.id')
            ->leftJoin('stores','product_sales.store_id','stores.id')
            ->where('product_sales.invoice_no',$request->product_sale_invoice_no)
            ->select('product_sales.id','product_sales.invoice_no','product_sales.discount_type','product_sales.discount_amount','product_sales.total_amount','product_sales.paid_amount','product_sales.due_amount','product_sales.sale_date_time','users.name as user_name','parties.id as customer_id','parties.name as customer_name','warehouses.id as warehouse_id','warehouses.name as warehouse_name','stores.id as store_id','stores.name as store_name')
            ->first();

        if($product_sales){

            $product_sale_details = DB::table('product_sales')
                ->join('product_sale_details','product_sales.id','product_sale_details.product_sale_id')
                ->leftJoin('products','product_sale_details.product_id','products.id')
                ->leftJoin('product_units','product_sale_details.product_unit_id','product_units.id')
                ->leftJoin('product_brands','product_sale_details.product_brand_id','product_brands.id')
                ->where('product_sales.invoice_no',$request->product_sale_invoice_no)
                ->select('products.id as product_id','products.name as product_name','product_units.id as product_unit_id','product_units.name as product_unit_name','product_brands.id as product_brand_id','product_brands.name as product_brand_name','product_sale_details.qty','product_sale_details.qty as current_qty','product_sale_details.id as product_sale_detail_id','product_sale_details.price as mrp_price','product_sale_details.sale_date','product_sale_details.return_among_day','product_sale_details.price as mrp_price')
                ->get();

            $success['product_sales'] = $product_sales;
            $success['product_sale_details'] = $product_sale_details;
            return response()->json(['success'=>true,'response' => $success], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Product Sale Data Found!'], $this->failStatus);
        }
    }

    public function productSaleReturnList(){
        $product_whole_sales = DB::table('product_sale_returns')
            ->leftJoin('users','product_sale_returns.user_id','users.id')
            ->leftJoin('parties','product_sale_returns.party_id','parties.id')
            ->leftJoin('warehouses','product_sale_returns.warehouse_id','warehouses.id')
            ->leftJoin('stores','product_sale_returns.store_id','stores.id')
            ->select(
                'product_sale_returns.id',
                'product_sale_returns.invoice_no',
                'product_sale_returns.product_sale_invoice_no',
                'product_sale_returns.discount_type',
                'product_sale_returns.discount_amount',
                //'product_sale_returns.total_vat_amount',
                'product_sale_returns.total_amount',
                'product_sale_returns.paid_amount',
                'product_sale_returns.due_amount',
                'product_sale_returns.product_sale_return_date_time',
                'users.name as user_name',
                'parties.id as customer_id',
                'parties.name as customer_name',
                'warehouses.id as warehouse_id',
                'warehouses.name as warehouse_name',
                'stores.id as store_id',
                'stores.name as store_name',
                'stores.address as store_address'
            )
            ->orderBy('product_sale_returns.id','desc')
            ->get();

        if(count($product_whole_sales) > 0)
        {
            $product_whole_sale_arr = [];
            foreach ($product_whole_sales as $data){
                $payment_type = DB::table('transactions')->where('ref_id',$data->id)->where('transaction_type','sale_return_balance')->pluck('payment_type')->first();

                $nested_data['id']=$data->id;
                $nested_data['invoice_no']=$data->invoice_no;
                $nested_data['product_sale_invoice_no']=$data->product_sale_invoice_no;
                $nested_data['discount_type']=$data->discount_type;
                $nested_data['discount_amount']=$data->discount_amount;
                $nested_data['total_amount']=$data->total_amount;
                $nested_data['paid_amount']=$data->paid_amount;
                $nested_data['due_amount']=$data->due_amount;
                $nested_data['product_sale_return_date_time']=$data->product_sale_return_date_time;
                $nested_data['user_name']=$data->user_name;
                $nested_data['customer_id']=$data->customer_id;
                $nested_data['customer_name']=$data->customer_name;
                $nested_data['warehouse_id']=$data->warehouse_id;
                $nested_data['warehouse_name']=$data->warehouse_name;
                $nested_data['store_id']=$data->store_id;
                $nested_data['store_name']=$data->store_name;
                $nested_data['store_address']=$data->store_address;
                $nested_data['payment_type']=$payment_type;

                array_push($product_whole_sale_arr,$nested_data);
            }

            $success['product_sale_return_list'] =  $product_whole_sale_arr;
            return response()->json(['success'=>true,'response' => $success], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Product Sale Return List Found!'], $this->failStatus);
        }
    }

//    public function productSaleReturnDetails(Request $request){
//        //dd($request->all());
//        $this->validate($request, [
//            'product_sale_invoice_no'=> 'required',
//        ]);
//
//        $product_sales = DB::table('product_sales')
//            ->leftJoin('users','product_sales.user_id','users.id')
//            ->leftJoin('parties','product_sales.party_id','parties.id')
//            ->leftJoin('warehouses','product_sales.warehouse_id','warehouses.id')
//            ->leftJoin('stores','product_sales.store_id','stores.id')
//            ->where('product_sales.invoice_no',$request->product_sale_invoice_no)
//            ->select('product_sales.id','product_sales.invoice_no','product_sales.discount_type','product_sales.discount_amount','product_sales.total_amount','product_sales.paid_amount','product_sales.due_amount','product_sales.sale_date_time','users.name as user_name','parties.id as customer_id','parties.name as customer_name','warehouses.id as warehouse_id','warehouses.name as warehouse_name','stores.id as store_id','stores.name as store_name')
//            ->first();
//
//        if($product_sales){
//
//            $product_sale_details = DB::table('product_sales')
//                ->join('product_sale_details','product_sales.id','product_sale_details.product_sale_id')
//                ->leftJoin('products','product_sale_details.product_id','products.id')
//                ->leftJoin('product_units','product_sale_details.product_unit_id','product_units.id')
//                ->leftJoin('product_brands','product_sale_details.product_brand_id','product_brands.id')
//                ->where('product_sales.invoice_no',$request->product_sale_invoice_no)
//                ->select('products.id as product_id','products.name as product_name','product_units.id as product_unit_id','product_units.name as product_unit_name','product_brands.id as product_brand_id','product_brands.name as product_brand_name','product_sale_details.qty','product_sale_details.qty as current_qty','product_sale_details.id as product_sale_detail_id','product_sale_details.price as mrp_price','product_sale_details.sale_date','product_sale_details.return_among_day','product_sale_details.price as mrp_price')
//                ->get();
//
//            $success['product_sales'] = $product_sales;
//            $success['product_sale_details'] = $product_sale_details;
//            return response()->json(['success'=>true,'response' => $success], $this->successStatus);
//        }else{
//            return response()->json(['success'=>false,'response'=>'No Product Sale Data Found!'], $this->failStatus);
//        }
//    }

    public function productSaleReturnDetails(Request $request){
        $product_sale_return_details = DB::table('product_sale_returns')
            ->join('product_sale_return_details','product_sale_returns.id','product_sale_return_details.pro_sale_return_id')
            ->leftJoin('products','product_sale_return_details.product_id','products.id')
            ->leftJoin('product_units','product_sale_return_details.product_unit_id','product_units.id')
            ->leftJoin('product_brands','product_sale_return_details.product_brand_id','product_brands.id')
            ->where('product_sale_return_details.pro_sale_return_id',$request->product_sale_return_id)
            ->select(
                'products.id as product_id',
                'products.name as product_name',
                'product_units.id as product_unit_id',
                'product_units.name as product_unit_name',
                'product_brands.id as product_brand_id',
                'product_brands.name as product_brand_name',
                'product_sale_return_details.qty',
                'product_sale_return_details.id as product_sale_return_detail_id',
                'product_sale_return_details.price as mrp_price'
            )
            ->get();

        if($product_sale_return_details)
        {
            $success['product_sale_return_details'] =  $product_sale_return_details;
            return response()->json(['success'=>true,'response' => $success], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Product Sale Return Detail Found!'], $this->failStatus);
        }
    }

    public function productSaleReturnCreate(Request $request){
        //dd($request->all());
        $this->validate($request, [
            'party_id'=> 'required',
            'store_id'=> 'required',
            'paid_amount'=> 'required',
            'due_amount'=> 'required',
            'total_amount'=> 'required',
            'payment_type'=> 'required',
            'product_sale_invoice_no'=> 'required',
        ]);

        $product_sale_id = ProductSale::where('invoice_no',$request->product_sale_invoice_no)->pluck('id')->first();
        $get_invoice_no = ProductSaleReturn::latest('id','desc')->pluck('invoice_no')->first();
        if(!empty($get_invoice_no)){
            $get_invoice = str_replace("sale-return","",$get_invoice_no);
            $invoice_no = $get_invoice+1;
        }else{
            $invoice_no = 800000;
        }
        $final_invoice = 'sale-return'.$invoice_no;

        $date = date('Y-m-d');
        $date_time = date('Y-m-d h:i:s');

        $user_id = Auth::user()->id;
        $store_id = $request->store_id;
        $warehouse_id = Store::where('id',$store_id)->latest('id')->pluck('warehouse_id')->first();

        // product sale return
        $productSaleReturn = new ProductSaleReturn();
        $productSaleReturn ->invoice_no = $final_invoice;
        $productSaleReturn ->product_sale_invoice_no = $request->product_sale_invoice_no;
        $productSaleReturn ->user_id = $user_id;
        $productSaleReturn ->party_id = $request->party_id;
        $productSaleReturn ->warehouse_id = $warehouse_id;
        $productSaleReturn ->store_id = $store_id;
        $productSaleReturn ->product_sale_return_type = 'sale_return';
        $productSaleReturn ->discount_type = $request->discount_type ? $request->discount_type : NULL;
        $productSaleReturn ->discount_amount = $request->discount_amount ? $request->discount_amount : 0;
        $productSaleReturn ->paid_amount = $request->total_amount;
        $productSaleReturn ->due_amount = $request->due_amount;
        $productSaleReturn ->total_amount = $request->total_amount;
        $productSaleReturn ->product_sale_return_date = $date;
        $productSaleReturn ->product_sale_return_date_time = $date_time;
        $productSaleReturn->save();
        $insert_id = $productSaleReturn->id;

        if($insert_id)
        {
            // for live testing
            foreach ($request->products as $data) {

                $product_id =  $data['product_id'];

                $barcode = Product::where('id',$product_id)->pluck('barcode')->first();

                // product purchase detail
                $purchase_sale_return_detail = new ProductSaleReturnDetail();
                $purchase_sale_return_detail->pro_sale_return_id = $insert_id;
                $purchase_sale_return_detail->pro_sale_detail_id = $data['product_sale_detail_id'];
                $purchase_sale_return_detail->product_unit_id = $data['product_unit_id'];
                $purchase_sale_return_detail->product_brand_id = $data['product_brand_id'] ? $data['product_brand_id'] : NULL;
                $purchase_sale_return_detail->product_id = $product_id;
                $purchase_sale_return_detail->barcode = $barcode;
                $purchase_sale_return_detail->qty = $data['qty'];
                $purchase_sale_return_detail->price = $data['mrp_price'];
                $purchase_sale_return_detail->sub_total = $data['qty']*$data['mrp_price'];
                $purchase_sale_return_detail->save();

                $check_previous_stock = Stock::where('warehouse_id',$warehouse_id)->where('store_id',$store_id)->where('product_id',$product_id)->latest('id','desc')->pluck('current_stock')->first();
                if(!empty($check_previous_stock)){
                    $previous_stock = $check_previous_stock;
                }else{
                    $previous_stock = 0;
                }

                // product stock
                $stock = new Stock();
                $stock->ref_id = $insert_id;
                $stock->user_id = $user_id;
                $stock->warehouse_id = $warehouse_id;
                $stock->store_id = $store_id;
                $stock->product_id = $product_id;
                $stock->product_unit_id = $data['product_unit_id'];
                $stock->product_brand_id = $data['product_brand_id'] ? $data['product_brand_id'] : NULL;
                $stock->stock_type = 'sale_return';
                $stock->stock_where = 'store';
                $stock->stock_in_out = 'stock_in';
                $stock->previous_stock = $previous_stock;
                $stock->stock_in = $data['qty'];
                $stock->stock_out = 0;
                $stock->current_stock = $previous_stock + $data['qty'];
                $stock->stock_date = $date;
                $stock->stock_date_time = $date_time;
                $stock->save();



                // warehouse store current stock
                $update_warehouse_store_current_stock = WarehouseStoreCurrentStock::where('warehouse_id',$warehouse_id)
                    ->where('store_id',$store_id)
                    ->where('product_id',$product_id)
                    ->first();

                $exists_current_stock = $update_warehouse_store_current_stock->current_stock;
                $final_warehouse_current_stock = $exists_current_stock + $data['qty'];
                $update_warehouse_store_current_stock->current_stock=$final_warehouse_current_stock;
                $update_warehouse_store_current_stock->save();



                $check_return_last_date = ProductSaleDetail::where('id',$data['product_sale_detail_id'])->pluck('return_last_date')->first();
                $today_date = date('Y-m-d');
                if($check_return_last_date >= $today_date){
                    // for sale return cash back among 2 days
                    // transaction
                    $transaction = new Transaction();
                    $transaction->ref_id = $insert_id;
                    $transaction->invoice_no = $final_invoice;
                    $transaction->user_id = $user_id;
                    $transaction->warehouse_id = $warehouse_id;
                    $transaction->store_id = $store_id;
                    $transaction->party_id = $request->party_id;
                    $transaction->transaction_type = 'sale_return_cash';
                    $transaction->payment_type = $request->payment_type;
                    $transaction->amount = $data['qty']*$data['mrp_price'];
                    $transaction->transaction_date = $date;
                    $transaction->transaction_date_time = $date_time;
                    $transaction->save();

                    // payment paid
                    $payment_collection = new PaymentCollection();
                    $payment_collection->invoice_no = $final_invoice;
                    $payment_collection->product_sale_id = $product_sale_id;
                    $payment_collection->product_sale_return_id = $insert_id;
                    $payment_collection->user_id = $user_id;
                    $payment_collection->party_id = $request->party_id;
                    $payment_collection->warehouse_id = $request->warehouse_id;
                    $payment_collection->store_id = $request->store_id;
                    $payment_collection->collection_type = 'Return Cash';
                    $payment_collection->collection_amount = $data['qty']*$data['mrp_price'];
                    $payment_collection->due_amount = 0;
                    $payment_collection->current_collection_amount = $data['qty']*$data['mrp_price'];
                    $payment_collection->collection_date = $date;
                    $payment_collection->collection_date_time = $date_time;
                    $payment_collection->save();
                }else{
                    // for sale return balance add after 2 days
                    // transaction
                    $transaction = new Transaction();
                    $transaction->ref_id = $insert_id;
                    $transaction->invoice_no = $final_invoice;
                    $transaction->user_id = $user_id;
                    $transaction->warehouse_id = $warehouse_id;
                    $transaction->store_id = $store_id;
                    $transaction->party_id = $request->party_id;
                    $transaction->transaction_type = 'sale_return_balance';
                    $transaction->payment_type = $request->payment_type;
                    $transaction->amount = $data['qty']*$data['mrp_price'];
                    $transaction->transaction_date = $date;
                    $transaction->transaction_date_time = $date_time;
                    $transaction->save();

                    // payment paid
                    $payment_collection = new PaymentCollection();
                    $payment_collection->invoice_no = $final_invoice;
                    $payment_collection->product_sale_id = $product_sale_id;
                    $payment_collection->product_sale_return_id = $insert_id;
                    $payment_collection->user_id = $user_id;
                    $payment_collection->party_id = $request->party_id;
                    $payment_collection->warehouse_id = $request->warehouse_id;
                    $payment_collection->store_id = $request->store_id;
                    $payment_collection->collection_type = 'Return Balance';
                    $payment_collection->collection_amount = $data['qty']*$data['mrp_price'];
                    $payment_collection->due_amount = 0;
                    $payment_collection->current_collection_amount = $data['qty']*$data['mrp_price'];
                    $payment_collection->collection_date = $date;
                    $payment_collection->collection_date_time = $date_time;
                    $payment_collection->save();

                    // add balance
                    $party_previous_virtual_balance = Party::where('id',$request->party_id)->pluck('virtual_balance')->first();

                    $party = Party::find($request->party_id);
                    $party->virtual_balance = $party_previous_virtual_balance + ($data['qty']*$data['mrp_price']);
                    $party->update();

                }

            }




            return response()->json(['success'=>true,'response' => 'Inserted Successfully.'], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Inserted Successfully!'], $this->failStatus);
        }
    }

    public function transactionHistory(Request $request){
        $transaction_history = DB::table('transactions')
            ->leftJoin('users','transactions.user_id','users.id')
            ->leftJoin('warehouses','transactions.warehouse_id','warehouses.id')
            ->leftJoin('stores','transactions.store_id','stores.id')
            ->leftJoin('parties','transactions.party_id','parties.id')
            ->select('transactions.id as transaction_id','users.name as transaction_by_user','warehouses.name as warehouse_name','stores.name as store_name','parties.name as party_name','transactions.transaction_type','transactions.payment_type','transactions.amount','transactions.transaction_date_time')
            ->latest('transactions.id','desc')
            ->get();

        if($transaction_history)
        {
            $success['transaction_history'] =  $transaction_history;
            return response()->json(['success'=>true,'response' => $success], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Transaction History Found!'], $this->failStatus);
        }
    }


    // stock sync
    function product_store_stock_sync($warehouse_id,$store_id,$product_id){

        if($store_id){
            $stock_data = Stock::where('warehouse_id',$warehouse_id)->where('store_id',$store_id)->where('product_id',$product_id)->get();
        }else{
            $stock_data = Stock::where('warehouse_id',$warehouse_id)->where('store_id','=',NULL)->where('product_id',$product_id)->get();
        }

        $row_count = count($stock_data);
        if($row_count > 0) {
            $store_previous_row_current_stock = null;
            $stock_in_flag = 0;
            $stock_out_flag = 0;

            foreach ($stock_data as $key => $data) {

                $id = $data->id;
                $previous_stock = $data->previous_stock;
                $stock_in = $data->stock_in;
                $stock_out = $data->stock_out;
                $current_stock = $data->current_stock;

                if($key == 0) {
                    $stock = Stock::find($id);
                    $stock->previous_stock = 0;
                    $stock->current_stock = $stock_in;
                    $affectedRow = $stock->update();
                    if($affectedRow){
                        $current_stock = $stock->current_stock;
                    }
                }else{
                    // update part
                    if($stock_in > 0){
                        if($stock_in_flag == 1){
                            $stock = Stock::find($id);
                            $stock->previous_stock = $store_previous_row_current_stock;
                            $stock->current_stock = $store_previous_row_current_stock + $stock_in;
                            $affectedRow = $stock->update();
                            if($affectedRow){
                                $current_stock = $stock->current_stock;
                            }
                        }else if($previous_stock != $store_previous_row_current_stock){
                            $stock_in_flag = 1;

                            $stock = Stock::find($id);
                            $stock->previous_stock = $store_previous_row_current_stock;
                            $stock->current_stock = $store_previous_row_current_stock + $stock_in;
                            $affectedRow = $stock->update();
                            if($affectedRow){
                                $current_stock = $stock->current_stock;
                            }
                        }else{}
                    }else if($stock_out > 0){
                        if($stock_out_flag == 1) {
                            $stock = Stock::find($id);
                            $stock->previous_stock = $store_previous_row_current_stock;
                            $stock->current_stock = $store_previous_row_current_stock - $stock_out;
                            $affectedRow = $stock->update();
                            if ($affectedRow) {
                                $current_stock = $stock->current_stock;
                            }
                        }else if($previous_stock != $store_previous_row_current_stock) {
                            $stock_out_flag = 1;

                            $stock = Stock::find($id);
                            $stock->previous_stock = $store_previous_row_current_stock;
                            $stock->current_stock = $store_previous_row_current_stock - $stock_out;
                            $affectedRow = $stock->update();
                            if ($affectedRow) {
                                $current_stock = $stock->current_stock;
                            }
                        }else{}
                    }else{}
                }
                $store_previous_row_current_stock = $current_stock;
            }
        }else{}
    }


}
