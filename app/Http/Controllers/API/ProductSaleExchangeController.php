<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\PaymentCollection;
use App\Product;
use App\ProductSale;
use App\ProductSaleExchange;
use App\ProductSaleExchangeDetail;
use App\ProductSalePreviousDetail;
use App\Stock;
use App\Store;
use App\Transaction;
use App\WarehouseCurrentStock;
use App\WarehouseStoreCurrentStock;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;


class ProductSaleExchangeController extends Controller
{
    public $successStatus = 200;
    public $authStatus = 401;
    public $failStatus = 402;
    public $ExistsStatus = 403;
    public $validationStatus = 404;


    // sale exchange
    public function productSaleExchangeList(){
        $product_pos_sales = DB::table('product_sale_exchanges')
            ->leftJoin('users','product_sale_exchanges.user_id','users.id')
            ->leftJoin('parties','product_sale_exchanges.party_id','parties.id')
            ->leftJoin('warehouses','product_sale_exchanges.warehouse_id','warehouses.id')
            ->leftJoin('stores','product_sale_exchanges.store_id','stores.id')
            //->where('product_sale_exchanges.sale_type','pos_sale')
            ->select(
                'product_sale_exchanges.id',
                'product_sale_exchanges.invoice_no',
                'product_sale_exchanges.sale_invoice_no',
                'product_sale_exchanges.discount_type',
                'product_sale_exchanges.discount_amount',
                'product_sale_exchanges.total_vat_amount',
                'product_sale_exchanges.total_amount',
                'product_sale_exchanges.paid_amount',
                'product_sale_exchanges.due_amount',
                'product_sale_exchanges.sale_exchange_date_time',
                'users.name as user_name',
                'parties.id as customer_id',
                'parties.name as customer_name',
                'warehouses.id as warehouse_id',
                'warehouses.name as warehouse_name',
                'stores.id as store_id',
                'stores.name as store_name',
                'stores.address as store_address'
            )
            ->orderBy('product_sale_exchanges.id','desc')
            ->get();

        if(count($product_pos_sales) > 0)
        {
            $product_sale_exchange_arr = [];
            foreach ($product_pos_sales as $data){
                $payment_type = DB::table('transactions')->where('ref_id',$data->id)->where('transaction_type','sale_exchange')->pluck('payment_type')->first();

                $nested_data['id']=$data->id;
                $nested_data['invoice_no']=ucfirst($data->invoice_no);
                $nested_data['sale_invoice_no']=$data->sale_invoice_no;
                $nested_data['discount_type']=$data->discount_type;
                $nested_data['discount_amount']=$data->discount_amount;
                $nested_data['total_vat_amount']=$data->total_vat_amount;
                $nested_data['total_amount']=$data->total_amount;
                $nested_data['paid_amount']=$data->paid_amount;
                $nested_data['due_amount']=$data->due_amount;
                $nested_data['sale_exchange_date_time']=$data->sale_exchange_date_time;
                $nested_data['user_name']=$data->user_name;
                $nested_data['customer_id']=$data->customer_id;
                $nested_data['customer_name']=$data->customer_name;
                $nested_data['warehouse_id']=$data->warehouse_id;
                $nested_data['warehouse_name']=$data->warehouse_name;
                $nested_data['store_id']=$data->store_id;
                $nested_data['store_name']=$data->store_name;
                $nested_data['store_address']=$data->store_address;
                $nested_data['payment_type']=$payment_type;

                array_push($product_sale_exchange_arr,$nested_data);
            }

            $success['product_sale_exchange'] =  $product_sale_exchange_arr;
            return response()->json(['success'=>true,'response' => $success], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Product Sale Exchange List Found!'], $this->failStatus);
        }
    }

    public function productSaleExchangeDetails(Request $request){
        $product_sale_previous_details = DB::table('product_sale_exchanges')
            ->join('product_sale_previous_details','product_sale_exchanges.id','product_sale_previous_details.pro_sale_ex_id')
            ->leftJoin('products','product_sale_previous_details.product_id','products.id')
            ->leftJoin('product_units','product_sale_previous_details.product_unit_id','product_units.id')
            ->leftJoin('product_brands','product_sale_previous_details.product_brand_id','product_brands.id')
            ->where('product_sale_exchanges.id',$request->product_sale_exchange_id)
            ->select(
                'products.id as product_id',
                'products.name as product_name',
                'product_units.id as product_unit_id',
                'product_units.name as product_unit_name',
                'product_brands.id as product_brand_id',
                'product_brands.name as product_brand_name',
                'product_sale_previous_details.qty',
                'product_sale_previous_details.id as product_sale_previous_detail_id',
                'product_sale_previous_details.price as mrp_price',
                'product_sale_previous_details.vat_amount'
            )
            ->get();


        $product_sale_exchange_details = DB::table('product_sale_exchanges')
            ->join('product_sale_exchange_details','product_sale_exchanges.id','product_sale_exchange_details.pro_sale_ex_id')
            ->leftJoin('products','product_sale_exchange_details.product_id','products.id')
            ->leftJoin('product_units','product_sale_exchange_details.product_unit_id','product_units.id')
            ->leftJoin('product_brands','product_sale_exchange_details.product_brand_id','product_brands.id')
            ->where('product_sale_exchanges.id',$request->product_sale_exchange_id)
            ->select(
                'products.id as product_id',
                'products.name as product_name',
                'product_units.id as product_unit_id',
                'product_units.name as product_unit_name',
                'product_brands.id as product_brand_id',
                'product_brands.name as product_brand_name',
                'product_sale_exchange_details.qty',
                'product_sale_exchange_details.id as product_sale_exchange_detail_id',
                'product_sale_exchange_details.price as mrp_price',
                'product_sale_exchange_details.vat_amount'
            )
            ->get();

        if($product_sale_previous_details)
        {
            $success['product_sale_previous_details'] =  $product_sale_previous_details;
            $success['product_sale_exchange_details'] =  $product_sale_exchange_details;
            return response()->json(['success'=>true,'response' => $success], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Product Sale Exchange Detail Found!'], $this->failStatus);
        }
    }

    public function productSaleExchangeCreate(Request $request){

        $this->validate($request, [
            'sale_invoice_no'=> 'required',
            'party_id'=> 'required',
            'store_id'=> 'required',
            'paid_amount'=> 'required',
            'back_amount'=> 'required',
            'due_amount'=> 'required',
            'total_amount'=> 'required',
            'payment_type'=> 'required',
        ]);

        $get_invoice_no = ProductSaleExchange::latest('id','desc')->pluck('invoice_no')->first();
        if(!empty($get_invoice_no)){
            $get_invoice = str_replace("sale-exchange-","",$get_invoice_no);
            $invoice_no = $get_invoice+1;
        }else{
            $invoice_no = 110000;
        }
        $final_invoice = 'sale-exchange-'.$invoice_no;

        $date = date('Y-m-d');
        $date_time = date('Y-m-d h:i:s');
        //$add_two_day_date =  date('Y-m-d', strtotime("+2 days"));

        $user_id = Auth::user()->id;
        $store_id = $request->store_id;
        $warehouse_id = Store::where('id',$store_id)->pluck('warehouse_id')->first();

        $previous_paid_amount = 0;
        foreach ($request->products as $data) {
            $product_id =  $data['product_id'];
            $vat_amount = Product::where('id',$product_id)->pluck('vat_amount')->first();
            $previous_paid_amount += ($data['exchange_quantity']*$data['mrp_price']) + ($data['current_qty']*$vat_amount);
        }

        $total_vat_amount = 0;
        foreach ($request->exchange_products as $data) {
            $total_vat_amount += $data['vat_amount'];
        }

        // discount start
        $price = $data['mrp_price'];
        $discount_amount = $request->discount_amount;
        $total_amount = $request->total_amount;

        $final_discount_amount = (float)$discount_amount * (float)$price;
        $final_total_amount = (float)$discount_amount + (float)$total_amount;
        $discount_type = $request->discount_type;
        $discount = (float)$final_discount_amount/(float)$final_total_amount;
        if($discount_type != NULL){
            if($discount_type == 'Flat'){
                $discount = round($discount);
            }
        }
        // discount end

        $sale_type = ProductSale::where('invoice_no',$request->sale_invoice_no)->pluck('sale_type')->first();

        // product purchase
        $productSaleExchange = new ProductSaleExchange();
        $productSaleExchange ->invoice_no = $final_invoice;
        $productSaleExchange ->sale_invoice_no = $request->sale_invoice_no;
        $productSaleExchange ->user_id = $user_id;
        $productSaleExchange ->party_id = $request->party_id;
        $productSaleExchange ->warehouse_id = $warehouse_id;
        $productSaleExchange ->store_id = $store_id;
        $productSaleExchange ->sale_exchange_type = 'sale_exchange';
        $productSaleExchange ->discount_type = $request->discount_type ? $request->discount_type : NULL;
        $productSaleExchange ->discount_amount = $discount;
        $productSaleExchange ->previous_paid_amount = $previous_paid_amount;
        $productSaleExchange ->paid_amount = $request->paid_amount;
        $productSaleExchange ->back_amount = $request->back_amount;
        $productSaleExchange ->due_amount = $request->due_amount;
        $productSaleExchange ->total_vat_amount = $total_vat_amount;
        $productSaleExchange ->total_amount = $total_amount;
        $productSaleExchange ->sale_exchange_date = $date;
        $productSaleExchange ->sale_exchange_date_time = $date_time;
        $productSaleExchange->save();
        $insert_id = $productSaleExchange->id;

        if($insert_id)
        {
            // sale products
            foreach ($request->products as $data) {

                if($data['exchange_quantity'] > 0){
                    $product_id =  $data['product_id'];

                    $barcode = Product::where('id',$product_id)->pluck('barcode')->first();
                    $vat_amount = Product::where('id',$product_id)->pluck('vat_amount')->first();

                    // product purchase detail
                    $product_sale_previous_detail = new ProductSalePreviousDetail();
                    $product_sale_previous_detail->pro_sale_ex_id = $insert_id;
                    $product_sale_previous_detail->pro_sale_detail_id = $data['product_sale_detail_id'];
                    $product_sale_previous_detail->product_brand_id = $data['product_brand_id'] ? $data['product_brand_id'] : NULL;
                    $product_sale_previous_detail->product_unit_id = $data['product_unit_id'] ? $data['product_unit_id'] : NULL;
                    $product_sale_previous_detail->product_id = $product_id;
                    $product_sale_previous_detail->barcode = $barcode;
                    $product_sale_previous_detail->qty = $data['exchange_quantity'];
                    $product_sale_previous_detail->price = $data['mrp_price'];
                    $product_sale_previous_detail->vat_amount = $vat_amount;
                    $product_sale_previous_detail->sub_total = ($data['exchange_quantity']*$data['mrp_price']) + ($data['exchange_quantity']*$vat_amount);
                    $product_sale_previous_detail->sale_exchange_date = $date;
                    $product_sale_previous_detail->save();

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
                    $stock->stock_type = 'sale_exchange';
                    $stock->stock_where = 'store';
                    $stock->stock_in_out = 'stock_out';
                    $stock->previous_stock = $previous_stock;
                    $stock->stock_in = $data['exchange_quantity'];
                    $stock->stock_out = 0;
                    $stock->current_stock = $previous_stock + $data['exchange_quantity'];
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
                        $final_warehouse_store_current_stock = $exists_current_stock + $data['exchange_quantity'];
                        $update_warehouse_store_current_stock->current_stock=$final_warehouse_store_current_stock;
                        $update_warehouse_store_current_stock->save();
                    }

                    if($sale_type == 'whole_sale'){
                        // warehouse current stock
                        $update_warehouse_current_stock = WarehouseCurrentStock::where('warehouse_id',$warehouse_id)
                            ->where('product_id',$product_id)
                            ->first();

                        $exists_current_stock = $update_warehouse_current_stock->current_stock;
                        $final_warehouse_current_stock = $exists_current_stock + $data['exchange_quantity'];
                        $update_warehouse_current_stock->current_stock=$final_warehouse_current_stock;
                        $update_warehouse_current_stock->save();
                    }
                }
            }

            // exchange products
            foreach ($request->exchange_products as $data) {

                $product_id =  $data['product_id'];

                $barcode = Product::where('id',$product_id)->pluck('barcode')->first();

                // product purchase detail
                $product_sale_exchange_detail = new ProductSaleExchangeDetail();
                $product_sale_exchange_detail->pro_sale_ex_id = $insert_id;
                $product_sale_exchange_detail->product_unit_id = $data['product_unit_id'];
                $product_sale_exchange_detail->product_brand_id = $data['product_brand_id'] ? $data['product_brand_id'] : NULL;
                $product_sale_exchange_detail->product_id = $product_id;
                $product_sale_exchange_detail->barcode = $barcode;
                $product_sale_exchange_detail->qty = $data['qty'];
                $product_sale_exchange_detail->price = $data['mrp_price'];
                $product_sale_exchange_detail->vat_amount = $data['vat_amount'];
                $product_sale_exchange_detail->sub_total = ($data['qty']*$data['mrp_price']) + ($data['qty']*$data['vat_amount']);
                $product_sale_exchange_detail->sale_exchange_date = $date;
                $product_sale_exchange_detail->save();


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
                $stock->stock_type = 'sale_exchange';
                $stock->stock_where = 'store';
                $stock->stock_in_out = 'stock_out';
                $stock->previous_stock = $previous_stock;
                $stock->stock_in = 0;
                $stock->stock_out = $data['qty'];
                $stock->current_stock = $previous_stock - $data['qty'];
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
                    $final_warehouse_current_stock = $exists_current_stock - $data['qty'];
                    $update_warehouse_store_current_stock->current_stock=$final_warehouse_current_stock;
                    $update_warehouse_store_current_stock->save();
                }

                if($sale_type == 'whole_sale'){
                    // warehouse store current stock
                    $update_warehouse_current_stock = WarehouseCurrentStock::where('warehouse_id',$warehouse_id)
                        ->where('product_id',$product_id)
                        ->first();

                    $exists_current_stock = $update_warehouse_current_stock->current_stock;
                    $final_warehouse_current_stock = $exists_current_stock - $data['qty'];
                    $update_warehouse_current_stock->current_stock=$final_warehouse_current_stock;
                    $update_warehouse_current_stock->save();
                }

            }

            // transaction
            $transaction = new Transaction();
            $transaction->ref_id = $insert_id;
            $transaction->invoice_no = $final_invoice;
            $transaction->user_id = $user_id;
            $transaction->warehouse_id = $warehouse_id;
            $transaction->store_id = $store_id;
            $transaction->party_id = $request->party_id;
            $transaction->transaction_type = 'sale_exchange';
            $transaction->payment_type = $request->payment_type;
            $transaction->amount = $request->paid_amount;
            $transaction->transaction_date = $date;
            $transaction->transaction_date_time = $date_time;
            $transaction->save();

            // payment paid
            $payment_collection = new PaymentCollection();
            $payment_collection->invoice_no = $final_invoice;
            $payment_collection->product_sale_exchange_id = $insert_id;
            $payment_collection->user_id = $user_id;
            $payment_collection->party_id = $request->party_id;
            $payment_collection->warehouse_id = $warehouse_id;
            $payment_collection->store_id = $store_id;
            $payment_collection->collection_type = 'Sale';
            $payment_collection->collection_amount = $request->paid_amount;
            $payment_collection->due_amount = $request->due_amount;
            $payment_collection->current_collection_amount = $request->paid_amount;
            $payment_collection->collection_date = $date;
            $payment_collection->collection_date_time = $date_time;
            $payment_collection->save();

            return response()->json(['success'=>true,'product_pos_sale' => 'Inserted Successfully!'], $this->successStatus);

        }else{
            return response()->json(['success'=>false,'response'=>'No Inserted Successfully!'], $this->failStatus);
        }
    }

