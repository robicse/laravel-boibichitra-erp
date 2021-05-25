<?php

namespace App\Http\Controllers\API;


use App\Http\Controllers\Controller;

use App\PaymentCollection;
use App\PaymentPaid;
use App\ProductPurchase;
use App\ProductSale;
use App\Store;
use App\Transaction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    public $successStatus = 200;
    public $authStatus = 401;
    public $failStatus = 402;
    public $ExistsStatus = 403;
    public $validationStatus = 404;

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

    public function posSaleCustomerList(){
        $customer_lists = DB::table('parties')
            ->where('type','customer')
            ->where('customer_type','POS Sale')
            ->select('id','name','phone')
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
//            $sum_payment_paid_due_amount = DB::table('product_purchases')
//                ->where('product_purchases.due_amount','>',0)
//                ->where('product_purchases.party_id',$request->supplier_id)
//                ->select(DB::raw('SUM(due_amount) as total_payment_paid_due_amount'))
//                ->first();

            $sum_payment_paid_due_amount = DB::table('payment_paids')
                //->where('product_purchases.due_amount','>',0)
                ->where('party_id',$request->supplier_id)
                ->select(DB::raw('SUM(due_amount) as total_payment_due_amount'),DB::raw('SUM(paid_amount) as total_payment_paid_amount'))
                ->first();
            if($sum_payment_paid_due_amount){
                $total_payment_paid_due_amount = $sum_payment_paid_due_amount->total_payment_due_amount - $sum_payment_paid_due_amount->total_payment_paid_amount;
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

    public function getPaymentInvoiceNo(){
        $get_invoice_no = DB::table('payment_paids')
            ->where('paid_type','Payment')
            ->select('invoice_no')
            ->first();
        if(!empty($get_invoice_no)){
            $get_invoice = str_replace("payment-","",$get_invoice_no);
            $invoice_no = $get_invoice+1;
        }else{
            $invoice_no = 1000;
        }
        $final_invoice = 'payment-'.$invoice_no;

        return response()->json(['success'=>true,'response' => $final_invoice,'date' => date('Y-m-d')], $this->successStatus);
    }

    public function supplierDuePaymentList(){
        $payment_paids = DB::table('payment_paids')
            ->join('parties','payment_paids.party_id','parties.id')
            ->where('payment_paids.paid_type','Payment')
            ->select('payment_paids.id as payment_paid_id','payment_paids.invoice_no','parties.name','payment_paids.paid_amount','payment_paids.payment_type','payment_paids.paid_date')
            ->get();

        if($payment_paids)
        {
            return response()->json(['success'=>true,'response' => $payment_paids], $this->successStatus);
        }else{
            return response()->json(['success'=>true,'response' => 'No Data Found!'], $this->failStatus);
        }

    }

    public function SupplierDuePaymentCreate(Request $request){
        //dd($request->all());
        $this->validate($request, [
            'supplier_id'=> 'required',
            'paid_amount'=> 'required',
            'current_due_amount'=> 'required',
            'payment_type'=> 'required',
            'invoice_no'=> 'required',
        ]);

        $date = $request->date;
        $date_time = $date.' h:i:s';

        $user_id = Auth::user()->id;

        // payment paid
        $payment_paid = new PaymentPaid();
        $payment_paid->invoice_no = $request->invoice_no;
        $payment_paid->product_purchase_id = NULL;
        $payment_paid->product_purchase_return_id = NULL;
        $payment_paid->user_id = $user_id;
        $payment_paid->party_id = $request->supplier_id;
        $payment_paid->paid_type = 'Payment';
        $payment_paid->paid_amount = $request->paid_amount;
        $payment_paid->due_amount = 0;
        $payment_paid->current_paid_amount = NULL;
        $payment_paid->paid_date = $date;
        $payment_paid->paid_date_time = $date_time;
        $payment_paid->save();
        $insert_id = $payment_paid->id;
        if($insert_id){
            // transaction
            $transaction = new Transaction();
            $transaction->ref_id = $insert_id;
            $transaction->invoice_no = $request->invoice_no;
            $transaction->user_id = $user_id;
            $transaction->warehouse_id = 6;
            $transaction->party_id = $request->supplier_id;
            $transaction->transaction_type = 'payment_paid';
            $transaction->payment_type = $request->payment_type;
            $transaction->amount = $request->paid_amount;
            $transaction->transaction_date = $date;
            $transaction->transaction_date_time = $date_time;
            $transaction->save();

            return response()->json(['success'=>true,'response' => 'Inserted Successfully.'], $this->successStatus);

        }else{
            return response()->json(['success'=>false,'response'=>'No Inserted Successfully!'], $this->failStatus);
        }
    }

    public function SupplierDuePaymentEdit(Request $request){
        //dd($request->all());
        $this->validate($request, [
            'supplier_id'=> 'required',
            'paid_amount'=> 'required',
            'current_due_amount'=> 'required',
            'payment_type'=> 'required',
        ]);

        $date = $request->date;
        $date_time = $date.' h:i:s';

        $user_id = Auth::user()->id;

        // payment paid
        $payment_paid = PaymentPaid::find($request->payment_paid_id);
        $payment_paid->user_id = $user_id;
        $payment_paid->party_id = $request->supplier_id;
        $payment_paid->paid_amount = $request->paid_amount;
        $payment_paid->paid_date = $date;
        $payment_paid->paid_date_time = $date_time;
        $affected_row = $payment_paid->save();
        if($affected_row){
            // transaction
            $transaction = Transaction::where('transaction_type','payment_paid')->where('ref_id',$request->payment_paid_id)->first();
            $transaction->party_id = $request->supplier_id;
            $transaction->payment_type = $request->payment_type;
            $transaction->amount = $request->paid_amount;
            $transaction->transaction_date = $date;
            $transaction->transaction_date_time = $date_time;
            $transaction->update();

            return response()->json(['success'=>true,'response' => 'Updated Successfully.'], $this->successStatus);

        }else{
            return response()->json(['success'=>false,'response'=>'No Updated Successfully!'], $this->failStatus);
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


}
