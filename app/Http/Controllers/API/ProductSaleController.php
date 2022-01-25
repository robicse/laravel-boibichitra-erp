<?php

namespace App\Http\Controllers\API;

use App\ChartOfAccount;
use App\ChartOfAccountTransaction;
use App\ChartOfAccountTransactionDetail;
use App\Helpers\APIHelpers;
use App\Http\Controllers\Controller;
use App\Http\Resources\ProductWholeSaleCollection;
use App\Party;
use App\PaymentCollection;
use App\PaymentPaid;
use App\Product;
use App\ProductPurchaseReturn;
use App\ProductSale;
use App\ProductSaleDetail;
use App\ProductSaleReturn;
use App\ProductSaleReturnDetail;
use App\Stock;
use App\Store;
use App\Transaction;
use App\VoucherType;
use App\WarehouseCurrentStock;
use App\WarehouseStoreCurrentStock;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ProductSaleController extends Controller
{
    public $successStatus = 200;
    public $authStatus = 401;
    public $failStatus = 402;
    public $ExistsStatus = 403;
    public $validationStatus = 404;

    public function productSaleInvoiceNo(){
        $get_invoice_no = ProductSale::latest()->pluck('invoice_no')->first();
        if(!empty($get_invoice_no)){
            $get_invoice = str_replace("sale-","",$get_invoice_no);
            $invoice_no = $get_invoice+1;
        }else{
            $invoice_no = 100000;
        }
        $final_invoice = 'sale-'.$invoice_no;

        return response()->json(['success'=>true,'response' => $final_invoice,'date' => date('Y-m-d')], $this->successStatus);
    }

    public function productWholeSaleList(){
        $product_whole_sales = DB::table('product_sales')
            ->leftJoin('users','product_sales.user_id','users.id')
            ->leftJoin('parties','product_sales.party_id','parties.id')
            ->leftJoin('warehouses','product_sales.warehouse_id','warehouses.id')
            //->leftJoin('stores','product_sales.store_id','stores.id')
            ->where('product_sales.sale_type','whole_sale')
            //->select('product_sales.id','product_sales.invoice_no','product_sales.discount_type','product_sales.discount_amount','product_sales.total_vat_amount','product_sales.total_amount','product_sales.paid_amount','product_sales.due_amount','product_sales.sale_date_time','users.name as user_name','parties.id as customer_id','parties.name as customer_name','warehouses.id as warehouse_id','warehouses.name as warehouse_name','stores.id as store_id','stores.name as store_name','stores.address as store_address')
            ->select('product_sales.id','product_sales.invoice_no','product_sales.sale_date','product_sales.discount_type','product_sales.discount_amount','product_sales.total_vat_amount','product_sales.total_amount','product_sales.paid_amount','product_sales.due_amount','product_sales.sale_date_time','product_sales.miscellaneous_comment','product_sales.miscellaneous_charge','users.name as user_name','parties.id as customer_id','parties.name as customer_name','parties.phone as customer_phone','parties.email as customer_email','parties.address as customer_address','warehouses.id as warehouse_id','warehouses.name as warehouse_name')
            ->orderBy('product_sales.id','desc')
            ->get();

        if(count($product_whole_sales) > 0)
        {
            $product_whole_sale_arr = [];
            foreach ($product_whole_sales as $data){
                $payment_type = DB::table('transactions')->where('ref_id',$data->id)->where('transaction_type','whole_sale')->pluck('payment_type')->first();

                $nested_data['id']=$data->id;
                $nested_data['invoice_no']=ucfirst($data->invoice_no);
                $nested_data['sale_date']=$data->sale_date;
                $nested_data['miscellaneous_comment']=$data->miscellaneous_comment;
                $nested_data['miscellaneous_charge']=$data->miscellaneous_charge;
                $nested_data['discount_type']=$data->discount_type;
                $nested_data['discount_amount']=$data->discount_amount;
                $nested_data['total_vat_amount']=$data->total_vat_amount;
                $nested_data['total_amount']=$data->total_amount;
                $nested_data['paid_amount']=$data->paid_amount;
                $nested_data['due_amount']=$data->due_amount;
                $nested_data['sale_date_time']=$data->sale_date_time;
                $nested_data['user_name']=$data->user_name;
                $nested_data['customer_id']=$data->customer_id;
                $nested_data['customer_name']=$data->customer_name;
                $nested_data['customer_phone']=$data->customer_phone;
                $nested_data['customer_email']=$data->customer_email;
                $nested_data['customer_address']=$data->customer_address;
                $nested_data['warehouse_id']=$data->warehouse_id;
                $nested_data['warehouse_name']=$data->warehouse_name;
                //$nested_data['store_id']=$data->store_id;
                //$nested_data['store_name']=$data->store_name;
                //$nested_data['store_address']=$data->store_address;
                $nested_data['payment_type']=$payment_type;

                array_push($product_whole_sale_arr,$nested_data);
            }

            $success['product_whole_sales'] =  $product_whole_sale_arr;
            return response()->json(['success'=>true,'response' => $success], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Product Whole Sale List Found!'], $this->failStatus);
        }
    }

    public function productWholeSaleListWithSearch(Request $request){
        if($request->search){
            $product_whole_sales = ProductSale::join('parties','product_sales.party_id','parties.id')
                ->where('product_sales.sale_type','whole_sale')
                ->where('product_sales.invoice_no','like','%'.$request->search.'%')
                ->orWhere('parties.name','like','%'.$request->search.'%')
                ->select(
                    'product_sales.id',
                    'product_sales.invoice_no',
                    'product_sales.sale_date',
                    'product_sales.discount_type',
                    'product_sales.discount_amount',
                    'product_sales.total_vat_amount',
                    'product_sales.total_amount',
                    'product_sales.paid_amount',
                    'product_sales.due_amount',
                    'product_sales.sale_date_time',
                    'product_sales.miscellaneous_comment',
                    'product_sales.miscellaneous_charge',
                    'product_sales.user_id',
                    'product_sales.party_id',
                    'product_sales.warehouse_id'
                )
                ->orderBy('product_sales.id','desc')
                ->paginate(12);
            return new ProductWholeSaleCollection($product_whole_sales);
        }else{
            return new ProductWholeSaleCollection(ProductSale::where('sale_type','whole_sale')->latest()->paginate(12));
        }

    }

    public function productWholeSaleListPagination(){
        return new ProductWholeSaleCollection(ProductSale::where('sale_type','whole_sale')->latest()->paginate(12));
    }

    public function productWholeSaleListPaginationWithSearch(Request $request){
        try {
            if($request->search){
                $product_whole_sales = ProductSale::join('parties','product_sales.party_id','parties.id')
                    ->where('product_sales.sale_type','whole_sale')
                    ->where('product_sales.invoice_no','like','%'.$request->search.'%')
                    ->orWhere('parties.name','like','%'.$request->search.'%')
                    ->select(
                        'product_sales.id',
                        'product_sales.invoice_no',
                        'product_sales.sale_date',
                        'product_sales.sub_total',
                        'product_sales.miscellaneous_comment',
                        'product_sales.miscellaneous_charge',
                        'product_sales.discount_type',
                        'product_sales.discount_percent',
                        'product_sales.discount_amount',
                        'product_sales.total_vat_amount',
                        'product_sales.after_discount_amount',
                        'product_sales.total_amount',
                        'product_sales.paid_amount',
                        'product_sales.due_amount',
                        'product_sales.sale_date_time',
                        'product_sales.user_id',
                        'product_sales.party_id',
                        'product_sales.warehouse_id'
                    )
                    ->orderBy('product_sales.id','desc')
                    ->paginate(12);

            }else{
                $product_whole_sales = ProductSale::where('sale_type','whole_sale')->latest()->paginate(12);
            }

            if($product_whole_sales === null){
                $response = APIHelpers::createAPIResponse(true,404,'No Product Sale Found.',null);
                return response()->json($response,404);
            }else{
                return new ProductWholeSaleCollection($product_whole_sales);
            }
        } catch (\Exception $e) {
            //return $e->getMessage();
            $response = APIHelpers::createAPIResponse(false,500,'Internal Server Error.',null);
            return response()->json($response,500);
        }
    }

    public function productWholeSaleDetails(Request $request){
        try {
            $product_sale_details = DB::table('product_sales')
                ->join('product_sale_details','product_sales.id','product_sale_details.product_sale_id')
                ->leftJoin('products','product_sale_details.product_id','products.id')
                ->leftJoin('product_units','product_sale_details.product_unit_id','product_units.id')
                ->leftJoin('product_brands','product_sale_details.product_brand_id','product_brands.id')
                ->where('product_sales.id',$request->product_sale_id)
                ->select(
                    'products.id as product_id',
                    'products.name as product_name',
                    'product_units.id as product_unit_id',
                    'product_units.name as product_unit_name',
                    'product_brands.id as product_brand_id',
                    'product_brands.name as product_brand_name',
                    'product_sales.warehouse_id',
                    'product_sales.paid_amount',
                    'product_sales.due_amount',
                    'product_sale_details.qty',
                    'product_sale_details.id as product_sale_detail_id',
                    'product_sale_details.price as whole_sale_price',
                    'product_sale_details.vat_amount'
                )
                ->get();

            $sale_product = [];
            if(count($product_sale_details) > 0){
                foreach ($product_sale_details as $product_sale_detail){
                    $current_stock = warehouseProductCurrentStock($product_sale_detail->warehouse_id,$product_sale_detail->product_id);

                    $nested_data['product_id']=$product_sale_detail->product_id;
                    $nested_data['product_name']=$product_sale_detail->product_name;
                    $nested_data['product_unit_id']=$product_sale_detail->product_unit_id;
                    $nested_data['product_unit_name']=$product_sale_detail->product_unit_name;
                    $nested_data['product_brand_id']=$product_sale_detail->product_brand_id;
                    $nested_data['product_brand_name']=$product_sale_detail->product_brand_name;
                    $nested_data['paid_amount']=$product_sale_detail->paid_amount;
                    $nested_data['due_amount']=$product_sale_detail->due_amount;
                    $nested_data['qty']=$product_sale_detail->qty;
                    $nested_data['product_sale_detail_id']=$product_sale_detail->product_sale_detail_id;
                    $nested_data['whole_sale_price']=$product_sale_detail->whole_sale_price;
                    $nested_data['vat_amount']=$product_sale_detail->vat_amount;
                    $nested_data['current_stock']=$current_stock;

                    array_push($sale_product, $nested_data);
                }
            }

            if($product_sale_details === null){
                $response = APIHelpers::createAPIResponse(true,404,'No Product Whole Sale Detail Found.',null);
                return response()->json($response,404);
            }else{
                $response = APIHelpers::createAPIResponse(false,200,'',$sale_product);
                return response()->json($response,200);
            }
        } catch (\Exception $e) {
            //return $e->getMessage();
            $response = APIHelpers::createAPIResponse(false,500,'Internal Server Error.',null);
            return response()->json($response,500);
        }
    }

    public function productWholeSaleCreate(Request $request){

        try {
            $validator = Validator::make($request->all(), [
                'party_id'=> 'required',
                //'store_id'=> 'required',
                'warehouse_id'=> 'required',
                'paid_amount'=> 'required',
                'due_amount'=> 'required',
                'total_amount'=> 'required',
                'payment_type'=> 'required',
            ]);

            if ($validator->fails()) {
                $response = APIHelpers::createAPIResponse(true,400,$validator->errors(),null);
                return response()->json($response,400);
            }

            $get_invoice_no = ProductSale::latest()->pluck('invoice_no')->first();
            if(!empty($get_invoice_no)){
                $get_invoice = str_replace("sale-","",$get_invoice_no);
                $invoice_no = $get_invoice+1;
            }else{
                $invoice_no = 100000;
            }
            $final_invoice = 'sale-'.$invoice_no;

            $date = $request->date;
            $date_time = $date." ".date('h:i:s');
            $add_two_day_date =  date('Y-m-d', strtotime($date. ' + 2 days'));

            $user_id = Auth::user()->id;
            $warehouse_id = $request->warehouse_id;

            // product purchase
            $productSale = new ProductSale();
            $productSale->invoice_no = $final_invoice;
            $productSale->user_id = $user_id;
            $productSale->store_id = NULL;
            $productSale->warehouse_id = $warehouse_id;
            $productSale->party_id = $request->party_id;
            $productSale->sale_type = 'whole_sale';
            $productSale->sub_total = $request->sub_total;
            $productSale->discount_type = $request->discount_type ? $request->discount_type : NULL;
            $productSale->discount_percent = $request->discount_percent ? $request->discount_percent : 0;
            $productSale->discount_amount = $request->discount_amount ? $request->discount_amount : 0;
            $productSale->after_discount_amount = $request->after_discount_amount ? $request->after_discount_amount : 0;
            $productSale->miscellaneous_comment = $request->miscellaneous_comment ? $request->miscellaneous_comment : NULL;
            $productSale->miscellaneous_charge = $request->miscellaneous_charge ? $request->miscellaneous_charge : 0;
            $productSale->total_vat_amount = $request->total_vat_amount;
            $productSale->total_amount = $request->total_amount;
            $productSale->paid_amount = $request->paid_amount;
            $productSale->due_amount = $request->due_amount;
            $productSale->sale_date = $date;
            $productSale->sale_date_time = $date_time;
            $productSale->save();
            $insert_id = $productSale->id;

            // discount start
            $sum_total_amount = 0;
            foreach ($request->products as $data) {
                $price = $data['mrp_price'];
                $qty = $data['qty'];
                $sum_total_amount += (float)$price * (float)$qty;
            }
            // discount start

            if($insert_id)
            {
                // for postman testing

                // for live testing
                foreach ($request->products as $data) {

                    $product_id =  $data['product_id'];

                    $barcode = Product::where('id',$product_id)->pluck('barcode')->first();
                    $get_purchase_price = Product::where('id',$product_id)->pluck('purchase_price')->first();

                    // discount start
//                    $price = $data['mrp_price'];
//                    $discount_amount = $request->discount_amount;
//                    $total_amount = $request->total_amount;
//
//                    $final_discount_amount = (float)$discount_amount * (float)$price;
//                    $final_total_amount = (float)$discount_amount + (float)$total_amount;
//                    $discount_type = $request->discount_type;
//                    $discount = (float)$final_discount_amount/(float)$final_total_amount;
//                    if($discount_type != NULL){
//                        if($discount_type == 'Flat'){
//                            $discount = round($discount);
//                        }
//                    }
                    // discount end



                    // discount start
                    $price = $data['mrp_price'];
                    $qty = $data['qty'];
                    $final_discount_amount = $request->discount_amount;
                    $sub_total_amount = (float)$price * (float)$qty;
                    $amount = $final_discount_amount*$sub_total_amount;
                    $discount = $amount/$sum_total_amount;
                    // discount end

                    // vat and sub total start
                    $after_discount_amount = $sub_total_amount - $discount;
                    $sub_total = $after_discount_amount;
                    // vat and sub total end

                    // product sale detail
                    $product_sale_detail = new ProductSaleDetail();
                    $product_sale_detail->product_sale_id = $insert_id;
                    $product_sale_detail->product_unit_id = $data['product_unit_id'];
                    $product_sale_detail->product_brand_id = $data['product_brand_id'] ? $data['product_brand_id'] : NULL;
                    $product_sale_detail->product_id = $product_id;
                    $product_sale_detail->purchase_price = $get_purchase_price;
                    $product_sale_detail->qty = $data['qty'];
                    $product_sale_detail->price = $data['mrp_price'];
                    $product_sale_detail->discount = $discount;
                    //$product_sale_detail->vat_amount = $data['vat_amount'];
                    $product_sale_detail->vat_amount = 0;
                    //$product_sale_detail->sub_total = ($data['qty']*$data['mrp_price']) + ($data['qty']);
                    $product_sale_detail->sub_total = $sub_total;
                    $product_sale_detail->barcode = $barcode;
                    $product_sale_detail->sale_date = $date;
                    $product_sale_detail->return_among_day = 2;
                    $product_sale_detail->return_last_date = $add_two_day_date;
                    $product_sale_detail->save();

                    $check_previous_stock = Stock::where('warehouse_id',$warehouse_id)->where('stock_where','warehouse')->where('product_id',$product_id)->latest()->pluck('current_stock')->first();
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
                    $stock->store_id = NULL;
                    $stock->product_id = $product_id;
                    $stock->product_unit_id = $data['product_unit_id'];
                    $stock->product_brand_id = $data['product_brand_id'] ? $data['product_brand_id'] : NULL;
                    $stock->stock_type = 'whole_sale';
                    $stock->stock_where = 'warehouse';
                    $stock->stock_in_out = 'stock_out';
                    $stock->previous_stock = $previous_stock;
                    $stock->stock_in = 0;
                    $stock->stock_out = $data['qty'];
                    $stock->current_stock = $previous_stock - $data['qty'];
                    $stock->stock_date = $date;
                    $stock->stock_date_time = $date_time;
                    $stock->save();


                    // warehouse current stock
                    $update_warehouse_current_stock = WarehouseCurrentStock::where('warehouse_id',$warehouse_id)
                        ->where('product_id',$product_id)
                        ->first();

                    $exists_current_stock = $update_warehouse_current_stock->current_stock;
                    $final_warehouse_current_stock = $exists_current_stock - $data['qty'];
                    $update_warehouse_current_stock->current_stock=$final_warehouse_current_stock;
                    $update_warehouse_current_stock->save();

                }

                // N.B: whole sale kono paid hobe na

                // transaction
    //            $transaction = new Transaction();
    //            $transaction->ref_id = $insert_id;
    //            $transaction->invoice_no = $final_invoice;
    //            $transaction->user_id = $user_id;
    //            $transaction->warehouse_id = $warehouse_id;
    //            $transaction->store_id = NULL;
    //            $transaction->party_id = $request->party_id;
    //            $transaction->transaction_type = 'whole_sale';
    //            $transaction->payment_type = $request->payment_type;
    //            $transaction->amount = $request->paid_amount;
    //            $transaction->transaction_date = $date;
    //            $transaction->transaction_date_time = $date_time;
    //            $transaction->save();
    //            $transaction_id = $transaction->id;
    //
    //            // payment paid
    //            $payment_collection = new PaymentCollection();
    //            $payment_collection->invoice_no = $final_invoice;
    //            $payment_collection->product_sale_id = $insert_id;
    //            $payment_collection->user_id = $user_id;
    //            $payment_collection->party_id = $request->party_id;
    //            $payment_collection->warehouse_id = $warehouse_id;
    //            $payment_collection->collection_type = 'Sale';
    //            $payment_collection->collection_amount = $request->paid_amount;
    //            $payment_collection->due_amount = $request->due_amount;
    //            $payment_collection->current_collection_amount = $request->paid_amount;
    //            $payment_collection->collection_date = $date;
    //            $payment_collection->collection_date_time = $date_time;
    //            $payment_collection->save();
    //
    //            if($request->paid_amount > 0){
    //                // posting
    //                $month = date('m', strtotime($request->date));
    //                $year = date('Y', strtotime($request->date));
    //                $transaction_date_time = date('Y-m-d H:i:s');
    //
    //                $get_voucher_name = VoucherType::where('id',2)->pluck('name')->first();
    //                $get_voucher_no = ChartOfAccountTransaction::where('voucher_type_id',2)->latest()->pluck('voucher_no')->first();
    //                if(!empty($get_voucher_no)){
    //                    $get_voucher_name_str = $get_voucher_name."-";
    //                    $get_voucher = str_replace($get_voucher_name_str,"",$get_voucher_no);
    //                    $voucher_no = $get_voucher+1;
    //                }else{
    //                    $voucher_no = 8000;
    //                }
    //                $final_voucher_no = $get_voucher_name.'-'.$voucher_no;
    //                $chart_of_account_transactions = new ChartOfAccountTransaction();
    //                $chart_of_account_transactions->ref_id = $insert_id;
    //                $chart_of_account_transactions->transaction_type = 'Sales';
    //                $chart_of_account_transactions->user_id = $user_id;
    //                $chart_of_account_transactions->store_id = NULL;
    //                $chart_of_account_transactions->voucher_type_id = 2;
    //                $chart_of_account_transactions->voucher_no = $final_voucher_no;
    //                $chart_of_account_transactions->is_approved = 'approved';
    //                $chart_of_account_transactions->transaction_date = $date;
    //                $chart_of_account_transactions->transaction_date_time = $transaction_date_time;
    //                $chart_of_account_transactions->save();
    //                $chart_of_account_transactions_insert_id = $chart_of_account_transactions->id;
    //
    //                if($chart_of_account_transactions_insert_id){
    //
    //                    // sales
    //                    $sales_chart_of_account_info = ChartOfAccount::where('head_name','Sales')->first();
    //                    $chart_of_account_transaction_details = new ChartOfAccountTransactionDetail();
    //                    $chart_of_account_transaction_details->chart_of_account_transaction_id = $chart_of_account_transactions_insert_id;
    //                    $chart_of_account_transaction_details->chart_of_account_id = $sales_chart_of_account_info->id;
    //                    $chart_of_account_transaction_details->chart_of_account_number = $sales_chart_of_account_info->head_code;
    //                    $chart_of_account_transaction_details->chart_of_account_name = 'Sales';
    //                    $chart_of_account_transaction_details->chart_of_account_parent_name = $sales_chart_of_account_info->parent_head_name;
    //                    $chart_of_account_transaction_details->chart_of_account_type = $sales_chart_of_account_info->head_type;
    //                    $chart_of_account_transaction_details->debit = NULL;
    //                    $chart_of_account_transaction_details->credit = $request->paid_amount;
    //                    $chart_of_account_transaction_details->description = 'Income From Sales';
    //                    $chart_of_account_transaction_details->year = $year;
    //                    $chart_of_account_transaction_details->month = $month;
    //                    $chart_of_account_transaction_details->transaction_date = $date;
    //                    $chart_of_account_transaction_details->transaction_date_time = $transaction_date_time;
    //                    $chart_of_account_transaction_details->save();
    //
    //                    // cash
    //                    if($request->payment_type == 'Cash'){
    //                        $cash_chart_of_account_info = ChartOfAccount::where('head_name','Cash')->first();
    //                        $chart_of_account_transaction_details = new ChartOfAccountTransactionDetail();
    //                        $chart_of_account_transaction_details->chart_of_account_transaction_id = $chart_of_account_transactions_insert_id;
    //                        $chart_of_account_transaction_details->chart_of_account_id = $cash_chart_of_account_info->id;
    //                        $chart_of_account_transaction_details->chart_of_account_number = $cash_chart_of_account_info->head_code;
    //                        $chart_of_account_transaction_details->chart_of_account_name = 'Cash';
    //                        $chart_of_account_transaction_details->chart_of_account_parent_name = $cash_chart_of_account_info->parent_head_name;
    //                        $chart_of_account_transaction_details->chart_of_account_type = $cash_chart_of_account_info->head_type;
    //                        $chart_of_account_transaction_details->debit = $request->paid_amount;
    //                        $chart_of_account_transaction_details->credit = NULL;
    //                        $chart_of_account_transaction_details->description = 'Cash In For Sales';
    //                        $chart_of_account_transaction_details->year = $year;
    //                        $chart_of_account_transaction_details->month = $month;
    //                        $chart_of_account_transaction_details->transaction_date = $date;
    //                        $chart_of_account_transaction_details->transaction_date_time = $transaction_date_time;
    //                        $chart_of_account_transaction_details->save();
    //                    }elseif($request->payment_type == 'Check'){
    //                        $cash_chart_of_account_info = ChartOfAccount::where('head_name','Check')->first();
    //                        $chart_of_account_transaction_details = new ChartOfAccountTransactionDetail();
    //                        $chart_of_account_transaction_details->chart_of_account_transaction_id = $chart_of_account_transactions_insert_id;
    //                        $chart_of_account_transaction_details->chart_of_account_id = $cash_chart_of_account_info->id;
    //                        $chart_of_account_transaction_details->chart_of_account_number = $cash_chart_of_account_info->head_code;
    //                        $chart_of_account_transaction_details->chart_of_account_name = 'Check';
    //                        $chart_of_account_transaction_details->chart_of_account_parent_name = $cash_chart_of_account_info->parent_head_name;
    //                        $chart_of_account_transaction_details->chart_of_account_type = $cash_chart_of_account_info->head_type;
    //                        $chart_of_account_transaction_details->debit = $request->paid_amount;
    //                        $chart_of_account_transaction_details->credit = NULL;
    //                        $chart_of_account_transaction_details->description = 'Check In For Sales';
    //                        $chart_of_account_transaction_details->year = $year;
    //                        $chart_of_account_transaction_details->month = $month;
    //                        $chart_of_account_transaction_details->transaction_date = $date;
    //                        $chart_of_account_transaction_details->transaction_date_time = $transaction_date_time;
    //                        $chart_of_account_transaction_details->save();
    //                    }elseif($request->payment_type == 'Card'){
    //                        $cash_chart_of_account_info = ChartOfAccount::where('head_name','Card')->first();
    //                        $chart_of_account_transaction_details = new ChartOfAccountTransactionDetail();
    //                        $chart_of_account_transaction_details->chart_of_account_transaction_id = $chart_of_account_transactions_insert_id;
    //                        $chart_of_account_transaction_details->chart_of_account_id = $cash_chart_of_account_info->id;
    //                        $chart_of_account_transaction_details->chart_of_account_number = $cash_chart_of_account_info->head_code;
    //                        $chart_of_account_transaction_details->chart_of_account_name = 'Card';
    //                        $chart_of_account_transaction_details->chart_of_account_parent_name = $cash_chart_of_account_info->parent_head_name;
    //                        $chart_of_account_transaction_details->chart_of_account_type = $cash_chart_of_account_info->head_type;
    //                        $chart_of_account_transaction_details->debit = $request->paid_amount;
    //                        $chart_of_account_transaction_details->credit = NULL;
    //                        $chart_of_account_transaction_details->description = 'Card In For Sales';
    //                        $chart_of_account_transaction_details->year = $year;
    //                        $chart_of_account_transaction_details->month = $month;
    //                        $chart_of_account_transaction_details->transaction_date = $date;
    //                        $chart_of_account_transaction_details->transaction_date_time = $transaction_date_time;
    //                        $chart_of_account_transaction_details->save();
    //                    }elseif($request->payment_type == 'Bkash'){
    //                        $cash_chart_of_account_info = ChartOfAccount::where('head_name','Bkash')->first();
    //                        $chart_of_account_transaction_details = new ChartOfAccountTransactionDetail();
    //                        $chart_of_account_transaction_details->chart_of_account_transaction_id = $chart_of_account_transactions_insert_id;
    //                        $chart_of_account_transaction_details->chart_of_account_id = $cash_chart_of_account_info->id;
    //                        $chart_of_account_transaction_details->chart_of_account_number = $cash_chart_of_account_info->head_code;
    //                        $chart_of_account_transaction_details->chart_of_account_name = 'Bkash';
    //                        $chart_of_account_transaction_details->chart_of_account_parent_name = $cash_chart_of_account_info->parent_head_name;
    //                        $chart_of_account_transaction_details->chart_of_account_type = $cash_chart_of_account_info->head_type;
    //                        $chart_of_account_transaction_details->debit = $request->paid_amount;
    //                        $chart_of_account_transaction_details->credit = NULL;
    //                        $chart_of_account_transaction_details->description = 'Bkash In For Sales';
    //                        $chart_of_account_transaction_details->year = $year;
    //                        $chart_of_account_transaction_details->month = $month;
    //                        $chart_of_account_transaction_details->transaction_date = $date;
    //                        $chart_of_account_transaction_details->transaction_date_time = $transaction_date_time;
    //                        $chart_of_account_transaction_details->save();
    //                    }elseif($request->payment_type == 'Nogod'){
    //                        $cash_chart_of_account_info = ChartOfAccount::where('head_name','Nogod')->first();
    //                        $chart_of_account_transaction_details = new ChartOfAccountTransactionDetail();
    //                        $chart_of_account_transaction_details->chart_of_account_transaction_id = $chart_of_account_transactions_insert_id;
    //                        $chart_of_account_transaction_details->chart_of_account_id = $cash_chart_of_account_info->id;
    //                        $chart_of_account_transaction_details->chart_of_account_number = $cash_chart_of_account_info->head_code;
    //                        $chart_of_account_transaction_details->chart_of_account_name = 'Nogod';
    //                        $chart_of_account_transaction_details->chart_of_account_parent_name = $cash_chart_of_account_info->parent_head_name;
    //                        $chart_of_account_transaction_details->chart_of_account_type = $cash_chart_of_account_info->head_type;
    //                        $chart_of_account_transaction_details->debit = $request->paid_amount;
    //                        $chart_of_account_transaction_details->credit = NULL;
    //                        $chart_of_account_transaction_details->description = 'Nogod In For Sales';
    //                        $chart_of_account_transaction_details->year = $year;
    //                        $chart_of_account_transaction_details->month = $month;
    //                        $chart_of_account_transaction_details->transaction_date = $date;
    //                        $chart_of_account_transaction_details->transaction_date_time = $transaction_date_time;
    //                        $chart_of_account_transaction_details->save();
    //                    }elseif($request->payment_type == 'Rocket'){
    //                        $cash_chart_of_account_info = ChartOfAccount::where('head_name','Rocket')->first();
    //                        $chart_of_account_transaction_details = new ChartOfAccountTransactionDetail();
    //                        $chart_of_account_transaction_details->chart_of_account_transaction_id = $chart_of_account_transactions_insert_id;
    //                        $chart_of_account_transaction_details->chart_of_account_id = $cash_chart_of_account_info->id;
    //                        $chart_of_account_transaction_details->chart_of_account_number = $cash_chart_of_account_info->head_code;
    //                        $chart_of_account_transaction_details->chart_of_account_name = 'Rocket';
    //                        $chart_of_account_transaction_details->chart_of_account_parent_name = $cash_chart_of_account_info->parent_head_name;
    //                        $chart_of_account_transaction_details->chart_of_account_type = $cash_chart_of_account_info->head_type;
    //                        $chart_of_account_transaction_details->debit = $request->paid_amount;
    //                        $chart_of_account_transaction_details->credit = NULL;
    //                        $chart_of_account_transaction_details->description = 'Rocket In For Sales';
    //                        $chart_of_account_transaction_details->year = $year;
    //                        $chart_of_account_transaction_details->month = $month;
    //                        $chart_of_account_transaction_details->transaction_date = $date;
    //                        $chart_of_account_transaction_details->transaction_date_time = $transaction_date_time;
    //                        $chart_of_account_transaction_details->save();
    //                    }elseif($request->payment_type == 'Upay'){
    //                        $cash_chart_of_account_info = ChartOfAccount::where('head_name','Upay')->first();
    //                        $chart_of_account_transaction_details = new ChartOfAccountTransactionDetail();
    //                        $chart_of_account_transaction_details->chart_of_account_transaction_id = $chart_of_account_transactions_insert_id;
    //                        $chart_of_account_transaction_details->chart_of_account_id = $cash_chart_of_account_info->id;
    //                        $chart_of_account_transaction_details->chart_of_account_number = $cash_chart_of_account_info->head_code;
    //                        $chart_of_account_transaction_details->chart_of_account_name = 'Upay';
    //                        $chart_of_account_transaction_details->chart_of_account_parent_name = $cash_chart_of_account_info->parent_head_name;
    //                        $chart_of_account_transaction_details->chart_of_account_type = $cash_chart_of_account_info->head_type;
    //                        $chart_of_account_transaction_details->debit = $request->paid_amount;
    //                        $chart_of_account_transaction_details->credit = NULL;
    //                        $chart_of_account_transaction_details->description = 'Upay In For Sales';
    //                        $chart_of_account_transaction_details->year = $year;
    //                        $chart_of_account_transaction_details->month = $month;
    //                        $chart_of_account_transaction_details->transaction_date = $date;
    //                        $chart_of_account_transaction_details->transaction_date_time = $transaction_date_time;
    //                        $chart_of_account_transaction_details->save();
    //                    }else{
    //
    //                    }
    //                }
    //            }


//                if($request->payment_type == 'SSL Commerz'){
//                    return response()->json(['success'=>true,'transaction_id' => '','payment_type' => $request->payment_type], $this->successStatus);
//                }else{
//                    return response()->json(['success'=>true,'response' => 'Inserted Successfully.'], $this->successStatus);
//                }
                $response = APIHelpers::createAPIResponse(false,201,'Product Whole Sale Added Successfully.',null);
                return response()->json($response,201);
            }else{
                $response = APIHelpers::createAPIResponse(true,400,'Product Whole Sale Updated Failed.',null);
                return response()->json($response,400);
            }
        } catch (\Exception $e) {
            //return $e->getMessage();
            $response = APIHelpers::createAPIResponse(false,500,'Internal Server Error.',null);
            return response()->json($response,500);
        }
    }

    public function productWholeSaleEdit(Request $request){
        try {
            $this->validate($request, [
                'product_sale_id'=> 'required',
                'party_id'=> 'required',
                //'store_id'=> 'required',
                'warehouse_id'=> 'required',
                'paid_amount'=> 'required',
                'due_amount'=> 'required',
                'total_amount'=> 'required',
                'payment_type'=> 'required',
            ]);

            $date = $request->date;
            $date_time = $date." ".date('h:i:s');
            $add_two_day_date =  date('Y-m-d', strtotime($date. ' + 2 days'));

            $user_id = Auth::user()->id;
            $warehouse_id = $request->warehouse_id;


            // product purchase
            $productSale = ProductSale::find($request->product_sale_id);
            $previous_paid_amount = $productSale->paid_amount;
            $productSale->user_id = $user_id;
            $productSale->party_id = $request->party_id;
            $productSale->warehouse_id = $warehouse_id;
            $productSale->store_id = NULL;
            $productSale->sub_total = $request->sub_total;
            $productSale->discount_type = $request->discount_type ? $request->discount_type : NULL;
            $productSale->discount_percent = $request->discount_percent ? $request->discount_percent : 0;
            $productSale->discount_amount = $request->discount_amount ? $request->discount_amount : 0;
            $productSale->after_discount_amount = $request->after_discount_amount ? $request->after_discount_amount : 0;
            $productSale->miscellaneous_comment = $request->miscellaneous_comment ? $request->miscellaneous_comment : NULL;
            $productSale->miscellaneous_charge = $request->miscellaneous_charge ? $request->miscellaneous_charge : 0;
            $productSale->paid_amount = $request->paid_amount;
            $productSale->due_amount = $request->due_amount;
            $productSale->total_vat_amount = $request->total_vat_amount;
            $productSale->total_amount = $request->total_amount;
            $productSale->update();
            $affectedRows = $productSale->id;

            // discount start
            $sum_total_amount = 0;
            foreach ($request->products as $data) {
                $price = $data['mrp_price'];
                $qty = $data['qty'];
                $sum_total_amount += (float)$price * (float)$qty;
            }
            // discount start

            if($affectedRows)
            {
                foreach ($request->products as $data) {

    //                $product_id = $data['product_id'];
    //                $barcode = Product::where('id',$product_id)->pluck('barcode')->first();
    //
    //                $product_sale_detail_id = $data['product_sale_detail_id'];
    //                // product purchase detail
    //                $product_sale_detail = ProductSaleDetail::find($product_sale_detail_id);
    //                $previous_sale_qty = $product_sale_detail->qty;
    //                $product_sale_detail->product_unit_id = $data['product_unit_id'];
    //                $product_sale_detail->product_brand_id = $data['product_brand_id'] ? $data['product_brand_id'] : NULL;
    //                $product_sale_detail->product_id = $product_id;
    //                $product_sale_detail->qty = $data['qty'];
    //                $product_sale_detail->vat_amount = $data['vat_amount'];
    //                $product_sale_detail->price = $data['mrp_price'];
    //                $product_sale_detail->sub_total = ($data['qty']*$data['mrp_price']) + ($data['qty']*$data['vat_amount']);
    //                $product_sale_detail->barcode = $barcode;
    //                $product_sale_detail->return_last_date = $add_two_day_date;
    //                $product_sale_detail->update();
    //
    //
    //                // product stock
    //                // product stock
    //                $stock_row = Stock::where('warehouse_id',$warehouse_id)->where('product_id',$product_id)->latest()->first();
    //                $current_stock = $stock_row->current_stock;
    //
    //                // warehouse current stock
    //                $update_warehouse_current_stock = WarehouseStoreCurrentStock::where('warehouse_id',$warehouse_id)
    //                    ->where('product_id',$product_id)
    //                    ->first();
    //                $exists_current_stock = $update_warehouse_current_stock->current_stock;
    //
    //                if($stock_row->stock_out != $data['qty']){
    //
    //                    if($data['qty'] > $stock_row->stock_in){
    //                        $new_stock_out = $data['qty'] - $previous_sale_qty;
    //
    //                        $stock = new Stock();
    //                        $stock->ref_id=$request->product_sale_id;
    //                        $stock->user_id=$user_id;
    //                        $stock->product_unit_id= $data['product_unit_id'];
    //                        $stock->product_brand_id= $data['product_brand_id'] ? $data['product_brand_id'] : NULL;
    //                        $stock->product_id= $product_id;
    //                        $stock->stock_type='whole_sale_increase';
    //                        $stock->warehouse_id= $warehouse_id;
    //                        $stock->store_id=NULL;
    //                        $stock->stock_where='warehouse';
    //                        $stock->stock_in_out='stock_out';
    //                        $stock->previous_stock=$current_stock;
    //                        $stock->stock_in=0;
    //                        $stock->stock_out=$new_stock_out;
    //                        $stock->current_stock=$current_stock - $new_stock_out;
    //                        $stock->stock_date=$date;
    //                        $stock->stock_date_time=$date_time;
    //                        $stock->save();
    //
    //                        // warehouse current stock
    //                        $update_warehouse_current_stock->current_stock=$exists_current_stock - $new_stock_out;
    //                        $update_warehouse_current_stock->save();
    //                    }else{
    //                        $new_stock_in = $previous_sale_qty - $data['qty'];
    //
    //                        $stock = new Stock();
    //                        $stock->ref_id=$request->product_sale_id;
    //                        $stock->user_id=$user_id;
    //                        $stock->product_unit_id= $data['product_unit_id'];
    //                        $stock->product_brand_id= $data['product_brand_id'] ? $data['product_brand_id'] : NULL;
    //                        $stock->product_id= $product_id;
    //                        $stock->stock_type='whole_sale_decrease';
    //                        $stock->warehouse_id= $warehouse_id;
    //                        $stock->store_id=NULL;
    //                        $stock->stock_where='warehouse';
    //                        $stock->stock_in_out='stock_in';
    //                        $stock->previous_stock=$current_stock;
    //                        $stock->stock_in=$new_stock_in;
    //                        $stock->stock_out=0;
    //                        $stock->current_stock=$current_stock + $new_stock_in;
    //                        $stock->stock_date=$date;
    //                        $stock->stock_date_time=$date_time;
    //                        $stock->save();
    //
    //                        // warehouse current stock
    //                        $update_warehouse_current_stock->current_stock=$exists_current_stock + $new_stock_in;
    //                        $update_warehouse_current_stock->save();
    //                    }
    //                }





                    // discount start
                    $price = $data['mrp_price'];
                    $qty = $data['qty'];
                    $final_discount_amount = $request->discount_amount;
                    $sub_total_amount = (float)$price * (float)$qty;
                    $amount = $final_discount_amount*$sub_total_amount;
                    $discount = $amount/$sum_total_amount;
                    // discount end

                    // vat and sub total start
                    $after_discount_amount = $sub_total_amount - $discount;
                    $sub_total = $after_discount_amount;
                    // vat and sub total end




                    //previous product
                    if($data['new_status'] == 0){
                        $product_id = $data['product_id'];
                        $barcode = Product::where('id',$product_id)->pluck('barcode')->first();
                        $get_purchase_price = Product::where('id',$product_id)->pluck('purchase_price')->first();

                        $product_sale_detail_id = $data['product_sale_detail_id'];
                        // product purchase detail
                        $product_sale_detail = ProductSaleDetail::find($product_sale_detail_id);
                        $previous_sale_qty = $product_sale_detail->qty;
                        $product_sale_detail->product_unit_id = $data['product_unit_id'];
                        $product_sale_detail->product_brand_id = $data['product_brand_id'] ? $data['product_brand_id'] : NULL;
                        $product_sale_detail->product_id = $product_id;
                        $product_sale_detail->purchase_price = $get_purchase_price;
                        $product_sale_detail->qty = $data['qty'];
                        //$product_sale_detail->vat_amount = $data['vat_amount'];
                        $product_sale_detail->vat_amount = 0;
                        $product_sale_detail->price = $data['mrp_price'];
                        $product_sale_detail->discount = $discount;
                        //$product_sale_detail->sub_total = ($data['qty']*$data['mrp_price']) + ($data['qty']);
                        $product_sale_detail->sub_total = $sub_total;
                        $product_sale_detail->barcode = $barcode;
                        $product_sale_detail->return_last_date = $add_two_day_date;
                        $product_sale_detail->update();


                        // product stock
                        // product stock
                        $stock_row = Stock::where('warehouse_id',$warehouse_id)->where('product_id',$product_id)->latest()->first();
                        $current_stock = $stock_row->current_stock;

                        // warehouse current stock
                        $update_warehouse_current_stock = WarehouseStoreCurrentStock::where('warehouse_id',$warehouse_id)
                            ->where('product_id',$product_id)
                            ->first();
                        $exists_current_stock = $update_warehouse_current_stock->current_stock;

                        if($stock_row->stock_out != $data['qty']){

                            if($data['qty'] > $stock_row->stock_in){
                                $new_stock_out = $data['qty'] - $previous_sale_qty;

                                $stock = new Stock();
                                $stock->ref_id=$request->product_sale_id;
                                $stock->user_id=$user_id;
                                $stock->product_unit_id= $data['product_unit_id'];
                                $stock->product_brand_id= $data['product_brand_id'] ? $data['product_brand_id'] : NULL;
                                $stock->product_id= $product_id;
                                $stock->stock_type='whole_sale_increase';
                                $stock->warehouse_id= $warehouse_id;
                                $stock->store_id=NULL;
                                $stock->stock_where='warehouse';
                                $stock->stock_in_out='stock_out';
                                $stock->previous_stock=$current_stock;
                                $stock->stock_in=0;
                                $stock->stock_out=$new_stock_out;
                                $stock->current_stock=$current_stock - $new_stock_out;
                                $stock->stock_date=$date;
                                $stock->stock_date_time=$date_time;
                                $stock->save();

                                // warehouse current stock
                                $update_warehouse_current_stock->current_stock=$exists_current_stock - $new_stock_out;
                                $update_warehouse_current_stock->save();
                            }else{
                                $new_stock_in = $previous_sale_qty - $data['qty'];

                                $stock = new Stock();
                                $stock->ref_id=$request->product_sale_id;
                                $stock->user_id=$user_id;
                                $stock->product_unit_id= $data['product_unit_id'];
                                $stock->product_brand_id= $data['product_brand_id'] ? $data['product_brand_id'] : NULL;
                                $stock->product_id= $product_id;
                                $stock->stock_type='whole_sale_decrease';
                                $stock->warehouse_id= $warehouse_id;
                                $stock->store_id=NULL;
                                $stock->stock_where='warehouse';
                                $stock->stock_in_out='stock_in';
                                $stock->previous_stock=$current_stock;
                                $stock->stock_in=$new_stock_in;
                                $stock->stock_out=0;
                                $stock->current_stock=$current_stock + $new_stock_in;
                                $stock->stock_date=$date;
                                $stock->stock_date_time=$date_time;
                                $stock->save();

                                // warehouse current stock
                                $update_warehouse_current_stock->current_stock=$exists_current_stock + $new_stock_in;
                                $update_warehouse_current_stock->save();
                            }
                        }
                    }
                    //add new product
                    elseif($data['new_status'] == 1){
                        $product_id =  $data['product_id'];
                        $barcode = Product::where('id',$product_id)->pluck('barcode')->first();

                        // product sale detail
                        $product_sale_detail = new ProductSaleDetail();
                        $product_sale_detail->product_sale_id = $request->product_sale_id;
                        $product_sale_detail->product_unit_id = $data['product_unit_id'];
                        $product_sale_detail->product_brand_id = $data['product_brand_id'] ? $data['product_brand_id'] : NULL;
                        $product_sale_detail->product_id = $product_id;
                        $product_sale_detail->qty = $data['qty'];
                        $product_sale_detail->price = $data['mrp_price'];
                        $product_sale_detail->discount = $discount;
                        $product_sale_detail->vat_amount = 0;
                        //$product_sale_detail->sub_total = ($data['qty']*$data['mrp_price']) + ($data['qty']*$data['vat_amount']);
                        $product_sale_detail->sub_total = $sub_total;
                        $product_sale_detail->barcode = $barcode;
                        $product_sale_detail->sale_date = $date;
                        $product_sale_detail->return_among_day = 2;
                        $product_sale_detail->return_last_date = $add_two_day_date;
                        $product_sale_detail->save();


                        $check_previous_stock = Stock::where('warehouse_id',$warehouse_id)->where('stock_where','warehouse')->where('product_id',$product_id)->latest()->pluck('current_stock')->first();
                        if(!empty($check_previous_stock)){
                            $previous_stock = $check_previous_stock;
                        }else{
                            $previous_stock = 0;
                        }

                        // product stock
                        $stock = new Stock();
                        $stock->ref_id = $request->product_sale_id;
                        $stock->user_id = $user_id;
                        $stock->warehouse_id = $warehouse_id;
                        $stock->store_id = NULL;
                        $stock->product_id = $product_id;
                        $stock->product_unit_id = $data['product_unit_id'];
                        $stock->product_brand_id = $data['product_brand_id'] ? $data['product_brand_id'] : NULL;
                        $stock->stock_type = 'whole_sale';
                        $stock->stock_where = 'warehouse';
                        $stock->stock_in_out = 'stock_out';
                        $stock->previous_stock = $previous_stock;
                        $stock->stock_in = 0;
                        $stock->stock_out = $data['qty'];
                        $stock->current_stock = $previous_stock - $data['qty'];
                        $stock->stock_date = $date;
                        $stock->stock_date_time = $date_time;
                        $stock->save();


                        // warehouse current stock
                        $update_warehouse_current_stock = WarehouseCurrentStock::where('warehouse_id',$warehouse_id)
                            ->where('product_id',$product_id)
                            ->first();

                        $exists_current_stock = $update_warehouse_current_stock->current_stock;
                        $final_warehouse_current_stock = $exists_current_stock - $data['qty'];
                        $update_warehouse_current_stock->current_stock=$final_warehouse_current_stock;
                        $update_warehouse_current_stock->save();
                    }else{
                        return response()->json(['success'=>false,'response'=>'No Found Previous or New Product!'], $this->failStatus);
                    }


                }









                // posting deyer somoi porbe

                // product whole sale e kono paid amount hobe na
                // transaction
    //            $transaction = Transaction::where('ref_id',$request->product_sale_id)->first();
    //            $transaction->user_id = $user_id;
    //            $transaction->warehouse_id = $warehouse_id;
    //            $transaction->store_id = NULL;
    //            $transaction->party_id = $request->party_id;
    //            $transaction->payment_type = $request->payment_type;
    //            $transaction->amount = $request->paid_amount;
    //            $transaction->update();
    //
    //            // payment paid
    //            $payment_collection = PaymentCollection::where('product_sale_id',$request->product_sale_id)->first();
    //            $payment_collection->user_id = $user_id;
    //            $payment_collection->party_id = $request->party_id;
    //            $payment_collection->warehouse_id = $warehouse_id;
    //            $payment_collection->store_id = NULL;
    //            $payment_collection->collection_amount = $request->paid_amount;
    //            $payment_collection->due_amount = $request->due_amount;
    //            $payment_collection->current_collection_amount = $request->paid_amount;
    //            $payment_collection->update();
    //
    //            $new_paid_amount = $request->paid_amount - $previous_paid_amount;
    //
    //            if($request->paid_amount > 0 && $new_paid_amount){
    //                // posting
    //                $month = date('m', strtotime($request->date));
    //                $year = date('Y', strtotime($request->date));
    //                $transaction_date_time = date('Y-m-d H:i:s');
    //
    //                $get_voucher_name = VoucherType::where('id',2)->pluck('name')->first();
    //                $get_voucher_no = ChartOfAccountTransaction::where('voucher_type_id',2)->latest()->pluck('voucher_no')->first();
    //                if(!empty($get_voucher_no)){
    //                    $get_voucher_name_str = $get_voucher_name."-";
    //                    $get_voucher = str_replace($get_voucher_name_str,"",$get_voucher_no);
    //                    $voucher_no = $get_voucher+1;
    //                }else{
    //                    $voucher_no = 8000;
    //                }
    //                $final_voucher_no = $get_voucher_name.'-'.$voucher_no;
    //                $chart_of_account_transactions = new ChartOfAccountTransaction();
    //                $chart_of_account_transactions->ref_id = $request->product_sale_id;
    //                $chart_of_account_transactions->transaction_type = 'Sales';
    //                $chart_of_account_transactions->user_id = $user_id;
    //                $chart_of_account_transactions->store_id = NULL;
    //                $chart_of_account_transactions->voucher_type_id = 2;
    //                $chart_of_account_transactions->voucher_no = $final_voucher_no;
    //                $chart_of_account_transactions->is_approved = 'approved';
    //                $chart_of_account_transactions->transaction_date = $date;
    //                $chart_of_account_transactions->transaction_date_time = $transaction_date_time;
    //                $chart_of_account_transactions->save();
    //                $chart_of_account_transactions_insert_id = $chart_of_account_transactions->id;
    //
    //                if($chart_of_account_transactions_insert_id){
    //
    //                    // sales
    //                    $sales_chart_of_account_info = ChartOfAccount::where('head_name','Sales')->first();
    //                    $chart_of_account_transaction_details = new ChartOfAccountTransactionDetail();
    //                    $chart_of_account_transaction_details->chart_of_account_transaction_id = $chart_of_account_transactions_insert_id;
    //                    $chart_of_account_transaction_details->chart_of_account_id = $sales_chart_of_account_info->id;
    //                    $chart_of_account_transaction_details->chart_of_account_number = $sales_chart_of_account_info->head_code;
    //                    $chart_of_account_transaction_details->chart_of_account_name = 'Sales';
    //                    $chart_of_account_transaction_details->chart_of_account_parent_name = $sales_chart_of_account_info->parent_head_name;
    //                    $chart_of_account_transaction_details->chart_of_account_type = $sales_chart_of_account_info->head_type;
    //                    $chart_of_account_transaction_details->debit = NULL;
    //                    $chart_of_account_transaction_details->credit = $new_paid_amount;
    //                    $chart_of_account_transaction_details->description = 'Income From Sales';
    //                    $chart_of_account_transaction_details->year = $year;
    //                    $chart_of_account_transaction_details->month = $month;
    //                    $chart_of_account_transaction_details->transaction_date = $date;
    //                    $chart_of_account_transaction_details->transaction_date_time = $transaction_date_time;
    //                    $chart_of_account_transaction_details->save();
    //
    //                    // cash
    //                    if($request->payment_type == 'Cash'){
    //                        $cash_chart_of_account_info = ChartOfAccount::where('head_name','Cash')->first();
    //                        $chart_of_account_transaction_details = new ChartOfAccountTransactionDetail();
    //                        $chart_of_account_transaction_details->chart_of_account_transaction_id = $chart_of_account_transactions_insert_id;
    //                        $chart_of_account_transaction_details->chart_of_account_id = $cash_chart_of_account_info->id;
    //                        $chart_of_account_transaction_details->chart_of_account_number = $cash_chart_of_account_info->head_code;
    //                        $chart_of_account_transaction_details->chart_of_account_name = 'Cash';
    //                        $chart_of_account_transaction_details->chart_of_account_parent_name = $cash_chart_of_account_info->parent_head_name;
    //                        $chart_of_account_transaction_details->chart_of_account_type = $cash_chart_of_account_info->head_type;
    //                        $chart_of_account_transaction_details->debit = $new_paid_amount;
    //                        $chart_of_account_transaction_details->credit = NULL;
    //                        $chart_of_account_transaction_details->description = 'Cash In For Sales';
    //                        $chart_of_account_transaction_details->year = $year;
    //                        $chart_of_account_transaction_details->month = $month;
    //                        $chart_of_account_transaction_details->transaction_date = $date;
    //                        $chart_of_account_transaction_details->transaction_date_time = $transaction_date_time;
    //                        $chart_of_account_transaction_details->save();
    //                    }elseif($request->payment_type == 'Check'){
    //                        $cash_chart_of_account_info = ChartOfAccount::where('head_name','Check')->first();
    //                        $chart_of_account_transaction_details = new ChartOfAccountTransactionDetail();
    //                        $chart_of_account_transaction_details->chart_of_account_transaction_id = $chart_of_account_transactions_insert_id;
    //                        $chart_of_account_transaction_details->chart_of_account_id = $cash_chart_of_account_info->id;
    //                        $chart_of_account_transaction_details->chart_of_account_number = $cash_chart_of_account_info->head_code;
    //                        $chart_of_account_transaction_details->chart_of_account_name = 'Check';
    //                        $chart_of_account_transaction_details->chart_of_account_parent_name = $cash_chart_of_account_info->parent_head_name;
    //                        $chart_of_account_transaction_details->chart_of_account_type = $cash_chart_of_account_info->head_type;
    //                        $chart_of_account_transaction_details->debit = $new_paid_amount;
    //                        $chart_of_account_transaction_details->credit = NULL;
    //                        $chart_of_account_transaction_details->description = 'Check In For Sales';
    //                        $chart_of_account_transaction_details->year = $year;
    //                        $chart_of_account_transaction_details->month = $month;
    //                        $chart_of_account_transaction_details->transaction_date = $date;
    //                        $chart_of_account_transaction_details->transaction_date_time = $transaction_date_time;
    //                        $chart_of_account_transaction_details->save();
    //                    }elseif($request->payment_type == 'Card'){
    //                        $cash_chart_of_account_info = ChartOfAccount::where('head_name','Card')->first();
    //                        $chart_of_account_transaction_details = new ChartOfAccountTransactionDetail();
    //                        $chart_of_account_transaction_details->chart_of_account_transaction_id = $chart_of_account_transactions_insert_id;
    //                        $chart_of_account_transaction_details->chart_of_account_id = $cash_chart_of_account_info->id;
    //                        $chart_of_account_transaction_details->chart_of_account_number = $cash_chart_of_account_info->head_code;
    //                        $chart_of_account_transaction_details->chart_of_account_name = 'Card';
    //                        $chart_of_account_transaction_details->chart_of_account_parent_name = $cash_chart_of_account_info->parent_head_name;
    //                        $chart_of_account_transaction_details->chart_of_account_type = $cash_chart_of_account_info->head_type;
    //                        $chart_of_account_transaction_details->debit = $new_paid_amount;
    //                        $chart_of_account_transaction_details->credit = NULL;
    //                        $chart_of_account_transaction_details->description = 'Card In For Sales';
    //                        $chart_of_account_transaction_details->year = $year;
    //                        $chart_of_account_transaction_details->month = $month;
    //                        $chart_of_account_transaction_details->transaction_date = $date;
    //                        $chart_of_account_transaction_details->transaction_date_time = $transaction_date_time;
    //                        $chart_of_account_transaction_details->save();
    //                    }elseif($request->payment_type == 'Bkash'){
    //                        $cash_chart_of_account_info = ChartOfAccount::where('head_name','Bkash')->first();
    //                        $chart_of_account_transaction_details = new ChartOfAccountTransactionDetail();
    //                        $chart_of_account_transaction_details->chart_of_account_transaction_id = $chart_of_account_transactions_insert_id;
    //                        $chart_of_account_transaction_details->chart_of_account_id = $cash_chart_of_account_info->id;
    //                        $chart_of_account_transaction_details->chart_of_account_number = $cash_chart_of_account_info->head_code;
    //                        $chart_of_account_transaction_details->chart_of_account_name = 'Bkash';
    //                        $chart_of_account_transaction_details->chart_of_account_parent_name = $cash_chart_of_account_info->parent_head_name;
    //                        $chart_of_account_transaction_details->chart_of_account_type = $cash_chart_of_account_info->head_type;
    //                        $chart_of_account_transaction_details->debit = $new_paid_amount;
    //                        $chart_of_account_transaction_details->credit = NULL;
    //                        $chart_of_account_transaction_details->description = 'Bkash In For Sales';
    //                        $chart_of_account_transaction_details->year = $year;
    //                        $chart_of_account_transaction_details->month = $month;
    //                        $chart_of_account_transaction_details->transaction_date = $date;
    //                        $chart_of_account_transaction_details->transaction_date_time = $transaction_date_time;
    //                        $chart_of_account_transaction_details->save();
    //                    }elseif($request->payment_type == 'Nogod'){
    //                        $cash_chart_of_account_info = ChartOfAccount::where('head_name','Nogod')->first();
    //                        $chart_of_account_transaction_details = new ChartOfAccountTransactionDetail();
    //                        $chart_of_account_transaction_details->chart_of_account_transaction_id = $chart_of_account_transactions_insert_id;
    //                        $chart_of_account_transaction_details->chart_of_account_id = $cash_chart_of_account_info->id;
    //                        $chart_of_account_transaction_details->chart_of_account_number = $cash_chart_of_account_info->head_code;
    //                        $chart_of_account_transaction_details->chart_of_account_name = 'Nogod';
    //                        $chart_of_account_transaction_details->chart_of_account_parent_name = $cash_chart_of_account_info->parent_head_name;
    //                        $chart_of_account_transaction_details->chart_of_account_type = $cash_chart_of_account_info->head_type;
    //                        $chart_of_account_transaction_details->debit = $new_paid_amount;
    //                        $chart_of_account_transaction_details->credit = NULL;
    //                        $chart_of_account_transaction_details->description = 'Nogod In For Sales';
    //                        $chart_of_account_transaction_details->year = $year;
    //                        $chart_of_account_transaction_details->month = $month;
    //                        $chart_of_account_transaction_details->transaction_date = $date;
    //                        $chart_of_account_transaction_details->transaction_date_time = $transaction_date_time;
    //                        $chart_of_account_transaction_details->save();
    //                    }elseif($request->payment_type == 'Rocket'){
    //                        $cash_chart_of_account_info = ChartOfAccount::where('head_name','Rocket')->first();
    //                        $chart_of_account_transaction_details = new ChartOfAccountTransactionDetail();
    //                        $chart_of_account_transaction_details->chart_of_account_transaction_id = $chart_of_account_transactions_insert_id;
    //                        $chart_of_account_transaction_details->chart_of_account_id = $cash_chart_of_account_info->id;
    //                        $chart_of_account_transaction_details->chart_of_account_number = $cash_chart_of_account_info->head_code;
    //                        $chart_of_account_transaction_details->chart_of_account_name = 'Rocket';
    //                        $chart_of_account_transaction_details->chart_of_account_parent_name = $cash_chart_of_account_info->parent_head_name;
    //                        $chart_of_account_transaction_details->chart_of_account_type = $cash_chart_of_account_info->head_type;
    //                        $chart_of_account_transaction_details->debit = $new_paid_amount;
    //                        $chart_of_account_transaction_details->credit = NULL;
    //                        $chart_of_account_transaction_details->description = 'Rocket In For Sales';
    //                        $chart_of_account_transaction_details->year = $year;
    //                        $chart_of_account_transaction_details->month = $month;
    //                        $chart_of_account_transaction_details->transaction_date = $date;
    //                        $chart_of_account_transaction_details->transaction_date_time = $transaction_date_time;
    //                        $chart_of_account_transaction_details->save();
    //                    }elseif($request->payment_type == 'Upay'){
    //                        $cash_chart_of_account_info = ChartOfAccount::where('head_name','Upay')->first();
    //                        $chart_of_account_transaction_details = new ChartOfAccountTransactionDetail();
    //                        $chart_of_account_transaction_details->chart_of_account_transaction_id = $chart_of_account_transactions_insert_id;
    //                        $chart_of_account_transaction_details->chart_of_account_id = $cash_chart_of_account_info->id;
    //                        $chart_of_account_transaction_details->chart_of_account_number = $cash_chart_of_account_info->head_code;
    //                        $chart_of_account_transaction_details->chart_of_account_name = 'Upay';
    //                        $chart_of_account_transaction_details->chart_of_account_parent_name = $cash_chart_of_account_info->parent_head_name;
    //                        $chart_of_account_transaction_details->chart_of_account_type = $cash_chart_of_account_info->head_type;
    //                        $chart_of_account_transaction_details->debit = $new_paid_amount;
    //                        $chart_of_account_transaction_details->credit = NULL;
    //                        $chart_of_account_transaction_details->description = 'Upay In For Sales';
    //                        $chart_of_account_transaction_details->year = $year;
    //                        $chart_of_account_transaction_details->month = $month;
    //                        $chart_of_account_transaction_details->transaction_date = $date;
    //                        $chart_of_account_transaction_details->transaction_date_time = $transaction_date_time;
    //                        $chart_of_account_transaction_details->save();
    //                    }else{
    //
    //                    }
    //                }
    //            }


                $response = APIHelpers::createAPIResponse(false,200,'Product Whole Sale Updated Successfully.',null);
                return response()->json($response,200);
            }else{
                $response = APIHelpers::createAPIResponse(true,400,'Product Whole Sale Updated Failed.',null);
                return response()->json($response,400);
            }
        } catch (\Exception $e) {
            //return $e->getMessage();
            $response = APIHelpers::createAPIResponse(false,500,'Internal Server Error.',null);
            return response()->json($response,500);
        }
    }

    public function productWholeSaleDelete(Request $request){
        try {
        $check_exists_product_sale = DB::table("product_sales")->where('id',$request->product_sale_id)->pluck('id')->first();
        if($check_exists_product_sale == null){
            return response()->json(['success'=>false,'response'=>'No Product Sale Found!'], $this->failStatus);
        }

        $productSale = ProductSale::find($request->product_sale_id);
            if($productSale){
                $user_id = Auth::user()->id;
                $date = date('Y-m-d');
                $date_time = date('Y-m-d H:i:s');

                $product_sale_details = DB::table('product_sale_details')->where('product_sale_id',$request->product_sale_id)->get();

                if(count($product_sale_details) > 0){
                    foreach ($product_sale_details as $product_sale_detail){
                        // current stock
                        $stock_row = Stock::where('stock_where','warehouse')->where('warehouse_id',$productSale->warehouse_id)->where('product_id',$product_sale_detail->product_id)->latest('id')->first();
                        $current_stock = $stock_row->current_stock;

                        $stock = new Stock();
                        $stock->ref_id=$productSale->id;
                        $stock->user_id=$user_id;
                        $stock->product_unit_id= $product_sale_detail->product_unit_id;
                        $stock->product_brand_id= $product_sale_detail->product_brand_id;
                        $stock->product_id= $product_sale_detail->product_id;
                        $stock->stock_type='whole_sale_delete';
                        $stock->warehouse_id= $productSale->warehouse_id;
                        $stock->store_id=NULL;
                        $stock->stock_where='warehouse';
                        $stock->stock_in_out='stock_in';
                        $stock->previous_stock=$current_stock;
                        $stock->stock_in=$product_sale_detail->qty;
                        $stock->stock_out=0;
                        $stock->current_stock=$current_stock + $product_sale_detail->qty;
                        $stock->stock_date=$date;
                        $stock->stock_date_time=$date_time;
                        $stock->save();


                        $warehouse_current_stock = WarehouseCurrentStock::where('warehouse_id',$productSale->warehouse_id)->where('product_id',$product_sale_detail->product_id)->first();
                        $exists_current_stock = $warehouse_current_stock->current_stock;
                        $warehouse_current_stock->current_stock=$exists_current_stock + $product_sale_detail->qty;
                        $warehouse_current_stock->update();
                    }
                }
            }
            $delete_sale = $productSale->delete();

            //DB::table('stocks')->where('ref_id',$request->product_sale_id)->delete();
            DB::table('product_sale_details')->where('product_sale_id',$request->product_sale_id)->delete();

            // product whole sale e kono paid amount hobe na
            //DB::table('transactions')->where('ref_id',$request->product_sale_id)->delete();
            //DB::table('payment_collections')->where('product_sale_id',$request->product_sale_id)->delete();


            if($delete_sale)
            {
                $response = APIHelpers::createAPIResponse(false,200,'Product Whole Sale Successfully Soft Deleted.',null);
                return response()->json($response,200);
            }else{
                $response = APIHelpers::createAPIResponse(true,400,'Product Whole Sale Updated Failed.',null);
                return response()->json($response,400);
            }
        } catch (\Exception $e) {
            //return $e->getMessage();
            $response = APIHelpers::createAPIResponse(false,500,'Internal Server Error.',null);
            return response()->json($response,500);
        }
    }

    public function productWholeSaleSingleProductRemove(Request $request){
        $check_exists_product_sale = DB::table("product_sales")->where('id',$request->product_sale_id)->pluck('id')->first();
        if($check_exists_product_sale == null){
            return response()->json(['success'=>false,'response'=>'No Product Sale Found!'], $this->failStatus);
        }

        $productSale = ProductSale::find($request->product_sale_id);
        if($productSale) {

            //$discount_amount = $productSale->discount_amount;
            $paid_amount = $productSale->paid_amount;
            //$due_amount = $productSale->due_amount;
            $total_vat_amount = $productSale->total_vat_amount;
            $total_amount = $productSale->total_amount;

            $product_sale_detail = DB::table('product_sale_details')->where('id', $request->product_sale_detail_id)->first();
            $product_unit_id = $product_sale_detail->product_unit_id;
            $product_brand_id = $product_sale_detail->product_brand_id;
            $product_id = $product_sale_detail->product_id;
            $qty = $product_sale_detail->qty;
            //return response()->json(['success'=>true,'response' =>$product_sale_detail], $this->successStatus);
            if ($product_sale_detail) {

                //$remove_discount = $product_sale_detail->discount;
                $remove_vat_amount = $product_sale_detail->vat_amount;
                $remove_sub_total = $product_sale_detail->sub_total;


                //$productSale->discount_amount = $discount_amount - $remove_discount;
                $productSale->discount_amount = $total_vat_amount - $remove_vat_amount;
                $productSale->total_amount = $total_amount - $remove_sub_total;
                $productSale->save();

                // delete single product
                //$product_sale_detail->delete();
                DB::table('product_sale_details')->delete($product_sale_detail->id);
            }



            $user_id = Auth::user()->id;
            $date = date('Y-m-d');
            $date_time = date('Y-m-d H:i:s');
            // current stock
            $stock_row = Stock::where('stock_where','warehouse')->where('warehouse_id',$productSale->warehouse_id)->where('product_id',$product_id)->latest('id')->first();
            $current_stock = $stock_row->current_stock;

            $stock = new Stock();
            $stock->ref_id=$productSale->id;
            $stock->user_id=$user_id;
            $stock->product_unit_id= $product_unit_id;
            $stock->product_brand_id= $product_brand_id;
            $stock->product_id= $product_id;
            $stock->stock_type='whole_sale_delete';
            $stock->warehouse_id= $productSale->warehouse_id;
            $stock->store_id=NULL;
            $stock->stock_where='warehouse';
            $stock->stock_in_out='stock_in';
            $stock->previous_stock=$current_stock;
            $stock->stock_in=$qty;
            $stock->stock_out=0;
            $stock->current_stock=$current_stock + $qty;
            $stock->stock_date=$date;
            $stock->stock_date_time=$date_time;
            $stock->save();

            $warehouse_current_stock = WarehouseCurrentStock::where('warehouse_id',$productSale->warehouse_id)->where('product_id',$product_id)->first();
            $exists_current_stock = $warehouse_current_stock->current_stock;
            $warehouse_current_stock->current_stock=$exists_current_stock + $qty;
            $warehouse_current_stock->update();

            return response()->json(['success'=>true,'response' =>'Single Product Successfully Removed!'], $this->successStatus);
        } else{
            return response()->json(['success'=>false,'response'=>'Sale Not Deleted!'], $this->failStatus);
        }
    }







    public function productPOSSaleList(){
        $product_pos_sales = DB::table('product_sales')
            ->leftJoin('users','product_sales.user_id','users.id')
            ->leftJoin('parties','product_sales.party_id','parties.id')
            ->leftJoin('warehouses','product_sales.warehouse_id','warehouses.id')
            ->leftJoin('stores','product_sales.store_id','stores.id')
            ->where('product_sales.sale_type','pos_sale')
            ->select('product_sales.id','product_sales.invoice_no','product_sales.discount_type','product_sales.discount_amount','product_sales.total_vat_amount','product_sales.total_amount','product_sales.paid_amount','product_sales.due_amount','product_sales.sale_date_time','users.name as user_name','parties.id as customer_id','parties.name as customer_name','warehouses.id as warehouse_id','warehouses.name as warehouse_name','stores.id as store_id','stores.name as store_name','stores.address as store_address','stores.phone')
            ->orderBy('product_sales.id','desc')
            ->get();

        if(count($product_pos_sales) > 0)
        {
            $product_pos_sale_arr = [];
            foreach ($product_pos_sales as $data){
                $payment_type = DB::table('transactions')->where('ref_id',$data->id)->where('transaction_type','pos_sale')->pluck('payment_type')->first();

                $nested_data['id']=$data->id;
                $nested_data['invoice_no']=ucfirst($data->invoice_no);
                $nested_data['discount_type']=$data->discount_type;
                $nested_data['discount_amount']=$data->discount_amount;
                $nested_data['total_vat_amount']=$data->total_vat_amount;
                $nested_data['total_amount']=$data->total_amount;
                $nested_data['paid_amount']=$data->paid_amount;
                $nested_data['due_amount']=$data->due_amount;
                $nested_data['sale_date_time']=$data->sale_date_time;
                $nested_data['user_name']=$data->user_name;
                $nested_data['customer_id']=$data->customer_id;
                $nested_data['customer_name']=$data->customer_name;
                $nested_data['warehouse_id']=$data->warehouse_id;
                $nested_data['warehouse_name']=$data->warehouse_name;
                $nested_data['store_id']=$data->store_id;
                $nested_data['store_name']=$data->store_name;
                $nested_data['store_address']=$data->store_address;
                $nested_data['phone']=$data->phone;
                $nested_data['payment_type']=$payment_type;

                array_push($product_pos_sale_arr,$nested_data);
            }

            $success['product_pos_sales'] =  $product_pos_sale_arr;
            return response()->json(['success'=>true,'response' => $success], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Product Whole Sale List Found!'], $this->failStatus);
        }
    }

    public function productPOSSaleListSearch(Request $request){
        if($request->search){
            $product_pos_sales = DB::table('product_sales')
                ->leftJoin('users','product_sales.user_id','users.id')
                ->leftJoin('parties','product_sales.party_id','parties.id')
                ->leftJoin('warehouses','product_sales.warehouse_id','warehouses.id')
                ->leftJoin('stores','product_sales.store_id','stores.id')
                ->leftJoin('transactions','product_sales.invoice_no','transactions.invoice_no')
                ->where('product_sales.sale_type','pos_sale')
                ->where('product_sales.invoice_no','like','%'.$request->search.'%')
                ->orWhere('parties.name','like','%'.$request->search.'%')
                ->orWhere('parties.phone','like','%'.$request->search.'%')
                ->select('product_sales.id','product_sales.invoice_no','product_sales.sub_total','product_sales.discount_type','product_sales.discount_percent','product_sales.discount_amount','product_sales.total_vat_amount','product_sales.total_amount','product_sales.paid_amount','product_sales.due_amount','product_sales.sale_date_time','users.name as user_name','parties.id as customer_id','parties.name as customer_name','warehouses.id as warehouse_id','warehouses.name as warehouse_name','stores.id as store_id','stores.name as store_name','stores.address as store_address','stores.phone','transactions.payment_type')
                ->orderBy('product_sales.id','desc')
                ->paginate(12);

            if(count($product_pos_sales) > 0)
            {
//            $product_pos_sale_arr = [];
//            foreach ($product_pos_sales as $data){
//                $payment_type = DB::table('transactions')->where('ref_id',$data->id)->where('transaction_type','pos_sale')->pluck('payment_type')->first();
//
//                $nested_data['id']=$data->id;
//                $nested_data['invoice_no']=$data->invoice_no;
//                $nested_data['discount_type']=$data->discount_type;
//                $nested_data['discount_amount']=$data->discount_amount;
//                $nested_data['total_vat_amount']=$data->total_vat_amount;
//                $nested_data['total_amount']=$data->total_amount;
//                $nested_data['paid_amount']=$data->paid_amount;
//                $nested_data['due_amount']=$data->due_amount;
//                $nested_data['sale_date_time']=$data->sale_date_time;
//                $nested_data['user_name']=$data->user_name;
//                $nested_data['customer_id']=$data->customer_id;
//                $nested_data['customer_name']=$data->customer_name;
//                $nested_data['warehouse_id']=$data->warehouse_id;
//                $nested_data['warehouse_name']=$data->warehouse_name;
//                $nested_data['store_id']=$data->store_id;
//                $nested_data['store_name']=$data->store_name;
//                $nested_data['store_address']=$data->store_address;
//                $nested_data['phone']=$data->phone;
//                $nested_data['payment_type']=$payment_type;
//
//                array_push($product_pos_sale_arr,$nested_data);
//            }

                $success['product_pos_sales'] =  $product_pos_sales;
                return response()->json(['success'=>true,'response' => $success], $this->successStatus);
            }else{
                return response()->json(['success'=>false,'response'=>'No Product POS Sale List Found!'], $this->failStatus);
            }
        }else{
            return response()->json(['success'=>false,'response'=>'No Product POS Sale List Found!'], $this->failStatus);
        }

    }

    public function productWholeSaleListSearch(Request $request){
        if($request->search){
            $product_pos_sales = DB::table('product_sales')
                ->leftJoin('users','product_sales.user_id','users.id')
                ->leftJoin('parties','product_sales.party_id','parties.id')
                ->leftJoin('warehouses','product_sales.warehouse_id','warehouses.id')
                ->leftJoin('stores','product_sales.store_id','stores.id')
                ->where('product_sales.sale_type','whole_sale')
                ->where('product_sales.invoice_no','like','%'.$request->search.'%')
                ->orWhere('product_sales.total_amount','like','%'.$request->search.'%')
                ->orWhere('parties.name','like','%'.$request->search.'%')
                ->select('product_sales.id','product_sales.invoice_no','product_sales.discount_type','product_sales.discount_percent','product_sales.discount_amount','product_sales.total_vat_amount','product_sales.after_discount_amount','product_sales.total_amount','product_sales.paid_amount','product_sales.due_amount','product_sales.sale_date_time','users.name as user_name','parties.id as customer_id','parties.name as customer_name','warehouses.id as warehouse_id','warehouses.name as warehouse_name','stores.id as store_id','stores.name as store_name','stores.address as store_address','stores.phone')
                ->orderBy('product_sales.id','desc')
                ->paginate(12);

            if(count($product_pos_sales) > 0)
            {

                $success['product_pos_sales'] =  $product_pos_sales;
                return response()->json(['success'=>true,'response' => $success], $this->successStatus);
            }else{
                return response()->json(['success'=>false,'response'=>'No Product POS Sale List Found!'], $this->failStatus);
            }
        }else{
            return response()->json(['success'=>false,'response'=>'No Product Whole Sale List Found!'], $this->failStatus);
        }

    }

    public function productPOSSaleDetails(Request $request){
        try {
            $product_sale_details = DB::table('product_sales')
                ->join('product_sale_details','product_sales.id','product_sale_details.product_sale_id')
                ->leftJoin('products','product_sale_details.product_id','products.id')
                ->leftJoin('product_units','product_sale_details.product_unit_id','product_units.id')
                ->leftJoin('product_brands','product_sale_details.product_brand_id','product_brands.id')
                ->where('product_sales.id',$request->product_sale_id)
                ->select(
                    'products.id as product_id',
                    'products.name as product_name',
                    'product_units.id as product_unit_id',
                    'product_units.name as product_unit_name',
                    'product_brands.id as product_brand_id',
                    'product_brands.name as product_brand_name',
                    'product_sales.warehouse_id',
                    'product_sales.store_id',
                    'product_sale_details.qty',
                    'product_sale_details.id as product_sale_detail_id',
                    'product_sale_details.price as mrp_price',
                    'product_sale_details.vat_amount'
                )
                ->get();

            $sale_product = [];
            if(count($product_sale_details) > 0){
                foreach ($product_sale_details as $product_sale_detail){
                    $current_stock = warehouseStoreProductCurrentStock($product_sale_detail->warehouse_id,$product_sale_detail->store_id,$product_sale_detail->product_id);

                    $nested_data['product_id']=$product_sale_detail->product_id;
                    $nested_data['product_name']=$product_sale_detail->product_name;
                    $nested_data['product_unit_id']=$product_sale_detail->product_unit_id;
                    $nested_data['product_unit_name']=$product_sale_detail->product_unit_name;
                    $nested_data['product_brand_id']=$product_sale_detail->product_brand_id;
                    $nested_data['product_brand_name']=$product_sale_detail->product_brand_name;
                    $nested_data['qty']=$product_sale_detail->qty;
                    $nested_data['product_sale_detail_id']=$product_sale_detail->product_sale_detail_id;
                    $nested_data['mrp_price']=$product_sale_detail->mrp_price;
                    $nested_data['vat_amount']=$product_sale_detail->vat_amount;
                    $nested_data['current_stock']=$current_stock;

                    array_push($sale_product, $nested_data);
                }
            }

            if($product_sale_details === null){
                $response = APIHelpers::createAPIResponse(true,404,'No Product POS Sale Detail Found.',null);
                return response()->json($response,404);
            }else{
                $response = APIHelpers::createAPIResponse(false,200,'',$sale_product);
                return response()->json($response,200);
            }
        } catch (\Exception $e) {
            //return $e->getMessage();
            $response = APIHelpers::createAPIResponse(false,500,'Internal Server Error.',null);
            return response()->json($response,500);
        }
    }


    public function productPOSSaleCreate(Request $request){
        try {
            $validator = Validator::make($request->all(), [
                'party_id'=> 'required',
                'store_id'=> 'required',
                'paid_amount'=> 'required',
                'due_amount'=> 'required',
                'total_amount'=> 'required',
                'payment_type'=> 'required',
            ]);

            if ($validator->fails()) {
                $response = APIHelpers::createAPIResponse(true,400,$validator->errors(),null);
                return response()->json($response,400);
            }

            $get_invoice_no = ProductSale::latest('id','desc')->pluck('invoice_no')->first();
            if(!empty($get_invoice_no)){
                $get_invoice = str_replace("sale-","",$get_invoice_no);
                $invoice_no = $get_invoice+1;
            }else{
                $invoice_no = 100000;
            }
            $final_invoice = 'sale-'.$invoice_no;

            $date = date('Y-m-d');
            $date_time = date('Y-m-d h:i:s');
            $add_two_day_date =  date('Y-m-d', strtotime("+2 days"));

            $user_id = Auth::user()->id;
            $store_id = $request->store_id;
            $warehouse_id = Store::where('id',$store_id)->pluck('warehouse_id')->first();

            // product purchase
            $productSale = new ProductSale();
            $productSale->invoice_no = $final_invoice;
            $productSale->user_id = $user_id;
            $productSale->party_id = $request->party_id;
            $productSale->warehouse_id = $warehouse_id;
            $productSale->store_id = $store_id;
            $productSale->sub_total = $request->sub_total;
            $productSale->sale_type = 'pos_sale';
            $productSale->discount_type = $request->discount_type ? $request->discount_type : NULL;
            $productSale->discount_percent = $request->discount_percent ? $request->discount_percent : 0;
            $productSale->discount_amount = $request->discount_amount ? $request->discount_amount : 0;
            $productSale->after_discount_amount = $request->after_discount_amount ? $request->after_discount_amount : 0;
            $productSale->paid_amount = $request->paid_amount;
            $productSale->due_amount = $request->due_amount;
            $productSale->total_vat_amount = $request->total_vat_amount;
            $productSale->total_amount = $request->total_amount;
            $productSale->sale_date = $date;
            $productSale->sale_date_time = $date_time;
            $productSale->save();
            $insert_id = $productSale->id;

            // discount start
            $sum_total_amount = 0;
            foreach ($request->products as $data) {
                $price = $data['mrp_price'];
                $qty = $data['qty'];
                $sum_total_amount += (float)$price * (float)$qty;
            }
            // discount start

            if($insert_id)
            {
                // for live testing
                foreach ($request->products as $data) {

                    $product_id =  $data['product_id'];

                    $barcode = Product::where('id',$product_id)->pluck('barcode')->first();
                    $get_purchase_price = Product::where('id',$product_id)->pluck('purchase_price')->first();



                    // discount start
//                    $price = $data['mrp_price'];
//                    $discount_amount = $request->discount_amount;
//                    $total_amount = $request->total_amount;
//
//                    $final_discount_amount = (float)$discount_amount * (float)$price;
//                    $final_total_amount = (float)$discount_amount + (float)$total_amount;
//                    $discount_type = $request->discount_type;
//                    $discount = (float)$final_discount_amount/(float)$final_total_amount;
//                    if($discount_type != NULL){
//                        if($discount_type == 'Flat'){
//                            $discount = round($discount);
//                        }
//                    }
                    // discount end

                    // discount start
                    $price = $data['mrp_price'];
                    $qty = $data['qty'];
                    $final_discount_amount = $request->discount_amount;
                    $sub_total_amount = (float)$price * (float)$qty;
                    $amount = $final_discount_amount*$sub_total_amount;
                    $discount = $amount/$sum_total_amount;
                    // discount end

                    // vat and sub total start
                    $vat_percent = VatPercent();
                    $after_discount_amount = $sub_total_amount - $discount;
                    $vat_amount = $after_discount_amount/$vat_percent;
                    if((!empty($request->total_vat_amount))){
                        $sub_total = $after_discount_amount + $vat_amount;
                    }else{
                        $sub_total = $after_discount_amount;
                    }
                    // vat and sub total end



                    // product purchase detail
                    $product_sale_detail = new ProductSaleDetail();
                    $product_sale_detail->product_sale_id = $insert_id;
                    $product_sale_detail->product_unit_id = $data['product_unit_id'];
                    $product_sale_detail->product_brand_id = $data['product_brand_id'] ? $data['product_brand_id'] : NULL;
                    $product_sale_detail->product_id = $product_id;
                    $product_sale_detail->barcode = $barcode;
                    $product_sale_detail->purchase_price = $get_purchase_price;
                    $product_sale_detail->qty = $data['qty'];
                    $product_sale_detail->discount = $discount;
                    $product_sale_detail->price = $data['mrp_price'];
                    //$product_sale_detail->vat_amount = $data['vat_amount'];
                    $product_sale_detail->vat_amount = $vat_amount;
                    //$product_sale_detail->sub_total = ($data['qty']*$data['mrp_price']) + ($data['qty']*$data['vat_amount']);
                    $product_sale_detail->sub_total = $sub_total;
                    $product_sale_detail->sale_date = $date;
                    $product_sale_detail->return_among_day = 2;
                    $product_sale_detail->return_last_date = $add_two_day_date;
                    $product_sale_detail->save();

                    $check_previous_stock = Stock::where('warehouse_id',$warehouse_id)->where('store_id',$store_id)->where('stock_where','store')->where('product_id',$product_id)->latest()->pluck('current_stock')->first();
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
                    $stock->stock_type = 'pos_sale';
                    $stock->stock_where = 'store';
                    $stock->stock_in_out = 'stock_out';
                    $stock->previous_stock = $previous_stock;
                    $stock->stock_in = 0;
                    $stock->stock_out = $data['qty'];
                    $stock->current_stock = $previous_stock - $data['qty'];
                    $stock->stock_date = $date;
                    $stock->stock_date_time = $date_time;
                    $stock->save();

                    // warehouse store current stock
                    $update_warehouse_store_current_stock = WarehouseStoreCurrentStock::where('warehouse_id',$warehouse_id)
                        ->where('store_id',$store_id)
                        ->where('product_id',$product_id)
                        ->first();

                    $exists_current_stock = $update_warehouse_store_current_stock->current_stock;
                    $final_warehouse_current_stock = $exists_current_stock - $data['qty'];
                    $update_warehouse_store_current_stock->current_stock=$final_warehouse_current_stock;
                    $update_warehouse_store_current_stock->save();
                }

                // transaction
                $transaction = new Transaction();
                $transaction->ref_id = $insert_id;
                $transaction->invoice_no = $final_invoice;
                $transaction->user_id = $user_id;
                $transaction->warehouse_id = $warehouse_id;
                $transaction->store_id = $store_id;
                $transaction->party_id = $request->party_id;
                $transaction->transaction_type = 'pos_sale';
                $transaction->payment_type = $request->payment_type;
                $transaction->amount = $request->total_amount;
                $transaction->transaction_date = $date;
                $transaction->transaction_date_time = $date_time;
                $transaction->save();
                $transaction_id = $transaction->id;

                // payment paid
                $payment_collection = new PaymentCollection();
                $payment_collection->invoice_no = $final_invoice;
                $payment_collection->product_sale_id = $insert_id;
                $payment_collection->user_id = $user_id;
                $payment_collection->party_id = $request->party_id;
                $payment_collection->warehouse_id = $warehouse_id;
                $payment_collection->store_id = $store_id;
                $payment_collection->collection_type = 'Sale';
                $payment_collection->collection_amount = $request->total_amount;
                $payment_collection->due_amount = $request->due_amount;
                $payment_collection->current_collection_amount = $request->total_amount;
                $payment_collection->collection_date = $date;
                $payment_collection->collection_date_time = $date_time;
                $payment_collection->save();






                // posting
                $month = date('m');
                $year = date('Y');
                $transaction_date_time = date('Y-m-d H:i:s');

                $get_voucher_name = VoucherType::where('id',2)->pluck('name')->first();
                $get_voucher_no = ChartOfAccountTransaction::where('voucher_type_id',2)->latest()->pluck('voucher_no')->first();
                if(!empty($get_voucher_no)){
                    $get_voucher_name_str = $get_voucher_name."-";
                    $get_voucher = str_replace($get_voucher_name_str,"",$get_voucher_no);
                    $voucher_no = $get_voucher+1;
                }else{
                    $voucher_no = 8000;
                }
                $final_voucher_no = $get_voucher_name.'-'.$voucher_no;
                $chart_of_account_transactions = new ChartOfAccountTransaction();
                $chart_of_account_transactions->ref_id = $insert_id;
                $chart_of_account_transactions->transaction_type = 'Sales';
                $chart_of_account_transactions->user_id = $user_id;
                $chart_of_account_transactions->warehouse_id = $warehouse_id;
                $chart_of_account_transactions->store_id = $store_id;
                $chart_of_account_transactions->voucher_type_id = 2;
                $chart_of_account_transactions->voucher_no = $final_voucher_no;
                $chart_of_account_transactions->is_approved = 'approved';
                $chart_of_account_transactions->transaction_date = $date;
                $chart_of_account_transactions->transaction_date_time = $transaction_date_time;
                $chart_of_account_transactions->save();
                $chart_of_account_transactions_insert_id = $chart_of_account_transactions->id;

                if($chart_of_account_transactions_insert_id){

                    // sales
                    $sales_chart_of_account_info = ChartOfAccount::where('head_name','Sales')->first();
                    $chart_of_account_transaction_details = new ChartOfAccountTransactionDetail();
                    $chart_of_account_transaction_details->warehouse_id = $warehouse_id;
                    $chart_of_account_transaction_details->store_id = $store_id;
                    $chart_of_account_transaction_details->chart_of_account_transaction_id = $chart_of_account_transactions_insert_id;
                    $chart_of_account_transaction_details->chart_of_account_id = $sales_chart_of_account_info->id;
                    $chart_of_account_transaction_details->chart_of_account_number = $sales_chart_of_account_info->head_code;
                    $chart_of_account_transaction_details->chart_of_account_name = 'Sales';
                    $chart_of_account_transaction_details->chart_of_account_parent_name = $sales_chart_of_account_info->parent_head_name;
                    $chart_of_account_transaction_details->chart_of_account_type = $sales_chart_of_account_info->head_type;
                    $chart_of_account_transaction_details->debit = NULL;
                    $chart_of_account_transaction_details->credit = $request->total_amount;
                    $chart_of_account_transaction_details->description = 'Income From Sales';
                    $chart_of_account_transaction_details->year = $year;
                    $chart_of_account_transaction_details->month = $month;
                    $chart_of_account_transaction_details->transaction_date = $date;
                    $chart_of_account_transaction_details->transaction_date_time = $transaction_date_time;
                    $chart_of_account_transaction_details->save();

                    // cash
                    if($request->payment_type == 'Cash'){
                        $cash_chart_of_account_info = ChartOfAccount::where('head_name','Cash')->first();
                        $chart_of_account_transaction_details = new ChartOfAccountTransactionDetail();
                        $chart_of_account_transaction_details->warehouse_id = $warehouse_id;
                        $chart_of_account_transaction_details->store_id = $store_id;
                        $chart_of_account_transaction_details->chart_of_account_transaction_id = $chart_of_account_transactions_insert_id;
                        $chart_of_account_transaction_details->chart_of_account_id = $cash_chart_of_account_info->id;
                        $chart_of_account_transaction_details->chart_of_account_number = $cash_chart_of_account_info->head_code;
                        $chart_of_account_transaction_details->chart_of_account_name = 'Cash';
                        $chart_of_account_transaction_details->chart_of_account_parent_name = $cash_chart_of_account_info->parent_head_name;
                        $chart_of_account_transaction_details->chart_of_account_type = $cash_chart_of_account_info->head_type;
                        $chart_of_account_transaction_details->debit = $request->total_amount;
                        $chart_of_account_transaction_details->credit = NULL;
                        $chart_of_account_transaction_details->description = 'Cash In For Sales';
                        $chart_of_account_transaction_details->year = $year;
                        $chart_of_account_transaction_details->month = $month;
                        $chart_of_account_transaction_details->transaction_date = $date;
                        $chart_of_account_transaction_details->transaction_date_time = $transaction_date_time;
                        $chart_of_account_transaction_details->save();
                    }elseif($request->payment_type == 'Check'){
                        $cash_chart_of_account_info = ChartOfAccount::where('head_name','Check')->first();
                        $chart_of_account_transaction_details = new ChartOfAccountTransactionDetail();
                        $chart_of_account_transaction_details->warehouse_id = $warehouse_id;
                        $chart_of_account_transaction_details->store_id = $store_id;
                        $chart_of_account_transaction_details->chart_of_account_transaction_id = $chart_of_account_transactions_insert_id;
                        $chart_of_account_transaction_details->chart_of_account_id = $cash_chart_of_account_info->id;
                        $chart_of_account_transaction_details->chart_of_account_number = $cash_chart_of_account_info->head_code;
                        $chart_of_account_transaction_details->chart_of_account_name = 'Check';
                        $chart_of_account_transaction_details->chart_of_account_parent_name = $cash_chart_of_account_info->parent_head_name;
                        $chart_of_account_transaction_details->chart_of_account_type = $cash_chart_of_account_info->head_type;
                        $chart_of_account_transaction_details->debit = $request->total_amount;
                        $chart_of_account_transaction_details->credit = NULL;
                        $chart_of_account_transaction_details->description = 'Check In For Sales';
                        $chart_of_account_transaction_details->year = $year;
                        $chart_of_account_transaction_details->month = $month;
                        $chart_of_account_transaction_details->transaction_date = $date;
                        $chart_of_account_transaction_details->transaction_date_time = $transaction_date_time;
                        $chart_of_account_transaction_details->save();
                    }elseif($request->payment_type == 'Card'){
                        $cash_chart_of_account_info = ChartOfAccount::where('head_name','Card')->first();
                        $chart_of_account_transaction_details = new ChartOfAccountTransactionDetail();
                        $chart_of_account_transaction_details->warehouse_id = $warehouse_id;
                        $chart_of_account_transaction_details->store_id = $store_id;
                        $chart_of_account_transaction_details->chart_of_account_transaction_id = $chart_of_account_transactions_insert_id;
                        $chart_of_account_transaction_details->chart_of_account_id = $cash_chart_of_account_info->id;
                        $chart_of_account_transaction_details->chart_of_account_number = $cash_chart_of_account_info->head_code;
                        $chart_of_account_transaction_details->chart_of_account_name = 'Card';
                        $chart_of_account_transaction_details->chart_of_account_parent_name = $cash_chart_of_account_info->parent_head_name;
                        $chart_of_account_transaction_details->chart_of_account_type = $cash_chart_of_account_info->head_type;
                        $chart_of_account_transaction_details->debit = $request->total_amount;
                        $chart_of_account_transaction_details->credit = NULL;
                        $chart_of_account_transaction_details->description = 'Card In For Sales';
                        $chart_of_account_transaction_details->year = $year;
                        $chart_of_account_transaction_details->month = $month;
                        $chart_of_account_transaction_details->transaction_date = $date;
                        $chart_of_account_transaction_details->transaction_date_time = $transaction_date_time;
                        $chart_of_account_transaction_details->save();
                    }elseif($request->payment_type == 'Bkash'){
                        $cash_chart_of_account_info = ChartOfAccount::where('head_name','Bkash')->first();
                        $chart_of_account_transaction_details = new ChartOfAccountTransactionDetail();
                        $chart_of_account_transaction_details->warehouse_id = $warehouse_id;
                        $chart_of_account_transaction_details->store_id = $store_id;
                        $chart_of_account_transaction_details->chart_of_account_transaction_id = $chart_of_account_transactions_insert_id;
                        $chart_of_account_transaction_details->chart_of_account_id = $cash_chart_of_account_info->id;
                        $chart_of_account_transaction_details->chart_of_account_number = $cash_chart_of_account_info->head_code;
                        $chart_of_account_transaction_details->chart_of_account_name = 'Bkash';
                        $chart_of_account_transaction_details->chart_of_account_parent_name = $cash_chart_of_account_info->parent_head_name;
                        $chart_of_account_transaction_details->chart_of_account_type = $cash_chart_of_account_info->head_type;
                        $chart_of_account_transaction_details->debit = $request->total_amount;
                        $chart_of_account_transaction_details->credit = NULL;
                        $chart_of_account_transaction_details->description = 'Bkash In For Sales';
                        $chart_of_account_transaction_details->year = $year;
                        $chart_of_account_transaction_details->month = $month;
                        $chart_of_account_transaction_details->transaction_date = $date;
                        $chart_of_account_transaction_details->transaction_date_time = $transaction_date_time;
                        $chart_of_account_transaction_details->save();
                    }elseif($request->payment_type == 'Nogod'){
                        $cash_chart_of_account_info = ChartOfAccount::where('head_name','Nogod')->first();
                        $chart_of_account_transaction_details = new ChartOfAccountTransactionDetail();
                        $chart_of_account_transaction_details->warehouse_id = $warehouse_id;
                        $chart_of_account_transaction_details->store_id = $store_id;
                        $chart_of_account_transaction_details->chart_of_account_transaction_id = $chart_of_account_transactions_insert_id;
                        $chart_of_account_transaction_details->chart_of_account_id = $cash_chart_of_account_info->id;
                        $chart_of_account_transaction_details->chart_of_account_number = $cash_chart_of_account_info->head_code;
                        $chart_of_account_transaction_details->chart_of_account_name = 'Nogod';
                        $chart_of_account_transaction_details->chart_of_account_parent_name = $cash_chart_of_account_info->parent_head_name;
                        $chart_of_account_transaction_details->chart_of_account_type = $cash_chart_of_account_info->head_type;
                        $chart_of_account_transaction_details->debit = $request->total_amount;
                        $chart_of_account_transaction_details->credit = NULL;
                        $chart_of_account_transaction_details->description = 'Nogod In For Sales';
                        $chart_of_account_transaction_details->year = $year;
                        $chart_of_account_transaction_details->month = $month;
                        $chart_of_account_transaction_details->transaction_date = $date;
                        $chart_of_account_transaction_details->transaction_date_time = $transaction_date_time;
                        $chart_of_account_transaction_details->save();
                    }elseif($request->payment_type == 'Rocket'){
                        $cash_chart_of_account_info = ChartOfAccount::where('head_name','Rocket')->first();
                        $chart_of_account_transaction_details = new ChartOfAccountTransactionDetail();
                        $chart_of_account_transaction_details->warehouse_id = $warehouse_id;
                        $chart_of_account_transaction_details->store_id = $store_id;
                        $chart_of_account_transaction_details->chart_of_account_transaction_id = $chart_of_account_transactions_insert_id;
                        $chart_of_account_transaction_details->chart_of_account_id = $cash_chart_of_account_info->id;
                        $chart_of_account_transaction_details->chart_of_account_number = $cash_chart_of_account_info->head_code;
                        $chart_of_account_transaction_details->chart_of_account_name = 'Rocket';
                        $chart_of_account_transaction_details->chart_of_account_parent_name = $cash_chart_of_account_info->parent_head_name;
                        $chart_of_account_transaction_details->chart_of_account_type = $cash_chart_of_account_info->head_type;
                        $chart_of_account_transaction_details->debit = $request->total_amount;
                        $chart_of_account_transaction_details->credit = NULL;
                        $chart_of_account_transaction_details->description = 'Rocket In For Sales';
                        $chart_of_account_transaction_details->year = $year;
                        $chart_of_account_transaction_details->month = $month;
                        $chart_of_account_transaction_details->transaction_date = $date;
                        $chart_of_account_transaction_details->transaction_date_time = $transaction_date_time;
                        $chart_of_account_transaction_details->save();
                    }elseif($request->payment_type == 'Upay'){
                        $cash_chart_of_account_info = ChartOfAccount::where('head_name','Upay')->first();
                        $chart_of_account_transaction_details = new ChartOfAccountTransactionDetail();
                        $chart_of_account_transaction_details->warehouse_id = $warehouse_id;
                        $chart_of_account_transaction_details->store_id = $store_id;
                        $chart_of_account_transaction_details->chart_of_account_transaction_id = $chart_of_account_transactions_insert_id;
                        $chart_of_account_transaction_details->chart_of_account_id = $cash_chart_of_account_info->id;
                        $chart_of_account_transaction_details->chart_of_account_number = $cash_chart_of_account_info->head_code;
                        $chart_of_account_transaction_details->chart_of_account_name = 'Upay';
                        $chart_of_account_transaction_details->chart_of_account_parent_name = $cash_chart_of_account_info->parent_head_name;
                        $chart_of_account_transaction_details->chart_of_account_type = $cash_chart_of_account_info->head_type;
                        $chart_of_account_transaction_details->debit = $request->total_amount;
                        $chart_of_account_transaction_details->credit = NULL;
                        $chart_of_account_transaction_details->description = 'Upay In For Sales';
                        $chart_of_account_transaction_details->year = $year;
                        $chart_of_account_transaction_details->month = $month;
                        $chart_of_account_transaction_details->transaction_date = $date;
                        $chart_of_account_transaction_details->transaction_date_time = $transaction_date_time;
                        $chart_of_account_transaction_details->save();
                    }else{

                    }
                }






//                if($request->payment_type == 'SSL Commerz'){
//                    $product_pos_sale = DB::table('product_sales')
//                        ->leftJoin('users','product_sales.user_id','users.id')
//                        ->leftJoin('parties','product_sales.party_id','parties.id')
//                        ->leftJoin('warehouses','product_sales.warehouse_id','warehouses.id')
//                        ->leftJoin('stores','product_sales.store_id','stores.id')
//                        ->leftJoin('transactions','product_sales.invoice_no','transactions.invoice_no')
//                        ->where('product_sales.sale_type','pos_sale')
//                        ->where('product_sales.id',$insert_id)
//                        ->select('product_sales.id','product_sales.invoice_no','product_sales.sub_total','product_sales.discount_type','product_sales.discount_amount','product_sales.total_vat_amount','product_sales.total_amount','product_sales.paid_amount','product_sales.due_amount','product_sales.sale_date_time','users.name as user_name','parties.id as customer_id','parties.name as customer_name','parties.phone as customer_phone','warehouses.id as warehouse_id','warehouses.name as warehouse_name','stores.id as store_id','stores.name as store_name','stores.address as store_address','stores.phone as phone','transactions.payment_type')
//                        ->first();
//
//                    return response()->json(['success'=>true,'transaction_id' => $transaction_id,'payment_type' => $request->payment_type,'product_pos_sale' => $product_pos_sale], $this->successStatus);
//                }else{
//
//                    $product_pos_sale = DB::table('product_sales')
//                        ->leftJoin('users','product_sales.user_id','users.id')
//                        ->leftJoin('parties','product_sales.party_id','parties.id')
//                        ->leftJoin('warehouses','product_sales.warehouse_id','warehouses.id')
//                        ->leftJoin('stores','product_sales.store_id','stores.id')
//                        ->leftJoin('transactions','product_sales.invoice_no','transactions.invoice_no')
//                        ->where('product_sales.sale_type','pos_sale')
//                        ->where('product_sales.id',$insert_id)
//                        ->select('product_sales.id','product_sales.invoice_no','product_sales.sub_total','product_sales.discount_type','product_sales.discount_amount','product_sales.total_vat_amount','product_sales.total_amount','product_sales.paid_amount','product_sales.due_amount','product_sales.sale_date_time','users.name as user_name','parties.id as customer_id','parties.name as customer_name','parties.phone as customer_phone','warehouses.id as warehouse_id','warehouses.name as warehouse_name','stores.id as store_id','stores.name as store_name','stores.address as store_address','stores.phone as phone','transactions.payment_type')
//                        ->first();
//
//                    return response()->json(['success'=>true,'product_pos_sale' => $product_pos_sale], $this->successStatus);
//                }


                $product_pos_sale = DB::table('product_sales')
                    ->leftJoin('users','product_sales.user_id','users.id')
                    ->leftJoin('parties','product_sales.party_id','parties.id')
                    ->leftJoin('warehouses','product_sales.warehouse_id','warehouses.id')
                    ->leftJoin('stores','product_sales.store_id','stores.id')
                    ->leftJoin('transactions','product_sales.invoice_no','transactions.invoice_no')
                    ->where('product_sales.sale_type','pos_sale')
                    ->where('product_sales.id',$insert_id)
                    ->select('product_sales.id','product_sales.invoice_no','product_sales.sub_total','product_sales.discount_type','product_sales.discount_amount','product_sales.total_vat_amount','product_sales.total_amount','product_sales.paid_amount','product_sales.due_amount','product_sales.sale_date_time','users.name as user_name','parties.id as customer_id','parties.name as customer_name','parties.phone as customer_phone','warehouses.id as warehouse_id','warehouses.name as warehouse_name','stores.id as store_id','stores.name as store_name','stores.address as store_address','stores.phone as phone','transactions.payment_type')
                    ->first();

                $response = APIHelpers::createAPIResponse(false,201,'Product POS Sale Added Successfully.',$product_pos_sale);
                return response()->json($response,201);
            }else{
                $response = APIHelpers::createAPIResponse(true,400,'Product POS Sale Sdded Failed.',null);
                return response()->json($response,400);
            }
        } catch (\Exception $e) {
            //return $e->getMessage();
            $response = APIHelpers::createAPIResponse(false,500,'Internal Server Error.',null);
            return response()->json($response,500);
        }
    }

    public function productPOSSaleEdit(Request $request){
        try {

            $validator = Validator::make($request->all(), [
                'product_sale_id'=> 'required',
                'party_id'=> 'required',
                'store_id'=> 'required',
                'paid_amount'=> 'required',
                'due_amount'=> 'required',
                'total_amount'=> 'required',
                'payment_type'=> 'required',
            ]);

            if ($validator->fails()) {
                $response = APIHelpers::createAPIResponse(true,400,$validator->errors(),null);
                return response()->json($response,400);
            }

            $user_id = Auth::user()->id;
            $date = date('Y-m-d');
            $date_time = date('Y-m-d H:i:s');
            $store_id = $request->store_id;
            $warehouse_id = Store::where('id',$store_id)->pluck('warehouse_id')->first();


            // product purchase
            $productSale = ProductSale::find($request->product_sale_id);
            $previous_paid_amount = $productSale ->paid_amount;
            $productSale->user_id = $user_id;
            $productSale->party_id = $request->party_id;
            $productSale->warehouse_id = $warehouse_id;
            $productSale->store_id = $store_id;
            $productSale->sub_total = $request->sub_total;
            $productSale->discount_type = $request->discount_type ? $request->discount_type : NULL;
            $productSale->discount_percent = $request->discount_percent ? $request->discount_percent : 0;
            $productSale->discount_amount = $request->discount_amount ? $request->discount_amount : 0;
            $productSale->after_discount_amount = $request->after_discount_amount ? $request->after_discount_amount : 0;
            $productSale->paid_amount = $request->paid_amount;
            $productSale->due_amount = $request->due_amount;
            $productSale->total_vat_amount = $request->total_vat_amount;
            $productSale->total_amount = $request->total_amount;
            $productSale->update();
            $affectedRows = $productSale->id;

            // discount
            $sum_total_amount = 0;
            foreach ($request->products as $data) {
                $price = $data['mrp_price'];
                $qty = $data['qty'];
                $sum_total_amount += (float)$price * (float)$qty;
            }
            // discount

            if($affectedRows)
            {
                foreach ($request->products as $data) {
                    $product_id = $data['product_id'];
                    $barcode = Product::where('id',$product_id)->pluck('barcode')->first();
                    $get_purchase_price = Product::where('id',$product_id)->pluck('purchase_price')->first();

                    // discount start
//                    $price = $data['mrp_price'];
//                    $discount_amount = $request->discount_amount;
//                    $total_amount = $request->total_amount;
//
//                    $final_discount_amount = (float)$discount_amount * (float)$price;
//                    $final_total_amount = (float)$discount_amount + (float)$total_amount;
//                    $discount_type = $request->discount_type;
//                    $discount = (float)$final_discount_amount/(float)$final_total_amount;
//                    if($discount_type != NULL){
//                        if($discount_type == 'Flat'){
//                            $discount = round($discount);
//                        }
//                    }
                    // discount end

                    // discount start
                    $price = $data['mrp_price'];
                    $qty = $data['qty'];
                    $final_discount_amount = $request->discount_amount;
                    $sub_total_amount = (float)$price * (float)$qty;
                    $amount = $final_discount_amount*$sub_total_amount;
                    $discount = $amount/$sum_total_amount;
                    // discount end

                    // vat and sub total start
                    $vat_percent = VatPercent();
                    $after_discount_amount = $sub_total_amount - $discount;
                    $vat_amount = $after_discount_amount/$vat_percent;
                    if((!empty($request->total_vat_amount))){
                        $sub_total = $after_discount_amount + $vat_amount;
                    }else{
                        $sub_total = $after_discount_amount;
                    }

                    // vat and sub total end

                    $product_sale_detail_id = $data['product_sale_detail_id'];
                    // product purchase detail
                    $purchase_sale_detail = ProductSaleDetail::find($product_sale_detail_id);
                    $previous_sale_qty = $purchase_sale_detail->qty;
                    $purchase_sale_detail->product_unit_id = $data['product_unit_id'];
                    $purchase_sale_detail->product_brand_id = $data['product_brand_id'] ? $data['product_brand_id'] : NULL;
                    $purchase_sale_detail->product_id = $product_id;
                    $purchase_sale_detail->purchase_price = $get_purchase_price;
                    $purchase_sale_detail->qty = $data['qty'];
                    $purchase_sale_detail->discount = $discount;
                    $purchase_sale_detail->price = $data['mrp_price'];
                    //$purchase_sale_detail->vat_amount = $data['vat_amount'];
                    $purchase_sale_detail->vat_amount = (!empty($request->total_vat_amount)) ? $vat_amount : 0;
                    //$purchase_sale_detail->sub_total = ($data['qty']*$data['mrp_price']) + ($data['qty']*$data['vat_amount']);
                    $purchase_sale_detail->sub_total = $sub_total;
                    $purchase_sale_detail->barcode = $barcode;
                    $purchase_sale_detail->update();


                    // product stock
                    $stock_row = Stock::where('warehouse_id',$warehouse_id)->where('store_id',$store_id)->where('product_id',$product_id)->latest()->first();
                    $current_stock = $stock_row->current_stock;

                    // warehouse store current stock
                    $update_warehouse_store_current_stock = WarehouseStoreCurrentStock::where('warehouse_id',$warehouse_id)
                        ->where('store_id',$store_id)
                        ->where('product_id',$product_id)
                        ->first();
                    $exists_current_stock = $update_warehouse_store_current_stock->current_stock;

                    if($stock_row->stock_out != $data['qty']){

                        if($data['qty'] > $stock_row->stock_in){
                            $new_stock_out = $data['qty'] - $previous_sale_qty;

                            $stock = new Stock();
                            $stock->ref_id=$request->product_sale_id;
                            $stock->user_id=$user_id;
                            $stock->product_unit_id= $data['product_unit_id'];
                            $stock->product_brand_id= $data['product_brand_id'] ? $data['product_brand_id'] : NULL;
                            $stock->product_id= $product_id;
                            $stock->stock_type='pos_sale_increase';
                            $stock->warehouse_id= $warehouse_id;
                            $stock->store_id=$store_id;
                            $stock->stock_where='store';
                            $stock->stock_in_out='stock_out';
                            $stock->previous_stock=$current_stock;
                            $stock->stock_in=0;
                            $stock->stock_out=$new_stock_out;
                            $stock->current_stock=$current_stock - $new_stock_out;
                            $stock->stock_date=$date;
                            $stock->stock_date_time=$date_time;
                            $stock->save();

                            // warehouse current stock
                            $update_warehouse_store_current_stock->current_stock=$exists_current_stock - $new_stock_out;
                            $update_warehouse_store_current_stock->save();
                        }else{
                            $new_stock_in = $previous_sale_qty - $data['qty'];

                            $stock = new Stock();
                            $stock->ref_id=$request->product_sale_id;
                            $stock->user_id=$user_id;
                            $stock->product_unit_id= $data['product_unit_id'];
                            $stock->product_brand_id= $data['product_brand_id'] ? $data['product_brand_id'] : NULL;
                            $stock->product_id= $product_id;
                            $stock->stock_type='pos_sale_decrease';
                            $stock->warehouse_id= $warehouse_id;
                            $stock->store_id=$store_id;
                            $stock->stock_where='store';
                            $stock->stock_in_out='stock_in';
                            $stock->previous_stock=$current_stock;
                            $stock->stock_in=$new_stock_in;
                            $stock->stock_out=0;
                            $stock->current_stock=$current_stock + $new_stock_in;
                            $stock->stock_date=$date;
                            $stock->stock_date_time=$date_time;
                            $stock->save();

                            // warehouse current stock
                            $update_warehouse_store_current_stock->current_stock=$exists_current_stock + $new_stock_in;
                            $update_warehouse_store_current_stock->save();
                        }
                    }
                }

                // transaction
                $transaction = Transaction::where('ref_id',$request->product_sale_id)->first();
                $transaction->user_id = $user_id;
                $transaction->warehouse_id = $warehouse_id;
                $transaction->store_id = $store_id;
                $transaction->party_id = $request->party_id;
                $transaction->payment_type = $request->payment_type;
                $transaction->amount = $request->total_amount;
                $transaction->update();

                // payment paid
                $payment_collection = PaymentCollection::where('product_sale_id',$request->product_sale_id)->first();
                $payment_collection->user_id = $user_id;
                $payment_collection->party_id = $request->party_id;
                $payment_collection->warehouse_id = $warehouse_id;
                $payment_collection->store_id = $store_id;
                $payment_collection->collection_amount = $request->total_amount;
                $payment_collection->due_amount = $request->due_amount;
                $payment_collection->current_collection_amount = $request->total_amount;
                $payment_collection->update();


                // posting update
    //            $month = date('m', strtotime($request->date));
    //            $year = date('Y', strtotime($request->date));
    //            $transaction_date_time = date('Y-m-d H:i:s');
    //
    //            $chart_of_account_transactions = ChartOfAccountTransaction::where('ref_id',$request->product_sale_id)->where('transaction_type','Sales')->first();
    //
    //            $chart_of_account_transactions_id = $chart_of_account_transactions->id;
    //
    //            $chart_of_account_transactions->user_id = $user_id;
    //            $chart_of_account_transactions->store_id = $store_id;
    //            $chart_of_account_transactions->transaction_date = $date;
    //            $chart_of_account_transactions->transaction_date_time = $transaction_date_time;
    //            $chart_of_account_transactions->update();
    //            $affectedRows = $chart_of_account_transactions->id;
    //
    //            if($affectedRows){
    //                // sales
    //                $chart_of_account_transaction_details = ChartOfAccountTransactionDetail::where('chart_of_account_transaction_id',$chart_of_account_transactions_id)->where('chart_of_account_name','Sales')->first();
    //                $chart_of_account_transaction_details->credit = $request->paid_amount;
    //                $chart_of_account_transaction_details->year = $year;
    //                $chart_of_account_transaction_details->month = $month;
    //                $chart_of_account_transaction_details->transaction_date = $date;
    //                $chart_of_account_transaction_details->transaction_date_time = $transaction_date_time;
    //                $chart_of_account_transaction_details->save();
    //
    //                // cash
    //                if($request->payment_type == 'Cash'){
    //                    $chart_of_account_transaction_details = ChartOfAccountTransactionDetail::where('chart_of_account_transaction_id',$chart_of_account_transactions_id)->where('chart_of_account_name','Cash')->first();
    //                    $chart_of_account_transaction_details->debit = $request->paid_amount;
    //                    $chart_of_account_transaction_details->year = $year;
    //                    $chart_of_account_transaction_details->month = $month;
    //                    $chart_of_account_transaction_details->transaction_date = $date;
    //                    $chart_of_account_transaction_details->transaction_date_time = $transaction_date_time;
    //                    $chart_of_account_transaction_details->save();
    //                }elseif($request->payment_type == 'Check'){
    //                    $chart_of_account_transaction_details = ChartOfAccountTransactionDetail::where('chart_of_account_transaction_id',$chart_of_account_transactions_id)->where('chart_of_account_name','Check')->first();
    //                    $chart_of_account_transaction_details->debit = $request->paid_amount;
    //                    $chart_of_account_transaction_details->year = $year;
    //                    $chart_of_account_transaction_details->month = $month;
    //                    $chart_of_account_transaction_details->transaction_date = $date;
    //                    $chart_of_account_transaction_details->transaction_date_time = $transaction_date_time;
    //                    $chart_of_account_transaction_details->save();
    //                }elseif($request->payment_type == 'Card'){
    //                    $chart_of_account_transaction_details = ChartOfAccountTransactionDetail::where('chart_of_account_transaction_id',$chart_of_account_transactions_id)->where('chart_of_account_name','Card')->first();
    //                    $chart_of_account_transaction_details->debit = $request->paid_amount;
    //                    $chart_of_account_transaction_details->year = $year;
    //                    $chart_of_account_transaction_details->month = $month;
    //                    $chart_of_account_transaction_details->transaction_date = $date;
    //                    $chart_of_account_transaction_details->transaction_date_time = $transaction_date_time;
    //                    $chart_of_account_transaction_details->save();
    //                }elseif($request->payment_type == 'Bkash'){
    //                    $chart_of_account_transaction_details = ChartOfAccountTransactionDetail::where('chart_of_account_transaction_id',$chart_of_account_transactions_id)->where('chart_of_account_name','Bkash')->first();
    //                    $chart_of_account_transaction_details->debit = $request->paid_amount;
    //                    $chart_of_account_transaction_details->year = $year;
    //                    $chart_of_account_transaction_details->month = $month;
    //                    $chart_of_account_transaction_details->transaction_date = $date;
    //                    $chart_of_account_transaction_details->transaction_date_time = $transaction_date_time;
    //                    $chart_of_account_transaction_details->save();
    //                }elseif($request->payment_type == 'Nogod'){
    //                    $chart_of_account_transaction_details = ChartOfAccountTransactionDetail::where('chart_of_account_transaction_id',$chart_of_account_transactions_id)->where('chart_of_account_name','Nogod')->first();
    //                    $chart_of_account_transaction_details->debit = $request->paid_amount;
    //                    $chart_of_account_transaction_details->year = $year;
    //                    $chart_of_account_transaction_details->month = $month;
    //                    $chart_of_account_transaction_details->transaction_date = $date;
    //                    $chart_of_account_transaction_details->transaction_date_time = $transaction_date_time;
    //                    $chart_of_account_transaction_details->save();
    //                }elseif($request->payment_type == 'Rocket'){
    //                    $chart_of_account_transaction_details = ChartOfAccountTransactionDetail::where('chart_of_account_transaction_id',$chart_of_account_transactions_id)->where('chart_of_account_name','Rocket')->first();
    //                    $chart_of_account_transaction_details->debit = $request->paid_amount;
    //                    $chart_of_account_transaction_details->year = $year;
    //                    $chart_of_account_transaction_details->month = $month;
    //                    $chart_of_account_transaction_details->transaction_date = $date;
    //                    $chart_of_account_transaction_details->transaction_date_time = $transaction_date_time;
    //                    $chart_of_account_transaction_details->save();
    //                }elseif($request->payment_type == 'Upay'){
    //                    $chart_of_account_transaction_details = ChartOfAccountTransactionDetail::where('chart_of_account_transaction_id',$chart_of_account_transactions_id)->where('chart_of_account_name','Upay')->first();
    //                    $chart_of_account_transaction_details->debit = $request->paid_amount;
    //                    $chart_of_account_transaction_details->year = $year;
    //                    $chart_of_account_transaction_details->month = $month;
    //                    $chart_of_account_transaction_details->transaction_date = $date;
    //                    $chart_of_account_transaction_details->transaction_date_time = $transaction_date_time;
    //                    $chart_of_account_transaction_details->save();
    //                }else{
    //
    //                }
    //            }

                $new_paid_amount = $request->paid_amount - $previous_paid_amount;

                if($request->paid_amount > 0 && $new_paid_amount){
                    // posting
                    $month = date('m', strtotime($request->date));
                    $year = date('Y', strtotime($request->date));
                    $transaction_date_time = date('Y-m-d H:i:s');

                    $get_voucher_name = VoucherType::where('id',2)->pluck('name')->first();
                    $get_voucher_no = ChartOfAccountTransaction::where('voucher_type_id',2)->latest()->pluck('voucher_no')->first();
                    if(!empty($get_voucher_no)){
                        $get_voucher_name_str = $get_voucher_name."-";
                        $get_voucher = str_replace($get_voucher_name_str,"",$get_voucher_no);
                        $voucher_no = $get_voucher+1;
                    }else{
                        $voucher_no = 8000;
                    }
                    $final_voucher_no = $get_voucher_name.'-'.$voucher_no;
                    $chart_of_account_transactions = new ChartOfAccountTransaction();
                    $chart_of_account_transactions->ref_id = $request->product_sale_id;
                    $chart_of_account_transactions->transaction_type = 'Sales';
                    $chart_of_account_transactions->user_id = $user_id;
                    $chart_of_account_transactions->store_id = NULL;
                    $chart_of_account_transactions->voucher_type_id = 2;
                    $chart_of_account_transactions->voucher_no = $final_voucher_no;
                    $chart_of_account_transactions->is_approved = 'approved';
                    $chart_of_account_transactions->transaction_date = $date;
                    $chart_of_account_transactions->transaction_date_time = $transaction_date_time;
                    $chart_of_account_transactions->save();
                    $chart_of_account_transactions_insert_id = $chart_of_account_transactions->id;

                    if($chart_of_account_transactions_insert_id){

                        // sales
                        $sales_chart_of_account_info = ChartOfAccount::where('head_name','Sales')->first();
                        $chart_of_account_transaction_details = new ChartOfAccountTransactionDetail();
                        $chart_of_account_transaction_details->chart_of_account_transaction_id = $chart_of_account_transactions_insert_id;
                        $chart_of_account_transaction_details->chart_of_account_id = $sales_chart_of_account_info->id;
                        $chart_of_account_transaction_details->chart_of_account_number = $sales_chart_of_account_info->head_code;
                        $chart_of_account_transaction_details->chart_of_account_name = 'Sales';
                        $chart_of_account_transaction_details->chart_of_account_parent_name = $sales_chart_of_account_info->parent_head_name;
                        $chart_of_account_transaction_details->chart_of_account_type = $sales_chart_of_account_info->head_type;
                        $chart_of_account_transaction_details->debit = NULL;
                        $chart_of_account_transaction_details->credit = $new_paid_amount;
                        $chart_of_account_transaction_details->description = 'Income From Sales';
                        $chart_of_account_transaction_details->year = $year;
                        $chart_of_account_transaction_details->month = $month;
                        $chart_of_account_transaction_details->transaction_date = $date;
                        $chart_of_account_transaction_details->transaction_date_time = $transaction_date_time;
                        $chart_of_account_transaction_details->save();

                        // cash
                        if($request->payment_type == 'Cash'){
                            $cash_chart_of_account_info = ChartOfAccount::where('head_name','Cash')->first();
                            $chart_of_account_transaction_details = new ChartOfAccountTransactionDetail();
                            $chart_of_account_transaction_details->chart_of_account_transaction_id = $chart_of_account_transactions_insert_id;
                            $chart_of_account_transaction_details->chart_of_account_id = $cash_chart_of_account_info->id;
                            $chart_of_account_transaction_details->chart_of_account_number = $cash_chart_of_account_info->head_code;
                            $chart_of_account_transaction_details->chart_of_account_name = 'Cash';
                            $chart_of_account_transaction_details->chart_of_account_parent_name = $cash_chart_of_account_info->parent_head_name;
                            $chart_of_account_transaction_details->chart_of_account_type = $cash_chart_of_account_info->head_type;
                            $chart_of_account_transaction_details->debit = $new_paid_amount;
                            $chart_of_account_transaction_details->credit = NULL;
                            $chart_of_account_transaction_details->description = 'Cash In For Sales';
                            $chart_of_account_transaction_details->year = $year;
                            $chart_of_account_transaction_details->month = $month;
                            $chart_of_account_transaction_details->transaction_date = $date;
                            $chart_of_account_transaction_details->transaction_date_time = $transaction_date_time;
                            $chart_of_account_transaction_details->save();
                        }elseif($request->payment_type == 'Check'){
                            $cash_chart_of_account_info = ChartOfAccount::where('head_name','Check')->first();
                            $chart_of_account_transaction_details = new ChartOfAccountTransactionDetail();
                            $chart_of_account_transaction_details->chart_of_account_transaction_id = $chart_of_account_transactions_insert_id;
                            $chart_of_account_transaction_details->chart_of_account_id = $cash_chart_of_account_info->id;
                            $chart_of_account_transaction_details->chart_of_account_number = $cash_chart_of_account_info->head_code;
                            $chart_of_account_transaction_details->chart_of_account_name = 'Check';
                            $chart_of_account_transaction_details->chart_of_account_parent_name = $cash_chart_of_account_info->parent_head_name;
                            $chart_of_account_transaction_details->chart_of_account_type = $cash_chart_of_account_info->head_type;
                            $chart_of_account_transaction_details->debit = $new_paid_amount;
                            $chart_of_account_transaction_details->credit = NULL;
                            $chart_of_account_transaction_details->description = 'Check In For Sales';
                            $chart_of_account_transaction_details->year = $year;
                            $chart_of_account_transaction_details->month = $month;
                            $chart_of_account_transaction_details->transaction_date = $date;
                            $chart_of_account_transaction_details->transaction_date_time = $transaction_date_time;
                            $chart_of_account_transaction_details->save();
                        }elseif($request->payment_type == 'Card'){
                            $cash_chart_of_account_info = ChartOfAccount::where('head_name','Card')->first();
                            $chart_of_account_transaction_details = new ChartOfAccountTransactionDetail();
                            $chart_of_account_transaction_details->chart_of_account_transaction_id = $chart_of_account_transactions_insert_id;
                            $chart_of_account_transaction_details->chart_of_account_id = $cash_chart_of_account_info->id;
                            $chart_of_account_transaction_details->chart_of_account_number = $cash_chart_of_account_info->head_code;
                            $chart_of_account_transaction_details->chart_of_account_name = 'Card';
                            $chart_of_account_transaction_details->chart_of_account_parent_name = $cash_chart_of_account_info->parent_head_name;
                            $chart_of_account_transaction_details->chart_of_account_type = $cash_chart_of_account_info->head_type;
                            $chart_of_account_transaction_details->debit = $new_paid_amount;
                            $chart_of_account_transaction_details->credit = NULL;
                            $chart_of_account_transaction_details->description = 'Card In For Sales';
                            $chart_of_account_transaction_details->year = $year;
                            $chart_of_account_transaction_details->month = $month;
                            $chart_of_account_transaction_details->transaction_date = $date;
                            $chart_of_account_transaction_details->transaction_date_time = $transaction_date_time;
                            $chart_of_account_transaction_details->save();
                        }elseif($request->payment_type == 'Bkash'){
                            $cash_chart_of_account_info = ChartOfAccount::where('head_name','Bkash')->first();
                            $chart_of_account_transaction_details = new ChartOfAccountTransactionDetail();
                            $chart_of_account_transaction_details->chart_of_account_transaction_id = $chart_of_account_transactions_insert_id;
                            $chart_of_account_transaction_details->chart_of_account_id = $cash_chart_of_account_info->id;
                            $chart_of_account_transaction_details->chart_of_account_number = $cash_chart_of_account_info->head_code;
                            $chart_of_account_transaction_details->chart_of_account_name = 'Bkash';
                            $chart_of_account_transaction_details->chart_of_account_parent_name = $cash_chart_of_account_info->parent_head_name;
                            $chart_of_account_transaction_details->chart_of_account_type = $cash_chart_of_account_info->head_type;
                            $chart_of_account_transaction_details->debit = $new_paid_amount;
                            $chart_of_account_transaction_details->credit = NULL;
                            $chart_of_account_transaction_details->description = 'Bkash In For Sales';
                            $chart_of_account_transaction_details->year = $year;
                            $chart_of_account_transaction_details->month = $month;
                            $chart_of_account_transaction_details->transaction_date = $date;
                            $chart_of_account_transaction_details->transaction_date_time = $transaction_date_time;
                            $chart_of_account_transaction_details->save();
                        }elseif($request->payment_type == 'Nogod'){
                            $cash_chart_of_account_info = ChartOfAccount::where('head_name','Nogod')->first();
                            $chart_of_account_transaction_details = new ChartOfAccountTransactionDetail();
                            $chart_of_account_transaction_details->chart_of_account_transaction_id = $chart_of_account_transactions_insert_id;
                            $chart_of_account_transaction_details->chart_of_account_id = $cash_chart_of_account_info->id;
                            $chart_of_account_transaction_details->chart_of_account_number = $cash_chart_of_account_info->head_code;
                            $chart_of_account_transaction_details->chart_of_account_name = 'Nogod';
                            $chart_of_account_transaction_details->chart_of_account_parent_name = $cash_chart_of_account_info->parent_head_name;
                            $chart_of_account_transaction_details->chart_of_account_type = $cash_chart_of_account_info->head_type;
                            $chart_of_account_transaction_details->debit = $new_paid_amount;
                            $chart_of_account_transaction_details->credit = NULL;
                            $chart_of_account_transaction_details->description = 'Nogod In For Sales';
                            $chart_of_account_transaction_details->year = $year;
                            $chart_of_account_transaction_details->month = $month;
                            $chart_of_account_transaction_details->transaction_date = $date;
                            $chart_of_account_transaction_details->transaction_date_time = $transaction_date_time;
                            $chart_of_account_transaction_details->save();
                        }elseif($request->payment_type == 'Rocket'){
                            $cash_chart_of_account_info = ChartOfAccount::where('head_name','Rocket')->first();
                            $chart_of_account_transaction_details = new ChartOfAccountTransactionDetail();
                            $chart_of_account_transaction_details->chart_of_account_transaction_id = $chart_of_account_transactions_insert_id;
                            $chart_of_account_transaction_details->chart_of_account_id = $cash_chart_of_account_info->id;
                            $chart_of_account_transaction_details->chart_of_account_number = $cash_chart_of_account_info->head_code;
                            $chart_of_account_transaction_details->chart_of_account_name = 'Rocket';
                            $chart_of_account_transaction_details->chart_of_account_parent_name = $cash_chart_of_account_info->parent_head_name;
                            $chart_of_account_transaction_details->chart_of_account_type = $cash_chart_of_account_info->head_type;
                            $chart_of_account_transaction_details->debit = $new_paid_amount;
                            $chart_of_account_transaction_details->credit = NULL;
                            $chart_of_account_transaction_details->description = 'Rocket In For Sales';
                            $chart_of_account_transaction_details->year = $year;
                            $chart_of_account_transaction_details->month = $month;
                            $chart_of_account_transaction_details->transaction_date = $date;
                            $chart_of_account_transaction_details->transaction_date_time = $transaction_date_time;
                            $chart_of_account_transaction_details->save();
                        }elseif($request->payment_type == 'Upay'){
                            $cash_chart_of_account_info = ChartOfAccount::where('head_name','Upay')->first();
                            $chart_of_account_transaction_details = new ChartOfAccountTransactionDetail();
                            $chart_of_account_transaction_details->chart_of_account_transaction_id = $chart_of_account_transactions_insert_id;
                            $chart_of_account_transaction_details->chart_of_account_id = $cash_chart_of_account_info->id;
                            $chart_of_account_transaction_details->chart_of_account_number = $cash_chart_of_account_info->head_code;
                            $chart_of_account_transaction_details->chart_of_account_name = 'Upay';
                            $chart_of_account_transaction_details->chart_of_account_parent_name = $cash_chart_of_account_info->parent_head_name;
                            $chart_of_account_transaction_details->chart_of_account_type = $cash_chart_of_account_info->head_type;
                            $chart_of_account_transaction_details->debit = $new_paid_amount;
                            $chart_of_account_transaction_details->credit = NULL;
                            $chart_of_account_transaction_details->description = 'Upay In For Sales';
                            $chart_of_account_transaction_details->year = $year;
                            $chart_of_account_transaction_details->month = $month;
                            $chart_of_account_transaction_details->transaction_date = $date;
                            $chart_of_account_transaction_details->transaction_date_time = $transaction_date_time;
                            $chart_of_account_transaction_details->save();
                        }else{

                        }
                    }
                }


                $response = APIHelpers::createAPIResponse(false,200,'Product POS Sale Updated Successfully.',null);
                return response()->json($response,200);
            }else{
                $response = APIHelpers::createAPIResponse(true,400,'Product POS Sale Updated Failed.',null);
                return response()->json($response,400);
            }
        } catch (\Exception $e) {
            //return $e->getMessage();
            $response = APIHelpers::createAPIResponse(false,500,'Internal Server Error.',null);
            return response()->json($response,500);
        }
    }

    public function productPOSSaleDelete(Request $request){
        try {
            $check_exists_product_sale = DB::table("product_sales")->where('id',$request->product_sale_id)->pluck('id')->first();
            if($check_exists_product_sale == null){
                $response = APIHelpers::createAPIResponse(true,404,'No Product POS Sale Found.',null);
                return response()->json($response,404);
            }

            $productSale = ProductSale::find($request->product_sale_id);
            if($productSale){
                $user_id = Auth::user()->id;
                $date = date('Y-m-d');
                $date_time = date('Y-m-d H:i:s');

                $product_sale_details = DB::table('product_sale_details')->where('product_sale_id',$request->product_sale_id)->get();

                if(count($product_sale_details) > 0){
                    foreach ($product_sale_details as $product_sale_detail){
                        // current stock
                        $stock_row = Stock::where('stock_where','store')->where('warehouse_id',$productSale->warehouse_id)->where('product_id',$product_sale_detail->product_id)->latest('id')->first();
                        $current_stock = $stock_row->current_stock;

                        $stock = new Stock();
                        $stock->ref_id=$productSale->id;
                        $stock->user_id=$user_id;
                        $stock->product_unit_id= $product_sale_detail->product_unit_id;
                        $stock->product_brand_id= $product_sale_detail->product_brand_id;
                        $stock->product_id= $product_sale_detail->product_id;
                        $stock->stock_type='pos_sale_delete';
                        $stock->warehouse_id= $productSale->warehouse_id;
                        $stock->store_id=$productSale->store_id;
                        $stock->stock_where='store';
                        $stock->stock_in_out='stock_in';
                        $stock->previous_stock=$current_stock;
                        $stock->stock_in=$product_sale_detail->qty;
                        $stock->stock_out=0;
                        $stock->current_stock=$current_stock + $product_sale_detail->qty;
                        $stock->stock_date=$date;
                        $stock->stock_date_time=$date_time;
                        $stock->save();


                        $warehouse_store_current_stock = WarehouseStoreCurrentStock::where('warehouse_id',$productSale->warehouse_id)->where('store_id',$productSale->store_id)->where('product_id',$product_sale_detail->product_id)->first();
                        $exists_current_stock = $warehouse_store_current_stock->current_stock;
                        $warehouse_store_current_stock->current_stock=$exists_current_stock + $product_sale_detail->qty;
                        $warehouse_store_current_stock->update();
                    }
                }
            }
            $delete_sale = $productSale->delete();

            DB::table('product_sale_details')->where('product_sale_id',$request->product_sale_id)->delete();
            //DB::table('stocks')->where('ref_id',$request->product_sale_id)->delete();
            DB::table('transactions')->where('ref_id',$request->product_sale_id)->delete();
            DB::table('payment_collections')->where('product_sale_id',$request->product_sale_id)->delete();

            if($delete_sale)
            {
                $response = APIHelpers::createAPIResponse(false,200,'Product POS Sale Successfully Soft Deleted.',null);
                return response()->json($response,200);
            }else{
                $response = APIHelpers::createAPIResponse(true,400,'Product POS Sale Updated Failed.',null);
                return response()->json($response,400);
            }
        } catch (\Exception $e) {
            //return $e->getMessage();
            $response = APIHelpers::createAPIResponse(false,500,'Internal Server Error.',null);
            return response()->json($response,500);
        }
    }

    public function productPOSSaleSingleProductRemove(Request $request){
        $check_exists_product_sale = DB::table("product_sales")->where('id',$request->product_sale_id)->pluck('id')->first();
        if($check_exists_product_sale == null){
            return response()->json(['success'=>false,'response'=>'No Product Sale Found!'], $this->failStatus);
        }

        $productSale = ProductSale::find($request->product_sale_id);
        if($productSale) {

            //$discount_amount = $productSale->discount_amount;
            $paid_amount = $productSale->paid_amount;
            //$due_amount = $productSale->due_amount;
            $total_vat_amount = $productSale->total_vat_amount;
            $total_amount = $productSale->total_amount;

            $product_sale_detail = DB::table('product_sale_details')->where('id', $request->product_sale_detail_id)->first();
            $product_unit_id = $product_sale_detail->product_unit_id;
            $product_brand_id = $product_sale_detail->product_brand_id;
            $product_id = $product_sale_detail->product_id;
            $qty = $product_sale_detail->qty;

            if ($product_sale_detail) {

                //$remove_discount = $product_sale_detail->discount;
                $remove_vat_amount = $product_sale_detail->vat_amount;
                $remove_sub_total = $product_sale_detail->sub_total;

                //$productSale->discount_amount = $discount_amount - $remove_discount;
                $productSale->discount_amount = $total_vat_amount - $remove_vat_amount;
                $productSale->total_amount = $total_amount - $remove_sub_total;
                $productSale->save();

                // delete single product
                //$product_sale_detail->delete();
                DB::table('product_sale_details')->delete($product_sale_detail->id);
            }



            $user_id = Auth::user()->id;
            $date = date('Y-m-d');
            $date_time = date('Y-m-d H:i:s');
            // current stock
            $stock_row = Stock::where('stock_where','warehouse')->where('warehouse_id',$productSale->warehouse_id)->where('product_id',$product_id)->latest('id')->first();
            $current_stock = $stock_row->current_stock;

            $stock = new Stock();
            $stock->ref_id=$productSale->id;
            $stock->user_id=$user_id;
            $stock->product_unit_id= $product_unit_id;
            $stock->product_brand_id= $product_brand_id;
            $stock->product_id= $product_id;
            $stock->stock_type='pos_sale_delete';
            $stock->warehouse_id= $productSale->warehouse_id;
            $stock->store_id=NULL;
            $stock->stock_where='warehouse';
            $stock->stock_in_out='stock_in';
            $stock->previous_stock=$current_stock;
            $stock->stock_in=$qty;
            $stock->stock_out=0;
            $stock->current_stock=$current_stock + $qty;
            $stock->stock_date=$date;
            $stock->stock_date_time=$date_time;
            $stock->save();

            $warehouse_current_stock = WarehouseCurrentStock::where('warehouse_id',$productSale->warehouse_id)->where('product_id',$product_id)->first();
            $exists_current_stock = $warehouse_current_stock->current_stock;
            $warehouse_current_stock->current_stock=$exists_current_stock + $qty;
            $warehouse_current_stock->update();

            return response()->json(['success'=>true,'response' =>'Single Product Successfully Removed!'], $this->successStatus);
        } else{
            return response()->json(['success'=>false,'response'=>'Sale Not Deleted!'], $this->failStatus);
        }
    }

    // product sale invoice list
    public function productSaleInvoiceList(){
        $product_sale_invoices = DB::table('product_sales')
            ->select('id','invoice_no','total_amount')
            ->get();

        if($product_sale_invoices)
        {
            $success['product_sale_invoices'] =  $product_sale_invoices;
            return response()->json(['success'=>true,'response' => $success], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Product Sale List Found!'], $this->failStatus);
        }
    }

    // product sale invoice list pagination
    public function productSaleInvoiceListPagination(){
        $product_sale_invoices = DB::table('product_sales')
            ->select('id','invoice_no','total_amount')
            ->paginate(12);

        if($product_sale_invoices)
        {
            $success['product_sale_invoices'] =  $product_sale_invoices;
            return response()->json(['success'=>true,'response' => $success], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Product Sale List Found!'], $this->failStatus);
        }
    }

    // product sale invoice list pagination
    public function productSaleInvoiceListPaginationWithSearch(Request $request){
        if($request->search){
            $product_sale_invoices = DB::table('product_sales')
                ->where('invoice_no','like','%'.$request->search.'%')
                ->select('id','invoice_no','total_amount')
                ->paginate(12);
        }else{
            $product_sale_invoices = DB::table('product_sales')
                ->select('id','invoice_no','total_amount')
                ->paginate(12);
        }

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
                ->select(
                    'products.id as product_id',
                    'products.name as product_name',
                    'product_units.id as product_unit_id',
                    'product_units.name as product_unit_name',
                    'product_brands.id as product_brand_id',
                    'product_brands.name as product_brand_name',
                    'product_sale_details.qty',
                    'product_sale_details.qty as current_qty',
                    'product_sale_details.id as product_sale_detail_id',
                    'product_sale_details.price as mrp_price',
                    'product_sale_details.sale_date',
                    'product_sale_details.return_among_day'
                )
                ->get();

            $product_sale_arr = [];
            if(count($product_sale_details) > 0){
                foreach ($product_sale_details as $product_sale_detail){
                    $already_return_qty = DB::table('product_sale_return_details')
                        ->where('pro_sale_detail_id',$product_sale_detail->product_sale_detail_id)
                        ->where('product_id',$product_sale_detail->product_id)
                        ->pluck('qty')
                        ->first();

                    $nested_data['product_id'] = $product_sale_detail->product_id;
                    $nested_data['product_name'] = $product_sale_detail->product_name;
                    $nested_data['product_unit_id'] = $product_sale_detail->product_unit_id;
                    $nested_data['product_unit_name'] = $product_sale_detail->product_unit_name;
                    $nested_data['product_brand_id'] = $product_sale_detail->product_brand_id;
                    $nested_data['product_brand_name'] = $product_sale_detail->product_brand_name;
                    //$nested_data['qty'] = $product_sale_detail->qty;
                    $nested_data['sale_qty'] = $product_sale_detail->qty;
                    //$nested_data['current_qty'] = $product_sale_detail->current_qty;
                    $nested_data['already_return_qty'] = $already_return_qty;
                    $nested_data['exists_return_qty'] = $product_sale_detail->qty - $already_return_qty;
                    $nested_data['product_sale_detail_id'] = $product_sale_detail->product_purchase_detail_id;
                    $nested_data['price'] = $product_sale_detail->price;
                    $nested_data['mrp_price'] = $product_sale_detail->mrp_price;
                    $nested_data['sale_date'] = $product_sale_detail->sale_date;
                    $nested_data['return_among_day'] = $product_sale_detail->return_among_day;

                    array_push($product_sale_arr,$nested_data);

                }
            }

            $success['product_sales'] = $product_sales;
            $success['product_sale_details'] = $product_sale_arr;
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
                $nested_data['invoice_no']=ucfirst($data->invoice_no);
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
                $get_purchase_price = Product::where('id',$product_id)->pluck('purchase_price')->first();

                // product purchase detail
                $purchase_sale_return_detail = new ProductSaleReturnDetail();
                $purchase_sale_return_detail->pro_sale_return_id = $insert_id;
                $purchase_sale_return_detail->pro_sale_detail_id = $data['product_sale_detail_id'];
                $purchase_sale_return_detail->product_unit_id = $data['product_unit_id'];
                $purchase_sale_return_detail->product_brand_id = $data['product_brand_id'] ? $data['product_brand_id'] : NULL;
                $purchase_sale_return_detail->product_id = $product_id;
                $purchase_sale_return_detail->purchase_price = $get_purchase_price;
                $purchase_sale_return_detail->barcode = $barcode;
                $purchase_sale_return_detail->qty = $data['qty'];
                $purchase_sale_return_detail->price = $data['mrp_price'];
                $purchase_sale_return_detail->sub_total = $data['qty']*$data['mrp_price'];
                $purchase_sale_return_detail->save();

                $sale_type = ProductSale::where('invoice_no',$request->sale_invoice_no)->pluck('sale_type')->first();

                //$check_previous_stock = Stock::where('warehouse_id',$warehouse_id)->where('store_id',$store_id)->where('product_id',$product_id)->latest('id','desc')->pluck('current_stock')->first();
                if($sale_type == 'pos_sale') {
                    $check_previous_stock = Stock::where('warehouse_id', $warehouse_id)->where('store_id', $store_id)->where('stock_where', 'store')->where('product_id', $product_id)->latest()->pluck('current_stock')->first();
                }

                if($sale_type == 'whole_sale') {
                    $check_previous_stock = Stock::where('warehouse_id', $warehouse_id)->where('store_id', NULL)->where('stock_where', 'store')->where('product_id', $product_id)->latest()->pluck('current_stock')->first();
                }
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

                if($sale_type == 'pos_sale'){
                    // warehouse store current stock
                    $update_warehouse_store_current_stock = WarehouseStoreCurrentStock::where('warehouse_id',$warehouse_id)
                        ->where('store_id',$store_id)
                        ->where('product_id',$product_id)
                        ->first();

                    $exists_current_stock = $update_warehouse_store_current_stock->current_stock;
                    $final_warehouse_store_current_stock = $exists_current_stock + $data['qty'];
                    $update_warehouse_store_current_stock->current_stock=$final_warehouse_store_current_stock;
                    $update_warehouse_store_current_stock->save();
                }

                if($sale_type == 'whole_sale'){
                    // warehouse current stock
                    $update_warehouse_current_stock = WarehouseCurrentStock::where('warehouse_id',$warehouse_id)
                        ->where('product_id',$product_id)
                        ->first();

                    $exists_current_stock = $update_warehouse_current_stock->current_stock;
                    $final_warehouse_current_stock = $exists_current_stock + $data['qty'];
                    $update_warehouse_current_stock->current_stock=$final_warehouse_current_stock;
                    $update_warehouse_current_stock->save();
                }



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


                    // posting
                    $month = date('m', strtotime($date));
                    $year = date('Y', strtotime($date));
                    $transaction_date_time = date('Y-m-d H:i:s');

                    $get_voucher_name = VoucherType::where('id',7)->pluck('name')->first();
                    $get_voucher_no = ChartOfAccountTransaction::where('voucher_type_id',7)->latest()->pluck('voucher_no')->first();
                    if(!empty($get_voucher_no)){
                        $get_voucher_name_str = $get_voucher_name."-";
                        $get_voucher = str_replace($get_voucher_name_str,"",$get_voucher_no);
                        $voucher_no = $get_voucher+1;
                    }else{
                        $voucher_no = 8000;
                    }
                    $final_voucher_no = $get_voucher_name.'-'.$voucher_no;
                    $chart_of_account_transactions = new ChartOfAccountTransaction();
                    $chart_of_account_transactions->warehouse_id = $warehouse_id;
                    $chart_of_account_transactions->store_id = $store_id;
                    $chart_of_account_transactions->ref_id = $insert_id;
                    $chart_of_account_transactions->transaction_type = 'Sales Return';
                    $chart_of_account_transactions->user_id = $user_id;
                    $chart_of_account_transactions->voucher_type_id = 7;
                    $chart_of_account_transactions->voucher_no = $final_voucher_no;
                    $chart_of_account_transactions->is_approved = 'approved';
                    $chart_of_account_transactions->transaction_date = $date;
                    $chart_of_account_transactions->transaction_date_time = $transaction_date_time;
                    $chart_of_account_transactions->save();
                    $chart_of_account_transactions_insert_id = $chart_of_account_transactions->id;

                    if($chart_of_account_transactions_insert_id){

                        // sales Return
                        $sales_chart_of_account_info = ChartOfAccount::where('head_name','Sales Return')->first();
                        $chart_of_account_transaction_details = new ChartOfAccountTransactionDetail();
                        $chart_of_account_transaction_details->warehouse_id = $warehouse_id;
                        $chart_of_account_transaction_details->store_id = $store_id;
                        $chart_of_account_transaction_details->chart_of_account_transaction_id = $chart_of_account_transactions_insert_id;
                        $chart_of_account_transaction_details->chart_of_account_id = $sales_chart_of_account_info->id;
                        $chart_of_account_transaction_details->chart_of_account_number = $sales_chart_of_account_info->head_code;
                        $chart_of_account_transaction_details->chart_of_account_name = 'Sales Return';
                        $chart_of_account_transaction_details->chart_of_account_parent_name = $sales_chart_of_account_info->parent_head_name;
                        $chart_of_account_transaction_details->chart_of_account_type = $sales_chart_of_account_info->head_type;
                        $chart_of_account_transaction_details->debit = $request->total_amount;
                        $chart_of_account_transaction_details->credit = NULL;
                        $chart_of_account_transaction_details->description = 'Expense For Sales Return';
                        $chart_of_account_transaction_details->year = $year;
                        $chart_of_account_transaction_details->month = $month;
                        $chart_of_account_transaction_details->transaction_date = $date;
                        $chart_of_account_transaction_details->transaction_date_time = $transaction_date_time;
                        $chart_of_account_transaction_details->save();

                        // cash
                        if($request->payment_type == 'Cash'){
                            $cash_chart_of_account_info = ChartOfAccount::where('head_name','Cash')->first();
                            $chart_of_account_transaction_details = new ChartOfAccountTransactionDetail();
                            $chart_of_account_transaction_details->warehouse_id = $warehouse_id;
                            $chart_of_account_transaction_details->store_id = $store_id;
                            $chart_of_account_transaction_details->chart_of_account_transaction_id = $chart_of_account_transactions_insert_id;
                            $chart_of_account_transaction_details->chart_of_account_id = $cash_chart_of_account_info->id;
                            $chart_of_account_transaction_details->chart_of_account_number = $cash_chart_of_account_info->head_code;
                            $chart_of_account_transaction_details->chart_of_account_name = 'Cash';
                            $chart_of_account_transaction_details->chart_of_account_parent_name = $cash_chart_of_account_info->parent_head_name;
                            $chart_of_account_transaction_details->chart_of_account_type = $cash_chart_of_account_info->head_type;
                            $chart_of_account_transaction_details->debit = NULL;
                            $chart_of_account_transaction_details->credit = $request->total_amount;
                            $chart_of_account_transaction_details->description = 'Cash Out For Sales Return';
                            $chart_of_account_transaction_details->year = $year;
                            $chart_of_account_transaction_details->month = $month;
                            $chart_of_account_transaction_details->transaction_date = $date;
                            $chart_of_account_transaction_details->transaction_date_time = $transaction_date_time;
                            $chart_of_account_transaction_details->save();
                        }elseif($request->payment_type == 'Check'){
                            $cash_chart_of_account_info = ChartOfAccount::where('head_name','Check')->first();
                            $chart_of_account_transaction_details = new ChartOfAccountTransactionDetail();
                            $chart_of_account_transaction_details->warehouse_id = $warehouse_id;
                            $chart_of_account_transaction_details->store_id = $store_id;
                            $chart_of_account_transaction_details->chart_of_account_transaction_id = $chart_of_account_transactions_insert_id;
                            $chart_of_account_transaction_details->chart_of_account_id = $cash_chart_of_account_info->id;
                            $chart_of_account_transaction_details->chart_of_account_number = $cash_chart_of_account_info->head_code;
                            $chart_of_account_transaction_details->chart_of_account_name = 'Check';
                            $chart_of_account_transaction_details->chart_of_account_parent_name = $cash_chart_of_account_info->parent_head_name;
                            $chart_of_account_transaction_details->chart_of_account_type = $cash_chart_of_account_info->head_type;
                            $chart_of_account_transaction_details->debit = NULL;
                            $chart_of_account_transaction_details->credit = $request->total_amount;
                            $chart_of_account_transaction_details->description = 'Check Out For Sales Return';
                            $chart_of_account_transaction_details->year = $year;
                            $chart_of_account_transaction_details->month = $month;
                            $chart_of_account_transaction_details->transaction_date = $date;
                            $chart_of_account_transaction_details->transaction_date_time = $transaction_date_time;
                            $chart_of_account_transaction_details->save();
                        }elseif($request->payment_type == 'Card'){
                            $cash_chart_of_account_info = ChartOfAccount::where('head_name','Card')->first();
                            $chart_of_account_transaction_details = new ChartOfAccountTransactionDetail();
                            $chart_of_account_transaction_details->warehouse_id = $warehouse_id;
                            $chart_of_account_transaction_details->store_id = $store_id;
                            $chart_of_account_transaction_details->chart_of_account_transaction_id = $chart_of_account_transactions_insert_id;
                            $chart_of_account_transaction_details->chart_of_account_id = $cash_chart_of_account_info->id;
                            $chart_of_account_transaction_details->chart_of_account_number = $cash_chart_of_account_info->head_code;
                            $chart_of_account_transaction_details->chart_of_account_name = 'Card';
                            $chart_of_account_transaction_details->chart_of_account_parent_name = $cash_chart_of_account_info->parent_head_name;
                            $chart_of_account_transaction_details->chart_of_account_type = $cash_chart_of_account_info->head_type;
                            $chart_of_account_transaction_details->debit = NULL;
                            $chart_of_account_transaction_details->credit = $request->total_amount;
                            $chart_of_account_transaction_details->description = 'Card Out For Sales Return';
                            $chart_of_account_transaction_details->year = $year;
                            $chart_of_account_transaction_details->month = $month;
                            $chart_of_account_transaction_details->transaction_date = $date;
                            $chart_of_account_transaction_details->transaction_date_time = $transaction_date_time;
                            $chart_of_account_transaction_details->save();
                        }elseif($request->payment_type == 'Bkash'){
                            $cash_chart_of_account_info = ChartOfAccount::where('head_name','Bkash')->first();
                            $chart_of_account_transaction_details = new ChartOfAccountTransactionDetail();
                            $chart_of_account_transaction_details->warehouse_id = $warehouse_id;
                            $chart_of_account_transaction_details->store_id = $store_id;
                            $chart_of_account_transaction_details->chart_of_account_transaction_id = $chart_of_account_transactions_insert_id;
                            $chart_of_account_transaction_details->chart_of_account_id = $cash_chart_of_account_info->id;
                            $chart_of_account_transaction_details->chart_of_account_number = $cash_chart_of_account_info->head_code;
                            $chart_of_account_transaction_details->chart_of_account_name = 'Bkash';
                            $chart_of_account_transaction_details->chart_of_account_parent_name = $cash_chart_of_account_info->parent_head_name;
                            $chart_of_account_transaction_details->chart_of_account_type = $cash_chart_of_account_info->head_type;
                            $chart_of_account_transaction_details->debit = NULL;
                            $chart_of_account_transaction_details->credit = $request->total_amount;
                            $chart_of_account_transaction_details->description = 'Bkash Out For Sales Return';
                            $chart_of_account_transaction_details->year = $year;
                            $chart_of_account_transaction_details->month = $month;
                            $chart_of_account_transaction_details->transaction_date = $date;
                            $chart_of_account_transaction_details->transaction_date_time = $transaction_date_time;
                            $chart_of_account_transaction_details->save();
                        }elseif($request->payment_type == 'Nogod'){
                            $cash_chart_of_account_info = ChartOfAccount::where('head_name','Nogod')->first();
                            $chart_of_account_transaction_details = new ChartOfAccountTransactionDetail();
                            $chart_of_account_transaction_details->warehouse_id = $warehouse_id;
                            $chart_of_account_transaction_details->store_id = $store_id;
                            $chart_of_account_transaction_details->chart_of_account_transaction_id = $chart_of_account_transactions_insert_id;
                            $chart_of_account_transaction_details->chart_of_account_id = $cash_chart_of_account_info->id;
                            $chart_of_account_transaction_details->chart_of_account_number = $cash_chart_of_account_info->head_code;
                            $chart_of_account_transaction_details->chart_of_account_name = 'Nogod';
                            $chart_of_account_transaction_details->chart_of_account_parent_name = $cash_chart_of_account_info->parent_head_name;
                            $chart_of_account_transaction_details->chart_of_account_type = $cash_chart_of_account_info->head_type;
                            $chart_of_account_transaction_details->debit = NULL;
                            $chart_of_account_transaction_details->credit = $request->total_amount;
                            $chart_of_account_transaction_details->description = 'Nogod Out For Sales Return';
                            $chart_of_account_transaction_details->year = $year;
                            $chart_of_account_transaction_details->month = $month;
                            $chart_of_account_transaction_details->transaction_date = $date;
                            $chart_of_account_transaction_details->transaction_date_time = $transaction_date_time;
                            $chart_of_account_transaction_details->save();
                        }elseif($request->payment_type == 'Rocket'){
                            $cash_chart_of_account_info = ChartOfAccount::where('head_name','Rocket')->first();
                            $chart_of_account_transaction_details = new ChartOfAccountTransactionDetail();
                            $chart_of_account_transaction_details->warehouse_id = $warehouse_id;
                            $chart_of_account_transaction_details->store_id = $store_id;
                            $chart_of_account_transaction_details->chart_of_account_transaction_id = $chart_of_account_transactions_insert_id;
                            $chart_of_account_transaction_details->chart_of_account_id = $cash_chart_of_account_info->id;
                            $chart_of_account_transaction_details->chart_of_account_number = $cash_chart_of_account_info->head_code;
                            $chart_of_account_transaction_details->chart_of_account_name = 'Rocket';
                            $chart_of_account_transaction_details->chart_of_account_parent_name = $cash_chart_of_account_info->parent_head_name;
                            $chart_of_account_transaction_details->chart_of_account_type = $cash_chart_of_account_info->head_type;
                            $chart_of_account_transaction_details->debit = NULL;
                            $chart_of_account_transaction_details->credit = $request->total_amount;
                            $chart_of_account_transaction_details->description = 'Rocket Out For Sales Return';
                            $chart_of_account_transaction_details->year = $year;
                            $chart_of_account_transaction_details->month = $month;
                            $chart_of_account_transaction_details->transaction_date = $date;
                            $chart_of_account_transaction_details->transaction_date_time = $transaction_date_time;
                            $chart_of_account_transaction_details->save();
                        }elseif($request->payment_type == 'Upay'){
                            $cash_chart_of_account_info = ChartOfAccount::where('head_name','Upay')->first();
                            $chart_of_account_transaction_details = new ChartOfAccountTransactionDetail();
                            $chart_of_account_transaction_details->warehouse_id = $warehouse_id;
                            $chart_of_account_transaction_details->store_id = $store_id;
                            $chart_of_account_transaction_details->chart_of_account_transaction_id = $chart_of_account_transactions_insert_id;
                            $chart_of_account_transaction_details->chart_of_account_id = $cash_chart_of_account_info->id;
                            $chart_of_account_transaction_details->chart_of_account_number = $cash_chart_of_account_info->head_code;
                            $chart_of_account_transaction_details->chart_of_account_name = 'Upay';
                            $chart_of_account_transaction_details->chart_of_account_parent_name = $cash_chart_of_account_info->parent_head_name;
                            $chart_of_account_transaction_details->chart_of_account_type = $cash_chart_of_account_info->head_type;
                            $chart_of_account_transaction_details->debit = NULL;
                            $chart_of_account_transaction_details->credit = $request->total_amount;
                            $chart_of_account_transaction_details->description = 'Upay Out For Sales Return';
                            $chart_of_account_transaction_details->year = $year;
                            $chart_of_account_transaction_details->month = $month;
                            $chart_of_account_transaction_details->transaction_date = $date;
                            $chart_of_account_transaction_details->transaction_date_time = $transaction_date_time;
                            $chart_of_account_transaction_details->save();
                        }else{

                        }
                    }



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

    public function productSaleReturnEdit(Request $request){
        //dd($request->all());
        $this->validate($request, [
            'product_sale_return_id'=> 'required',
            'party_id'=> 'required',
            'paid_amount'=> 'required',
            'due_amount'=> 'required',
            'total_amount'=> 'required',
            'payment_type'=> 'required',
        ]);

        $date = date('Y-m-d');
        $date_time = date('Y-m-d h:i:s');

        $user_id = Auth::user()->id;

        // product sale return
        $productSaleReturn = ProductSaleReturn::where('id',$request->product_sale_return_id)->first();;
        $productSaleReturn->user_id = $user_id;
        $productSaleReturn->party_id = $request->party_id;
        $productSaleReturn->discount_type = $request->discount_type ? $request->discount_type : NULL;
        $productSaleReturn->discount_amount = $request->discount_amount ? $request->discount_amount : 0;
        $productSaleReturn->paid_amount = $request->total_amount;
        $productSaleReturn->due_amount = $request->due_amount;
        $productSaleReturn->total_amount = $request->total_amount;
        $productSaleReturn->product_sale_return_date = $date;
        $productSaleReturn->product_sale_return_date_time = $date_time;
        $affected_row = $productSaleReturn->save();

        $productSale = ProductSale::where('invoice_no',$productSaleReturn->product_sale_invoice_no)->first();
        $warehouse_id = $productSale->warehouse_id;

        if($affected_row)
        {
            // for live testing
            foreach ($request->products as $data) {

                $product_id =  $data['product_id'];

                $barcode = Product::where('id',$product_id)->pluck('barcode')->first();
                $get_purchase_price = Product::where('id',$product_id)->pluck('purchase_price')->first();

                // product purchase detail
                $productSaleReturnDetail = ProductSaleReturnDetail::find($data['product_sale_return_detail_id']);
                $product_sale_detail_id = $productSaleReturnDetail->pro_sale_detail_id;
                $previous_sale_return_qty = $productSaleReturnDetail->qty;
                $productSaleReturnDetail->pro_sale_detail_id = $data['product_sale_detail_id'];
                $productSaleReturnDetail->product_unit_id = $data['product_unit_id'];
                $productSaleReturnDetail->product_brand_id = $data['product_brand_id'] ? $data['product_brand_id'] : NULL;
                $productSaleReturnDetail->product_id = $product_id;
                $productSaleReturnDetail->purchase_price = $get_purchase_price;
                $productSaleReturnDetail->barcode = $barcode;
                $productSaleReturnDetail->qty = $data['qty'];
                $productSaleReturnDetail->price = $data['mrp_price'];
                $productSaleReturnDetail->sub_total = $data['qty']*$data['mrp_price'];
                $productSaleReturnDetail->save();

                $sale_type = $productSale->sale_type;
                // product stock
                if($sale_type == 'pos_sale') {
                    $store_id = $productSale->store_id;
                    $stock_row = Stock::where('warehouse_id',$warehouse_id)->where('store_id',$store_id)->where('product_id',$product_id)->latest()->first();
                }else{
                    $stock_row = Stock::where('warehouse_id',$warehouse_id)->where('product_id',$product_id)->latest()->first();
                }

                $current_stock = $stock_row->current_stock;

                if($stock_row->stock_out != $data['qty']){

                    if($sale_type == 'pos_sale') {
                        $update_warehouse_store_current_stock = WarehouseStoreCurrentStock::where('warehouse_id', $warehouse_id)
                            ->where('store_id', $store_id)
                            ->where('product_id', $product_id)
                            ->first();
                        $exists_current_stock = $update_warehouse_store_current_stock->current_stock;

                        if($data['qty'] > $stock_row->stock_in){
                            $new_stock_out = $data['qty'] - $previous_sale_return_qty;

                            $stock = new Stock();
                            $stock->ref_id=$request->product_sale_id;
                            $stock->user_id=$user_id;
                            $stock->product_unit_id= $data['product_unit_id'];
                            $stock->product_brand_id= $data['product_brand_id'] ? $data['product_brand_id'] : NULL;
                            $stock->product_id= $product_id;
                            $stock->stock_type='sale_return_increase';
                            $stock->warehouse_id= $warehouse_id;
                            $stock->store_id=$store_id;
                            $stock->stock_where='store';
                            $stock->stock_in_out='stock_out';
                            $stock->previous_stock=$current_stock;
                            $stock->stock_in=0;
                            $stock->stock_out=$new_stock_out;
                            $stock->current_stock=$current_stock - $new_stock_out;
                            $stock->stock_date=$date;
                            $stock->stock_date_time=$date_time;
                            $stock->save();

                            // warehouse current stock
                            $update_warehouse_store_current_stock->current_stock=$exists_current_stock - $new_stock_out;
                            $update_warehouse_store_current_stock->save();
                        }else{
                            $new_stock_in = $previous_sale_return_qty - $data['qty'];

                            $stock = new Stock();
                            $stock->ref_id=$request->product_sale_id;
                            $stock->user_id=$user_id;
                            $stock->product_unit_id= $data['product_unit_id'];
                            $stock->product_brand_id= $data['product_brand_id'] ? $data['product_brand_id'] : NULL;
                            $stock->product_id= $product_id;
                            $stock->stock_type='sale_return_decrease';
                            $stock->warehouse_id= $warehouse_id;
                            $stock->store_id=$store_id;
                            $stock->stock_where='store';
                            $stock->stock_in_out='stock_in';
                            $stock->previous_stock=$current_stock;
                            $stock->stock_in=$new_stock_in;
                            $stock->stock_out=0;
                            $stock->current_stock=$current_stock + $new_stock_in;
                            $stock->stock_date=$date;
                            $stock->stock_date_time=$date_time;
                            $stock->save();

                            // warehouse current stock
                            $update_warehouse_store_current_stock->current_stock=$exists_current_stock + $new_stock_in;
                            $update_warehouse_store_current_stock->save();
                        }
                    }

                    if($sale_type == 'whole_sale') {
                        $update_warehouse_current_stock = WarehouseCurrentStock::where('warehouse_id', $warehouse_id)
                            ->where('product_id', $product_id)
                            ->first();
                        $exists_current_stock = $update_warehouse_current_stock->current_stock;

                        if($data['qty'] > $stock_row->stock_in){
                            $new_stock_out = $data['qty'] - $previous_sale_return_qty;

                            $stock = new Stock();
                            $stock->ref_id=$request->product_sale_id;
                            $stock->user_id=$user_id;
                            $stock->product_unit_id= $data['product_unit_id'];
                            $stock->product_brand_id= $data['product_brand_id'] ? $data['product_brand_id'] : NULL;
                            $stock->product_id= $product_id;
                            $stock->stock_type='sale_return_increase';
                            $stock->warehouse_id= $warehouse_id;
                            $stock->store_id=NULL;
                            $stock->stock_where='store';
                            $stock->stock_in_out='stock_out';
                            $stock->previous_stock=$current_stock;
                            $stock->stock_in=0;
                            $stock->stock_out=$new_stock_out;
                            $stock->current_stock=$current_stock - $new_stock_out;
                            $stock->stock_date=$date;
                            $stock->stock_date_time=$date_time;
                            $stock->save();

                            // warehouse current stock
                            $update_warehouse_current_stock->current_stock=$exists_current_stock - $new_stock_out;
                            $update_warehouse_current_stock->save();
                        }else{
                            $new_stock_in = $previous_sale_return_qty - $data['qty'];

                            $stock = new Stock();
                            $stock->ref_id=$request->product_sale_id;
                            $stock->user_id=$user_id;
                            $stock->product_unit_id= $data['product_unit_id'];
                            $stock->product_brand_id= $data['product_brand_id'] ? $data['product_brand_id'] : NULL;
                            $stock->product_id= $product_id;
                            $stock->stock_type='sale_return_decrease';
                            $stock->warehouse_id= $warehouse_id;
                            $stock->store_id=NULL;
                            $stock->stock_where='store';
                            $stock->stock_in_out='stock_in';
                            $stock->previous_stock=$current_stock;
                            $stock->stock_in=$new_stock_in;
                            $stock->stock_out=0;
                            $stock->current_stock=$current_stock + $new_stock_in;
                            $stock->stock_date=$date;
                            $stock->stock_date_time=$date_time;
                            $stock->save();

                            // warehouse current stock
                            $update_warehouse_current_stock->current_stock=$exists_current_stock + $new_stock_in;
                            $update_warehouse_current_stock->save();
                        }
                    }
                }

                $check_return_last_date = ProductSaleDetail::where('id',$product_sale_detail_id)->pluck('return_last_date')->first();
                $today_date = date('Y-m-d');

                if($check_return_last_date >= $today_date){
                    // for sale return cash back among 2 days
                    // transaction
                    $transaction = Transaction::where('ref_id',$productSale->id)->where('transaction_type','sale_return_cash')->first();
                    $transaction->user_id = $user_id;
                    $transaction->party_id = $request->party_id;
                    $transaction->payment_type = $request->payment_type;
                    $transaction->amount = $data['qty']*$data['mrp_price'];
                    $transaction->transaction_date = $date;
                    $transaction->transaction_date_time = $date_time;
                    $transaction->save();

                    // payment paid
                    $payment_collection = PaymentCollection::where('ref_id',$productSale->id)->where('collection_type','Return Cash')->first();
                    $payment_collection->user_id = $user_id;
                    $payment_collection->party_id = $request->party_id;
                    $payment_collection->collection_amount = $data['qty']*$data['mrp_price'];
                    $payment_collection->current_collection_amount = $data['qty']*$data['mrp_price'];
                    $payment_collection->collection_date = $date;
                    $payment_collection->collection_date_time = $date_time;
                    $payment_collection->save();

                    if($sale_type == 'pos_sale') {
                        // posting
                        $month = date('m', strtotime($date));
                        $year = date('Y', strtotime($date));
                        $transaction_date_time = date('Y-m-d H:i:s');

                        $chart_of_account_transactions = ChartOfAccountTransaction::where('ref_id', $productSale->id)->where('transaction_type', 'Sales Return')->first();;
                        $chart_of_account_transaction_id = $chart_of_account_transactions->id;
                        $chart_of_account_transactions->transaction_type = 'Sales Return';
                        $chart_of_account_transactions->user_id = $user_id;
                        $chart_of_account_transactions->transaction_date = $date;
                        $chart_of_account_transactions->transaction_date_time = $transaction_date_time;
                        $aff_row = $chart_of_account_transactions->save();

                        if ($aff_row) {
                            // sales Return
                            $chart_of_account_transaction_details = ChartOfAccountTransactionDetail::where('chart_of_account_transaction_id', $chart_of_account_transaction_id)->where('debit', '>', 0)->first();
                            $chart_of_account_transaction_details->debit = $request->total_amount;
                            $chart_of_account_transaction_details->year = $year;
                            $chart_of_account_transaction_details->month = $month;
                            $chart_of_account_transaction_details->transaction_date = $date;
                            $chart_of_account_transaction_details->transaction_date_time = $transaction_date_time;
                            $chart_of_account_transaction_details->save();

                            // cash
                            $chart_of_account_transaction_details = ChartOfAccountTransactionDetail::where('chart_of_account_transaction_id', $chart_of_account_transaction_id)->where('credit', '>', 0)->first();
                            $chart_of_account_transaction_details->credit = $request->total_amount;
                            $chart_of_account_transaction_details->year = $year;
                            $chart_of_account_transaction_details->month = $month;
                            $chart_of_account_transaction_details->transaction_date = $date;
                            $chart_of_account_transaction_details->transaction_date_time = $transaction_date_time;
                            $chart_of_account_transaction_details->save();
                        }
                    }
                }else{
                    // for sale return balance add after 2 days
                    // transaction
                    $transaction = Transaction::where('ref_id',$productSale->id)->where('transaction_type','sale_return_balance')->first();;
                    $transaction->user_id = $user_id;
                    $transaction->party_id = $request->party_id;
                    $transaction->payment_type = $request->payment_type;
                    $transaction->amount = $data['qty']*$data['mrp_price'];
                    $transaction->transaction_date = $date;
                    $transaction->transaction_date_time = $date_time;
                    $transaction->save();

                    // payment paid
                    $payment_collection = PaymentCollection::where('ref_id',$productSale->id)->where('collection_type','Return Balance')->first();;
                    $payment_collection->user_id = $user_id;
                    $payment_collection->party_id = $request->party_id;
                    $payment_collection->collection_amount = $data['qty']*$data['mrp_price'];
                    $payment_collection->current_collection_amount = $data['qty']*$data['mrp_price'];
                    $payment_collection->collection_date = $date;
                    $payment_collection->collection_date_time = $date_time;
                    $payment_collection->save();

                    if($sale_type == 'pos_sale') {
                        // add balance
                        $party_previous_virtual_balance = Party::where('id', $request->party_id)->pluck('virtual_balance')->first();

                        $party = Party::find($request->party_id);
                        if ($previous_sale_return_qty != $data['qty']) {
                            if ($data['qty'] > $previous_sale_return_qty) {
                                $new_qty = $data['qty'] - $previous_sale_return_qty;
                                $party->virtual_balance = $party_previous_virtual_balance + ($new_qty * $data['mrp_price']);
                            } else {
                                $new_qty = $previous_sale_return_qty - $data['qty'];
                                $party->virtual_balance = $party_previous_virtual_balance - ($new_qty * $data['mrp_price']);
                            }
                        }
                        $party->update();
                    }
                }
            }

            return response()->json(['success'=>true,'response' => 'Inserted Successfully.'], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Inserted Successfully!'], $this->failStatus);
        }
    }


    public function productSaleReturnSingleProductRemove(Request $request){
        //dd($request->all());
        $this->validate($request, [
            'product_sale_return_id'=> 'required',
            'product_sale_return_detail_id'=> 'required',
        ]);

        $date = date('Y-m-d');
        $date_time = date('Y-m-d h:i:s');

        $user_id = Auth::user()->id;

        // product sale return
        $productSaleReturn = ProductSaleReturn::where('id',$request->product_sale_return_id)->first();

        $productSaleReturnDetail = ProductSaleReturnDetail::where('id',$request->product_sale_return_detail_id)->first();
        $product_sale_detail_id = $productSaleReturnDetail->pro_sale_detail_id;
        $product_id = $productSaleReturnDetail->product_id;
        $qty = $productSaleReturnDetail->qty;
        $product_unit_id = $productSaleReturnDetail->product_unit_id;
        $product_brand_id = $productSaleReturnDetail->product_brand_id;

        $sub_total = $productSaleReturnDetail->sub_total;








        $productSale = ProductSale::where('invoice_no',$productSaleReturn->product_sale_invoice_no)->first();
        $warehouse_id = $productSale->warehouse_id;
        $final_paid_amount = $productSale->paid_amount - $sub_total;
        $final_due_amount = $productSale->due_amount - $sub_total;
        $final_total_amount = $productSale->total_amount - $sub_total;




        $sale_type = $productSale->sale_type;
        // product stock
        $store_id = NULL;
        if($sale_type == 'pos_sale') {
            $store_id = $productSale->store_id;
            $stock_row = Stock::where('warehouse_id',$warehouse_id)->where('store_id',$store_id)->where('product_id',$product_id)->latest()->first();
        }else{
            $stock_row = Stock::where('warehouse_id',$warehouse_id)->where('product_id',$product_id)->latest()->first();
        }

        $current_stock = $stock_row->current_stock;

        if($sale_type == 'pos_sale') {
            $update_warehouse_store_current_stock = WarehouseStoreCurrentStock::where('warehouse_id', $warehouse_id)
                ->where('store_id', $store_id)
                ->where('product_id', $product_id)
                ->first();
            $exists_current_stock = $update_warehouse_store_current_stock->current_stock;

            $stock = new Stock();
            $stock->ref_id=$request->product_sale_id;
            $stock->user_id=$user_id;
            $stock->product_unit_id= $product_unit_id;
            $stock->product_brand_id= $product_brand_id ? $product_brand_id : NULL;
            $stock->product_id= $product_id;
            $stock->stock_type='sale_return_remove';
            $stock->warehouse_id= $warehouse_id;
            $stock->store_id=$store_id;
            $stock->stock_where='store';
            $stock->stock_in_out='stock_in';
            $stock->previous_stock=$current_stock;
            $stock->stock_in=$qty;
            $stock->stock_out=0;
            $stock->current_stock=$current_stock + $qty;
            $stock->stock_date=$date;
            $stock->stock_date_time=$date_time;
            $stock->save();

            // warehouse current stock
            $update_warehouse_store_current_stock->current_stock=$exists_current_stock + $qty;
            $update_warehouse_store_current_stock->save();

        }

        if($sale_type == 'whole_sale') {
            $update_warehouse_current_stock = WarehouseCurrentStock::where('warehouse_id', $warehouse_id)
                ->where('product_id', $product_id)
                ->first();
            $exists_current_stock = $update_warehouse_current_stock->current_stock;

            $stock = new Stock();
            $stock->ref_id=$request->product_sale_id;
            $stock->user_id=$user_id;
            $stock->product_unit_id= $product_unit_id;
            $stock->product_brand_id= $product_brand_id ? $product_brand_id : NULL;
            $stock->product_id= $product_id;
            $stock->stock_type='sale_return_remove';
            $stock->warehouse_id= $warehouse_id;
            $stock->store_id=NULL;
            $stock->stock_where='store';
            $stock->stock_in_out='stock_in';
            $stock->previous_stock=$current_stock;
            $stock->stock_in=$qty;
            $stock->stock_out=0;
            $stock->current_stock=$current_stock + $qty;
            $stock->stock_date=$date;
            $stock->stock_date_time=$date_time;
            $stock->save();

            // warehouse current stock
            $update_warehouse_current_stock->current_stock=$exists_current_stock + $qty;
            $update_warehouse_current_stock->save();
        }

        $check_return_last_date = ProductSaleDetail::where('id',$product_sale_detail_id)->pluck('return_last_date')->first();
        $today_date = date('Y-m-d');

        if($check_return_last_date >= $today_date){
            // for sale return cash back among 2 days
            // transaction
            $transaction = Transaction::where('ref_id',$productSale->id)->where('transaction_type','sale_return_cash')->first();
            $previous_amount = $transaction->amount;
            $transaction->user_id = $user_id;
            $transaction->amount = $previous_amount - $sub_total;
            $transaction->transaction_date = $date;
            $transaction->transaction_date_time = $date_time;
            $transaction->save();

            // payment paid
            $payment_collection = PaymentCollection::where('ref_id',$productSale->id)->where('collection_type','Return Cash')->first();
            $payment_collection->user_id = $user_id;
            $payment_collection->collection_amount = $previous_amount - $sub_total;
            $payment_collection->current_collection_amount = $previous_amount - $sub_total;
            $payment_collection->collection_date = $date;
            $payment_collection->collection_date_time = $date_time;
            $payment_collection->save();

            // posting
            $month = date('m', strtotime($date));
            $year = date('Y', strtotime($date));
            $transaction_date_time = date('Y-m-d H:i:s');

            $chart_of_account_transactions = ChartOfAccountTransaction::where('ref_id',$productSale->id)->where('transaction_type','Sales Return')->first();;
            $chart_of_account_transactions->user_id = $user_id;
            $chart_of_account_transactions->transaction_date = $date;
            $chart_of_account_transactions->transaction_date_time = $transaction_date_time;
            $aff_row = $chart_of_account_transactions->save();

            if($aff_row){
                // sales Return
                $chart_of_account_transaction_details = ChartOfAccountTransactionDetail::where('chart_of_account_transaction_id',$chart_of_account_transactions->chart_of_account_transaction_id)->where('debit','>',0)->where('chart_of_account_name','=','Sales Return')->first();
                $previous_chart_of_account_transaction_amount = $chart_of_account_transaction_details->amount;
                $chart_of_account_transaction_details->debit = $previous_chart_of_account_transaction_amount - $sub_total;
                $chart_of_account_transaction_details->year = $year;
                $chart_of_account_transaction_details->month = $month;
                $chart_of_account_transaction_details->transaction_date = $date;
                $chart_of_account_transaction_details->transaction_date_time = $transaction_date_time;
                $chart_of_account_transaction_details->save();

                // cash
                $chart_of_account_transaction_details = ChartOfAccountTransactionDetail::where('chart_of_account_transaction_id',$chart_of_account_transactions->chart_of_account_transaction_id)->where('credit','>',0)->where('chart_of_account_name','!=','Sales Return')->first();
                $previous_chart_of_account_transaction_amount = $chart_of_account_transaction_details->amount;
                $chart_of_account_transaction_details->credit = $previous_chart_of_account_transaction_amount - $sub_total;
                $chart_of_account_transaction_details->year = $year;
                $chart_of_account_transaction_details->month = $month;
                $chart_of_account_transaction_details->transaction_date = $date;
                $chart_of_account_transaction_details->transaction_date_time = $transaction_date_time;
                $chart_of_account_transaction_details->save();
            }
        }else{
            // for sale return balance add after 2 days
            // transaction
            $transaction = Transaction::where('ref_id',$productSale->id)->where('transaction_type','sale_return_cash')->first();
            $previous_amount = $transaction->amount;
            $transaction->user_id = $user_id;
            $transaction->amount = $previous_amount - $sub_total;
            $transaction->transaction_date = $date;
            $transaction->transaction_date_time = $date_time;

            // payment paid
            $payment_collection = PaymentCollection::where('ref_id',$productSale->id)->where('collection_type','Return Cash')->first();
            $payment_collection->user_id = $user_id;
            $payment_collection->collection_amount = $previous_amount - $sub_total;
            $payment_collection->current_collection_amount = $previous_amount - $sub_total;
            $payment_collection->collection_date = $date;
            $payment_collection->collection_date_time = $date_time;
            $payment_collection->save();

            // add balance
            $party_previous_virtual_balance = Party::where('id',$productSaleReturn->party_id)->pluck('virtual_balance')->first();
            $party = Party::find($request->party_id);
            $party->virtual_balance = $party_previous_virtual_balance - $sub_total;
            $party->update();
        }




        $productSaleReturn->user_id = $user_id;
        $productSaleReturn->paid_amount = $final_paid_amount ;
        $productSaleReturn->due_amount = $final_due_amount;
        $productSaleReturn->total_amount = $final_total_amount;
        $productSaleReturn->product_sale_return_date = $date;
        $productSaleReturn->product_sale_return_date_time = $date_time;
        $productSaleReturn->save();

        DB::table('product_sale_return_details')->delete($request->product_sale_return_detail_id);

        return response()->json(['success'=>true,'response' => 'Inserted Successfully.'], $this->successStatus);

    }
}