    public function productSaleExchangeEdit(Request $request){

        $this->validate($request, [
            'product_sale_exchange_id'=> 'required',
            'party_id'=> 'required',
            'store_id'=> 'required',
            'paid_amount'=> 'required',
            'back_amount'=> 'required',
            'due_amount'=> 'required',
            'total_amount'=> 'required',
            'payment_type'=> 'required',
        ]);



        $date = date('Y-m-d');
        $date_time = date('Y-m-d h:i:s');

        $user_id = Auth::user()->id;
        $store_id = $request->store_id;
        $warehouse_id = Store::where('id',$store_id)->pluck('warehouse_id')->first();

        $previous_paid_amount = $request->previous_paid_amount ? $request->previous_paid_amount : 0;
//        foreach ($request->products as $data) {
//            $product_id =  $data['product_id'];
//            $vat_amount = Product::where('id',$product_id)->pluck('vat_amount')->first();
//            $previous_paid_amount += ($data['exchange_quantity']*$data['mrp_price']) + ($data['exchange_quantity']*$vat_amount);
//        }

        $total_vat_amount = 0;
        foreach ($request->exchange_products as $data) {
            $total_vat_amount += $data['vat_amount'];
        }

        // discount start
        $price = $data['mrp_price'];
        $discount_amount = $request->discount_amount;
        $total_amount = $request->total_amount;

        $final_discount_amount = (float)$discount_amount * (float)$price;
        $final_total_amount = (float)$discount_amount + (float)$total_amount;
        $discount_type = $request->discount_type;
        $discount = (float)$final_discount_amount/(float)$final_total_amount;
        if($discount_type != NULL){
            if($discount_type == 'Flat'){
                $discount = round($discount);
            }
        }
        // discount end

        // product purchase
        $productSaleExchange = ProductSaleExchange::find($request->product_sale_exchange_id);
        $productSaleExchange ->user_id = $user_id;
        $productSaleExchange ->party_id = $request->party_id;
        $productSaleExchange ->warehouse_id = $warehouse_id;
        $productSaleExchange ->store_id = $store_id;
        $productSaleExchange ->discount_type = $request->discount_type ? $request->discount_type : NULL;
        $productSaleExchange ->discount_amount = $discount;
        $productSaleExchange ->previous_paid_amount = $previous_paid_amount;
        $productSaleExchange ->paid_amount = $request->paid_amount;
        $productSaleExchange ->back_amount = $request->back_amount;
        $productSaleExchange ->due_amount = $request->due_amount;
        $productSaleExchange ->total_vat_amount = $total_vat_amount;
        $productSaleExchange ->total_amount = $total_amount;
        $productSaleExchange ->sale_exchange_date = $date;
        $productSaleExchange ->sale_exchange_date_time = $date_time;
        $productSaleExchange->update();
        $affectedRows = $productSaleExchange->id;
        if($affectedRows)
        {
            // sale products
            foreach ($request->products as $data) {

                $product_sale_previous_detail_id =  $data['product_sale_previous_detail_id'];
                $product_id =  $data['product_id'];

                $barcode = Product::where('id',$product_id)->pluck('barcode')->first();
                $vat_amount = Product::where('id',$product_id)->pluck('vat_amount')->first();

                // product purchase detail
                $product_sale_previous_detail = ProductSalePreviousDetail::find($product_sale_previous_detail_id);
                $previous_sale_qty = $product_sale_previous_detail->qty;
                $product_sale_previous_detail->product_brand_id = $data['product_brand_id'] ? $data['product_brand_id'] : NULL;
                $product_sale_previous_detail->product_unit_id = $data['product_unit_id'] ? $data['product_unit_id'] : NULL;
                $product_sale_previous_detail->product_id = $product_id;
                $product_sale_previous_detail->barcode = $barcode;
                $product_sale_previous_detail->qty = $data['exchange_quantity'];
                $product_sale_previous_detail->price = $data['mrp_price'];
                $product_sale_previous_detail->vat_amount = $vat_amount;
                $product_sale_previous_detail->sub_total = ($data['exchange_quantity']*$data['mrp_price']) + ($data['exchange_quantity']*$vat_amount);
                $product_sale_previous_detail->sale_exchange_date = $date;
                $product_sale_previous_detail->update();

                // product stock
                $stock_row = Stock::where('warehouse_id',$warehouse_id)
                    ->where('store_id',$store_id)
                    ->where('product_id',$product_id)
                    ->latest()->first();
                $current_stock = $stock_row->current_stock;

                // warehouse store current stock
                $update_warehouse_store_current_stock = WarehouseStoreCurrentStock::where('warehouse_id',$warehouse_id)
                    ->where('store_id',$store_id)
                    ->where('product_id',$product_id)
                    ->first();
                $exists_current_stock = $update_warehouse_store_current_stock->current_stock;

                if($stock_row->stock_out != $data['exchange_quantity']){

                    if($data['exchange_quantity'] > $stock_row->stock_in){
                        $new_stock_out = $data['exchange_quantity'] - $previous_sale_qty;

                        $stock = new Stock();
                        $stock->ref_id=$request->product_sale_exchange_id;
                        $stock->user_id=$user_id;
                        $stock->product_unit_id= $data['product_unit_id'];
                        $stock->product_brand_id= $data['product_brand_id'] ? $data['product_brand_id'] : NULL;
                        $stock->product_id= $product_id;
                        $stock->stock_type='sale_exchange_increase';
                        $stock->warehouse_id= $warehouse_id;
                        $stock->store_id=$store_id;
                        $stock->stock_where='store';
                        $stock->stock_in_out='stock_out';
                        $stock->previous_stock=$current_stock;
                        $stock->stock_in=0;
                        $stock->stock_out=$new_stock_out;
                        $stock->current_stock=$current_stock + $new_stock_out;
                        $stock->stock_date=$date;
                        $stock->stock_date_time=$date_time;
                        $stock->save();

                        // warehouse current stock
                        $update_warehouse_store_current_stock->current_stock=$exists_current_stock + $new_stock_out;
                        $update_warehouse_store_current_stock->save();
                    }else{
                        $new_stock_in = $previous_sale_qty - $data['exchange_quantity'];

                        $stock = new Stock();
                        $stock->ref_id=$request->product_sale_exchange_id;
                        $stock->user_id=$user_id;
                        $stock->product_unit_id= $data['product_unit_id'];
                        $stock->product_brand_id= $data['product_brand_id'] ? $data['product_brand_id'] : NULL;
                        $stock->product_id= $product_id;
                        $stock->stock_type='sale_exchange_decrease';
                        $stock->warehouse_id= $warehouse_id;
                        $stock->store_id=$store_id;
                        $stock->stock_where='store';
                        $stock->stock_in_out='stock_in';
                        $stock->previous_stock=$current_stock;
                        $stock->stock_in=$new_stock_in;
                        $stock->stock_out=0;
                        $stock->current_stock=$current_stock - $new_stock_in;
                        $stock->stock_date=$date;
                        $stock->stock_date_time=$date_time;
                        $stock->save();

                        // warehouse current stock
                        $update_warehouse_store_current_stock->current_stock=$exists_current_stock - $new_stock_in;
                        $update_warehouse_store_current_stock->save();
                    }
                }

            }

            // exchange products
            foreach ($request->exchange_products as $data) {

                $product_id =  $data['product_id'];
                $product_sale_exchange_detail_id =  $data['product_sale_exchange_detail_id'];

                $barcode = Product::where('id',$product_id)->pluck('barcode')->first();

                // product purchase detail
                $product_sale_exchange_detail = ProductSaleExchangeDetail::find($product_sale_exchange_detail_id);
                $product_sale_exchange_detail->product_unit_id = $data['product_unit_id'];
                $product_sale_exchange_detail->product_brand_id = $data['product_brand_id'] ? $data['product_brand_id'] : NULL;
                $product_sale_exchange_detail->product_id = $product_id;
                $product_sale_exchange_detail->barcode = $barcode;
                $product_sale_exchange_detail->qty = $data['qty'];
                $product_sale_exchange_detail->price = $data['mrp_price'];
                $product_sale_exchange_detail->vat_amount = $data['vat_amount'];
                $product_sale_exchange_detail->sub_total = ($data['qty']*$data['mrp_price']) + ($data['qty']*$data['vat_amount']);
                $product_sale_exchange_detail->sale_exchange_date = $date;
                $product_sale_exchange_detail->save();

                // product stock
                $stock_row = Stock::where('warehouse_id',$warehouse_id)
                    ->where('store_id',$store_id)
                    ->where('product_id',$product_id)
                    ->latest()->first();
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
                        $stock->ref_id=$request->product_sale_exchange_id;
                        $stock->user_id=$user_id;
                        $stock->product_unit_id= $data['product_unit_id'];
                        $stock->product_brand_id= $data['product_brand_id'] ? $data['product_brand_id'] : NULL;
                        $stock->product_id= $product_id;
                        $stock->stock_type='sale_exchange_increase';
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
                        $stock->ref_id=$request->product_sale_exchange_id;
                        $stock->user_id=$user_id;
                        $stock->product_unit_id= $data['product_unit_id'];
                        $stock->product_brand_id= $data['product_brand_id'] ? $data['product_brand_id'] : NULL;
                        $stock->product_id= $product_id;
                        $stock->stock_type='sale_exchange_decrease';
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
            $transaction = Transaction::where('ref_id',$request->product_sale_exchange_id)->first();
            $transaction->user_id = $user_id;
            $transaction->warehouse_id = $warehouse_id;
            $transaction->store_id = $store_id;
            $transaction->party_id = $request->party_id;
            $transaction->transaction_type = 'sale_exchange';
            $transaction->payment_type = $request->payment_type;
            $transaction->amount = $request->paid_amount;
            $transaction->transaction_date = $date;
            $transaction->transaction_date_time = $date_time;
            $transaction->update();

            // payment paid
            $payment_collection = PaymentCollection::where('product_sale_exchange_id',$request->product_sale_exchange_id)->first();
            $payment_collection->user_id = $user_id;
            $payment_collection->party_id = $request->party_id;
            $payment_collection->warehouse_id = $warehouse_id;
            $payment_collection->store_id = $store_id;
            $payment_collection->collection_type = 'Sale';
            $payment_collection->collection_amount = $request->paid_amount;
            $payment_collection->due_amount = $request->due_amount;
            $payment_collection->current_collection_amount = $request->paid_amount;
            $payment_collection->collection_date = $date;
            $payment_collection->collection_date_time = $date_time;
            $payment_collection->update();

            return response()->json(['success'=>true,'product_pos_sale' => 'Inserted Successfully!'], $this->successStatus);

        }else{
            return response()->json(['success'=>false,'response'=>'No Inserted Successfully!'], $this->failStatus);
        }
    }

    public function productSaleExchangeDelete(Request $request){
        $check_exists_product_sale_exchange = DB::table("product_sale_exchanges")->where('id',$request->product_sale_exchange_id)->pluck('id')->first();
        if($check_exists_product_sale_exchange == null){
            return response()->json(['success'=>false,'response'=>'No Product Sale Exchange Found!'], $this->failStatus);
        }

        $productSaleExchange = ProductSaleExchange::find($request->product_sale_exchange_id);
        if($productSaleExchange){
            $user_id = Auth::user()->id;
            $date = date('Y-m-d');
            $date_time = date('Y-m-d H:i:s');

            // product sale previous details
            $product_sale_previous_details = DB::table('product_sale_previous_details')->where('pro_sale_ex_id',$request->product_sale_exchange_id)->get();

            if(count($product_sale_previous_details) > 0){
                foreach ($product_sale_previous_details as $product_sale_previous_detail){
                    // current stock
                    $stock_row = Stock::where('stock_where','store')->where('warehouse_id',$productSaleExchange->warehouse_id)
                        ->where('product_id',$product_sale_previous_detail->product_id)
                        ->latest('id')->first();
                    $current_stock = $stock_row->current_stock;

                    $stock = new Stock();
                    $stock->ref_id=$productSaleExchange->id;
                    $stock->user_id=$user_id;
                    $stock->product_unit_id= $product_sale_previous_detail->product_unit_id;
                    $stock->product_brand_id= $product_sale_previous_detail->product_brand_id;
                    $stock->product_id= $product_sale_previous_detail->product_id;
                    $stock->stock_type='sale_exchange_delete';
                    $stock->warehouse_id= $productSaleExchange->warehouse_id;
                    $stock->store_id=$productSaleExchange->store_id;
                    $stock->stock_where='store';
                    $stock->stock_in_out='stock_out';
                    $stock->previous_stock=$current_stock;
                    $stock->stock_in=0;
                    $stock->stock_out=$product_sale_previous_detail->qty;
                    $stock->current_stock=$current_stock - $product_sale_previous_detail->qty;
                    $stock->stock_date=$date;
                    $stock->stock_date_time=$date_time;
                    $stock->save();


                    $warehouse_store_current_stock = WarehouseStoreCurrentStock::where('warehouse_id',$productSaleExchange->warehouse_id)
                        ->where('store_id',$productSaleExchange->store_id)
                        ->where('product_id',$product_sale_previous_detail->product_id)
                        ->first();
                    $exists_current_stock = $warehouse_store_current_stock->current_stock;
                    $warehouse_store_current_stock->current_stock=$exists_current_stock - $product_sale_previous_detail->qty;
                    $warehouse_store_current_stock->update();
                }
            }

            // product sale exchange details
            $product_sale_exchange_details = DB::table('product_sale_exchange_details')->where('pro_sale_ex_id',$request->product_sale_exchange_id)->get();

            if(count($product_sale_exchange_details) > 0){
                foreach ($product_sale_exchange_details as $product_sale_exchange_detail){
                    // current stock
                    $stock_row = Stock::where('stock_where','store')->where('warehouse_id',$productSaleExchange->warehouse_id)
                        ->where('product_id',$product_sale_exchange_detail->product_id)
                        ->latest('id')->first();
                    $current_stock = $stock_row->current_stock;

                    $stock = new Stock();
                    $stock->ref_id=$productSaleExchange->id;
                    $stock->user_id=$user_id;
                    $stock->product_unit_id= $product_sale_exchange_detail->product_unit_id;
                    $stock->product_brand_id= $product_sale_exchange_detail->product_brand_id;
                    $stock->product_id= $product_sale_exchange_detail->product_id;
                    $stock->stock_type='sale_exchange_delete';
                    $stock->warehouse_id= $productSaleExchange->warehouse_id;
                    $stock->store_id=$productSaleExchange->store_id;
                    $stock->stock_where='store';
                    $stock->stock_in_out='stock_in';
                    $stock->previous_stock=$current_stock;
                    $stock->stock_in=$product_sale_exchange_detail->qty;
                    $stock->stock_out=0;
                    $stock->current_stock=$current_stock + $product_sale_exchange_detail->qty;
                    $stock->stock_date=$date;
                    $stock->stock_date_time=$date_time;
                    $stock->save();


                    $warehouse_store_current_stock = WarehouseStoreCurrentStock::where('warehouse_id',$productSaleExchange->warehouse_id)
                        ->where('store_id',$productSaleExchange->store_id)
                        ->where('product_id',$product_sale_exchange_detail->product_id)
                        ->first();
                    $exists_current_stock = $warehouse_store_current_stock->current_stock;
                    $warehouse_store_current_stock->current_stock=$exists_current_stock + $product_sale_exchange_detail->qty;
                    $warehouse_store_current_stock->update();
                }
            }
        }
        $delete_sale = $productSaleExchange->delete();

        DB::table('product_sale_previous_details')->where('pro_sale_ex_id',$request->product_sale_exchange_id)->delete();
        DB::table('product_sale_exchange_details')->where('pro_sale_ex_id',$request->product_sale_exchange_id)->delete();
        //DB::table('stocks')->where('ref_id',$request->product_sale_id)->delete();
        DB::table('transactions')->where('ref_id',$request->product_sale_exchange_id)->delete();
        DB::table('payment_collections')->where('product_sale_exchange_id',$request->product_sale_exchange_id)->delete();

        if($delete_sale)
        {
            return response()->json(['success'=>true,'response' =>'Sale Successfully Deleted!'], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'Sale Exchange Not Deleted!'], $this->failStatus);
        }
    }




}
