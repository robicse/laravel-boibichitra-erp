<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Party;
use App\PaymentCollection;
use App\Product;
use App\ProductSale;
use App\ProductSaleDetail;
use App\ProductSaleReturn;
use App\ProductSaleReturnDetail;
use App\Stock;
use App\Store;
use App\Transaction;
use App\WarehouseCurrentStock;
use App\WarehouseStoreCurrentStock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SaleController extends Controller
{
    public $successStatus = 200;
    public $authStatus = 401;
    public $failStatus = 402;
    public $ExistsStatus = 403;
    public $validationStatus = 404;

    public function productWholeSaleList(){
        $product_whole_sales = DB::table('product_sales')
            ->leftJoin('users','product_sales.user_id','users.id')
            ->leftJoin('parties','product_sales.party_id','parties.id')
            ->leftJoin('warehouses','product_sales.warehouse_id','warehouses.id')
            //->leftJoin('stores','product_sales.store_id','stores.id')
            ->where('product_sales.sale_type','whole_sale')
            //->select('product_sales.id','product_sales.invoice_no','product_sales.discount_type','product_sales.discount_amount','product_sales.total_vat_amount','product_sales.total_amount','product_sales.paid_amount','product_sales.due_amount','product_sales.sale_date_time','users.name as user_name','parties.id as customer_id','parties.name as customer_name','warehouses.id as warehouse_id','warehouses.name as warehouse_name','stores.id as store_id','stores.name as store_name','stores.address as store_address')
            ->select('product_sales.id','product_sales.invoice_no','product_sales.discount_type','product_sales.discount_amount','product_sales.total_vat_amount','product_sales.total_amount','product_sales.paid_amount','product_sales.due_amount','product_sales.sale_date_time','users.name as user_name','parties.id as customer_id','parties.name as customer_name','warehouses.id as warehouse_id','warehouses.name as warehouse_name')
            ->orderBy('product_sales.id','desc')
            ->get();

        if(count($product_whole_sales) > 0)
        {
            $product_whole_sale_arr = [];
            foreach ($product_whole_sales as $data){
                $payment_type = DB::table('transactions')->where('ref_id',$data->id)->where('transaction_type','whole_sale')->pluck('payment_type')->first();

                $nested_data['id']=$data->id;
                $nested_data['invoice_no']=$data->invoice_no;
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

    public function productWholeSaleDetails(Request $request){
        $product_sale_details = DB::table('product_sales')
            ->join('product_sale_details','product_sales.id','product_sale_details.product_sale_id')
            ->leftJoin('products','product_sale_details.product_id','products.id')
            ->leftJoin('product_units','product_sale_details.product_unit_id','product_units.id')
            ->leftJoin('product_brands','product_sale_details.product_brand_id','product_brands.id')
            ->where('product_sales.id',$request->product_sale_id)
            ->select('products.id as product_id','products.name as product_name','product_units.id as product_unit_id','product_units.name as product_unit_name','product_brands.id as product_brand_id','product_brands.name as product_brand_name','product_sale_details.qty','product_sale_details.id as product_sale_detail_id','product_sale_details.price as mrp_price','product_sale_details.vat_amount')
            ->get();

        if($product_sale_details)
        {
            $success['product_whole_sale_details'] =  $product_sale_details;
            return response()->json(['success'=>true,'response' => $success], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Product Whole Sale Detail Found!'], $this->failStatus);
        }
    }

    public function productWholeSaleCreate(Request $request){

        $this->validate($request, [
            'party_id'=> 'required',
            //'store_id'=> 'required',
            'warehouse_id'=> 'required',
            'paid_amount'=> 'required',
            'due_amount'=> 'required',
            'total_amount'=> 'required',
            'payment_type'=> 'required',
        ]);

        $get_invoice_no = ProductSale::latest()->pluck('invoice_no')->first();
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
        //$store_id = $request->store_id;
        $warehouse_id = $request->warehouse_id;
        //$warehouse_id = Store::where('id',$store_id)->pluck('warehouse_id')->first();

        // product purchase
        $productSale = new ProductSale();
        $productSale->invoice_no = $final_invoice;
        $productSale->user_id = $user_id;
        //$productSale->store_id = $store_id;
        $productSale->store_id = NULL;
        $productSale->warehouse_id = $warehouse_id;
        $productSale->party_id = $request->party_id;
        $productSale->sale_type = 'whole_sale';
        $productSale->discount_type = $request->discount_type ? $request->discount_type : NULL;
        $productSale->discount_amount = $request->discount_amount ? $request->discount_amount : 0;
        $productSale->miscellaneous_comment = $request->miscellaneous_comment ? $request->miscellaneous_comment : NULL;
        $productSale->miscellaneous_charge = $request->miscellaneous_charge ? $request->miscellaneous_charge : 0;
        $productSale->paid_amount = $request->paid_amount;
        $productSale->due_amount = $request->due_amount;
        $productSale->total_vat_amount = $request->total_vat_amount;
        $productSale->total_amount = $request->total_amount;
        $productSale->sale_date = $date;
        $productSale->sale_date_time = $date_time;
        $productSale->save();
        $insert_id = $productSale->id;

        if($insert_id)
        {
            // for postman testing

            // for live testing
            foreach ($request->products as $data) {

                $product_id =  $data['product_id'];

                $barcode = Product::where('id',$product_id)->pluck('barcode')->first();

                // product sale detail
                $product_sale_detail = new ProductSaleDetail();
                $product_sale_detail->product_sale_id = $insert_id;
                $product_sale_detail->product_unit_id = $data['product_unit_id'];
                $product_sale_detail->product_brand_id = $data['product_brand_id'] ? $data['product_brand_id'] : NULL;
                $product_sale_detail->product_id = $product_id;
                $product_sale_detail->qty = $data['qty'];
                $product_sale_detail->price = $data['mrp_price'];
                $product_sale_detail->vat_amount = $data['vat_amount'];
                $product_sale_detail->sub_total = ($data['qty']*$data['mrp_price']) + ($data['qty']*$data['vat_amount']);
                $product_sale_detail->barcode = $barcode;
                $product_sale_detail->sale_date = $date;
                $product_sale_detail->return_among_day = 2;
                $product_sale_detail->return_last_date = $add_two_day_date;
                $product_sale_detail->save();

                //$check_previous_stock = Stock::where('warehouse_id',$warehouse_id)->where('store_id',$store_id)->where('stock_where','store')->where('product_id',$product_id)->latest()->pluck('current_stock')->first();
                $check_previous_stock = Stock::where('warehouse_id',$warehouse_id)->where('stock_where','store')->where('product_id',$product_id)->latest()->pluck('current_stock')->first();
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
                //$stock->store_id = $store_id;
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

            // transaction
            $transaction = new Transaction();
            $transaction->ref_id = $insert_id;
            $transaction->invoice_no = $final_invoice;
            $transaction->user_id = $user_id;
            $transaction->warehouse_id = $warehouse_id;
            //$transaction->store_id = $store_id;
            $transaction->store_id = NULL;
            $transaction->party_id = $request->party_id;
            $transaction->transaction_type = 'whole_sale';
            $transaction->payment_type = $request->payment_type;
            $transaction->amount = $request->paid_amount;
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
            $payment_collection->collection_type = 'Sale';
            $payment_collection->collection_amount = $request->paid_amount;
            $payment_collection->due_amount = $request->due_amount;
            $payment_collection->current_collection_amount = $request->paid_amount;
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

    public function productWholeSaleEdit(Request $request){
        //dd($request->all());
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

        $user_id = Auth::user()->id;
        $date = date('Y-m-d');
        $date_time = date('Y-m-d H:i:s');
        $store_id = $request->store_id;
        $warehouse_id = $request->warehouse_id;


        // product purchase
        $productSale = ProductSale::find($request->product_sale_id);
        $productSale->user_id = $user_id;
        $productSale->party_id = $request->party_id;
        $productSale->warehouse_id = $warehouse_id;
        $productSale->store_id = NULL;
        $productSale->discount_type = $request->discount_type ? $request->discount_type : NULL;
        $productSale->discount_amount = $request->discount_amount ? $request->discount_amount : 0;
        $productSale->miscellaneous_comment = $request->miscellaneous_comment ? $request->miscellaneous_comment : NULL;
        $productSale->miscellaneous_charge = $request->miscellaneous_charge ? $request->miscellaneous_charge : 0;
        $productSale->paid_amount = $request->paid_amount;
        $productSale->due_amount = $request->due_amount;
        $productSale->total_vat_amount = $request->total_vat_amount;
        $productSale->total_amount = $request->total_amount;
        $productSale->update();
        $affectedRows = $productSale->id;
        if($affectedRows)
        {
            foreach ($request->products as $data) {
                $product_id = $data['product_id'];
                $barcode = Product::where('id',$product_id)->pluck('barcode')->first();

                $product_sale_detail_id = $data['product_sale_detail_id'];
                // product purchase detail
                $product_sale_detail = ProductSaleDetail::find($product_sale_detail_id);
                $previous_sale_qty = $product_sale_detail->qty;
                $product_sale_detail->product_unit_id = $data['product_unit_id'];
                $product_sale_detail->product_brand_id = $data['product_brand_id'] ? $data['product_brand_id'] : NULL;
                $product_sale_detail->product_id = $product_id;
                $product_sale_detail->qty = $data['qty'];
                $product_sale_detail->vat_amount = $data['vat_amount'];
                $product_sale_detail->price = $data['mrp_price'];
                $product_sale_detail->sub_total = ($data['qty']*$data['mrp_price']) + ($data['qty']*$data['vat_amount']);
                $product_sale_detail->barcode = $barcode;
                $product_sale_detail->update();


                // product stock
                // product stock
                $stock_row = Stock::where('warehouse_id',$warehouse_id)->where('store_id',NULL)->where('product_id',$product_id)->latest()->first();
                $current_stock = $stock_row->current_stock;

                // warehouse current stock
                $update_warehouse_current_stock = WarehouseCurrentStock::where('warehouse_id',$warehouse_id)
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

            // transaction
            $transaction = Transaction::where('ref_id',$request->product_sale_id)->first();
            $transaction->user_id = $user_id;
            $transaction->warehouse_id = $warehouse_id;
            $transaction->store_id = NULL;
            $transaction->party_id = $request->party_id;
            $transaction->payment_type = $request->payment_type;
            $transaction->amount = $request->paid_amount;
            $transaction->update();

            // payment paid
            $payment_collection = PaymentCollection::where('product_sale_id',$request->product_sale_id)->first();
            $payment_collection->user_id = $user_id;
            $payment_collection->party_id = $request->party_id;
            $payment_collection->warehouse_id = $warehouse_id;
            $payment_collection->store_id = NULL;
            $payment_collection->collection_amount = $request->paid_amount;
            $payment_collection->due_amount = $request->due_amount;
            $payment_collection->current_collection_amount = $request->paid_amount;
            $payment_collection->update();


            return response()->json(['success'=>true,'response' => 'Updated Successfully.'], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Updated Successfully!'], $this->failStatus);
        }
    }

    public function productWholeSaleDelete(Request $request){
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
        DB::table('transactions')->where('ref_id',$request->product_sale_id)->delete();
        DB::table('payment_collections')->where('product_sale_id',$request->product_sale_id)->delete();


        if($delete_sale)
        {
            return response()->json(['success'=>true,'response' =>'Sale Successfully Deleted!'], $this->successStatus);
        }else{
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
                $nested_data['invoice_no']=$data->invoice_no;
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



    public function productPOSSaleDetails(Request $request){
        $product_sale_details = DB::table('product_sales')
            ->join('product_sale_details','product_sales.id','product_sale_details.product_sale_id')
            ->leftJoin('products','product_sale_details.product_id','products.id')
            ->leftJoin('product_units','product_sale_details.product_unit_id','product_units.id')
            ->leftJoin('product_brands','product_sale_details.product_brand_id','product_brands.id')
            ->where('product_sales.id',$request->product_sale_id)
            ->select('products.id as product_id','products.name as product_name','product_units.id as product_unit_id','product_units.name as product_unit_name','product_brands.id as product_brand_id','product_brands.name as product_brand_name','product_sale_details.qty','product_sale_details.id as product_sale_detail_id','product_sale_details.price as mrp_price','product_sale_details.vat_amount')
            ->get();

        if($product_sale_details)
        {
            $success['product_pos_sale_details'] =  $product_sale_details;
            return response()->json(['success'=>true,'response' => $success], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Product POS Sale Detail Found!'], $this->failStatus);
        }
    }


    public function productPOSSaleCreate(Request $request){

        $this->validate($request, [
            'party_id'=> 'required',
            'store_id'=> 'required',
            'paid_amount'=> 'required',
            'due_amount'=> 'required',
            'total_amount'=> 'required',
            'payment_type'=> 'required',
        ]);

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
        $productSale ->invoice_no = $final_invoice;
        $productSale ->user_id = $user_id;
        $productSale ->party_id = $request->party_id;
        $productSale ->warehouse_id = $warehouse_id;
        $productSale ->store_id = $store_id;
        $productSale ->sale_type = 'pos_sale';
        $productSale ->discount_type = $request->discount_type ? $request->discount_type : NULL;
        $productSale ->discount_amount = $request->discount_amount ? $request->discount_amount : 0;
        $productSale ->paid_amount = $request->paid_amount;
        $productSale ->due_amount = $request->due_amount;
        $productSale ->total_vat_amount = $request->total_vat_amount;
        $productSale ->total_amount = $request->total_amount;
        $productSale ->sale_date = $date;
        $productSale ->sale_date_time = $date_time;
        $productSale->save();
        $insert_id = $productSale->id;

        if($insert_id)
        {
            // for live testing
            foreach ($request->products as $data) {

                $product_id =  $data['product_id'];

                $barcode = Product::where('id',$product_id)->pluck('barcode')->first();




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



                // product purchase detail
                $product_sale_detail = new ProductSaleDetail();
                $product_sale_detail->product_sale_id = $insert_id;
                $product_sale_detail->product_unit_id = $data['product_unit_id'];
                $product_sale_detail->product_brand_id = $data['product_brand_id'] ? $data['product_brand_id'] : NULL;
                $product_sale_detail->product_id = $product_id;
                $product_sale_detail->barcode = $barcode;
                $product_sale_detail->qty = $data['qty'];
                $product_sale_detail->discount = $discount;
                $product_sale_detail->price = $data['mrp_price'];
                $product_sale_detail->vat_amount = $data['vat_amount'];
                $product_sale_detail->sub_total = ($data['qty']*$data['mrp_price']) + ($data['qty']*$data['vat_amount']);
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
            $transaction->amount = $request->paid_amount;
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
            $payment_collection->collection_amount = $request->paid_amount;
            $payment_collection->due_amount = $request->due_amount;
            $payment_collection->current_collection_amount = $request->paid_amount;
            $payment_collection->collection_date = $date;
            $payment_collection->collection_date_time = $date_time;
            $payment_collection->save();






            // posting
//            $transaction_date = $request->date;
//            $month = date('m', strtotime($request->date));
//            $year = date('Y', strtotime($request->date));
//            $transaction_date_time = date('Y-m-d H:i:s');
//
//            $get_voucher_name = VoucherType::where('id',2)->pluck('name')->first();
//            $get_voucher_no = ChartOfAccountTransaction::where('voucher_type_id',2)->latest()->pluck('voucher_no')->first();
//            if(!empty($get_voucher_no)){
//                $get_voucher_name_str = $get_voucher_name."-";
//                $get_voucher = str_replace($get_voucher_name_str,"",$get_voucher_no);
//                $voucher_no = $get_voucher+1;
//            }else{
//                $voucher_no = 8000;
//            }
//            $final_voucher_no = $get_voucher_name.'-'.$voucher_no;
//            $chart_of_account_transactions = new ChartOfAccountTransaction();
//            $chart_of_account_transactions->user_id = $user_id;
//            $chart_of_account_transactions->store_id = $store_id;
//            $chart_of_account_transactions->voucher_type_id = 2;
//            $chart_of_account_transactions->voucher_no = $final_voucher_no;
//            $chart_of_account_transactions->is_approved = 'approved';
//            $chart_of_account_transactions->transaction_date = $transaction_date;
//            $chart_of_account_transactions->transaction_date_time = $transaction_date_time;
//            $chart_of_account_transactions->save();
//            $chart_of_account_transactions_insert_id = $chart_of_account_transactions->id;
//
//            if($chart_of_account_transactions_insert_id){
//
//                // sales
//                $sales_chart_of_account_info = ChartOfAccount::where('head_name','Sales')->first();
//                $chart_of_account_transaction_details = new ChartOfAccountTransactionDetail();
//                $chart_of_account_transaction_details->chart_of_account_transaction_id = $insert_id;
//                $chart_of_account_transaction_details->chart_of_account_id = $sales_chart_of_account_info->id;
//                $chart_of_account_transaction_details->chart_of_account_number = $sales_chart_of_account_info->head_code;
//                $chart_of_account_transaction_details->chart_of_account_name = 'Sales';
//                $chart_of_account_transaction_details->chart_of_account_parent_name = $sales_chart_of_account_info->parent_head_name;
//                $chart_of_account_transaction_details->chart_of_account_type = $sales_chart_of_account_info->head_type;
//                $chart_of_account_transaction_details->debit = NULL;
//                $chart_of_account_transaction_details->credit = $request->paid_amount;
//                $chart_of_account_transaction_details->description = 'Income From Sales';
//                $chart_of_account_transaction_details->year = $year;
//                $chart_of_account_transaction_details->month = $month;
//                $chart_of_account_transaction_details->transaction_date = $transaction_date;
//                $chart_of_account_transaction_details->transaction_date_time = $transaction_date_time;
//                $chart_of_account_transaction_details->save();
//
//                // cash
//                if($request->payment_type == 'Cash'){
//                    $cash_chart_of_account_info = ChartOfAccount::where('head_name','Cash')->first();
//                    $chart_of_account_transaction_details = new ChartOfAccountTransactionDetail();
//                    $chart_of_account_transaction_details->chart_of_account_transaction_id = $insert_id;
//                    $chart_of_account_transaction_details->chart_of_account_id = $cash_chart_of_account_info->id;
//                    $chart_of_account_transaction_details->chart_of_account_number = $cash_chart_of_account_info->head_code;
//                    $chart_of_account_transaction_details->chart_of_account_name = 'Sales';
//                    $chart_of_account_transaction_details->chart_of_account_parent_name = $cash_chart_of_account_info->parent_head_name;
//                    $chart_of_account_transaction_details->chart_of_account_type = $cash_chart_of_account_info->head_type;
//                    $chart_of_account_transaction_details->debit = $request->paid_amount;
//                    $chart_of_account_transaction_details->credit = NULL;
//                    $chart_of_account_transaction_details->description = 'Cash In For Sales';
//                    $chart_of_account_transaction_details->year = $year;
//                    $chart_of_account_transaction_details->month = $month;
//                    $chart_of_account_transaction_details->transaction_date = $transaction_date;
//                    $chart_of_account_transaction_details->transaction_date_time = $transaction_date_time;
//                    $chart_of_account_transaction_details->save();
//                }elseif($request->payment_type == 'Check'){
//                    $cash_chart_of_account_info = ChartOfAccount::where('head_name','Check')->first();
//                    $chart_of_account_transaction_details = new ChartOfAccountTransactionDetail();
//                    $chart_of_account_transaction_details->chart_of_account_transaction_id = $insert_id;
//                    $chart_of_account_transaction_details->chart_of_account_id = $cash_chart_of_account_info->id;
//                    $chart_of_account_transaction_details->chart_of_account_number = $cash_chart_of_account_info->head_code;
//                    $chart_of_account_transaction_details->chart_of_account_name = 'Check';
//                    $chart_of_account_transaction_details->chart_of_account_parent_name = $cash_chart_of_account_info->parent_head_name;
//                    $chart_of_account_transaction_details->chart_of_account_type = $cash_chart_of_account_info->head_type;
//                    $chart_of_account_transaction_details->debit = $request->paid_amount;
//                    $chart_of_account_transaction_details->credit = NULL;
//                    $chart_of_account_transaction_details->description = 'Check In For Sales';
//                    $chart_of_account_transaction_details->year = $year;
//                    $chart_of_account_transaction_details->month = $month;
//                    $chart_of_account_transaction_details->transaction_date = $transaction_date;
//                    $chart_of_account_transaction_details->transaction_date_time = $transaction_date_time;
//                    $chart_of_account_transaction_details->save();
//                }elseif($request->payment_type == 'Card'){
//                    $cash_chart_of_account_info = ChartOfAccount::where('head_name','Card')->first();
//                    $chart_of_account_transaction_details = new ChartOfAccountTransactionDetail();
//                    $chart_of_account_transaction_details->chart_of_account_transaction_id = $insert_id;
//                    $chart_of_account_transaction_details->chart_of_account_id = $cash_chart_of_account_info->id;
//                    $chart_of_account_transaction_details->chart_of_account_number = $cash_chart_of_account_info->head_code;
//                    $chart_of_account_transaction_details->chart_of_account_name = 'Card';
//                    $chart_of_account_transaction_details->chart_of_account_parent_name = $cash_chart_of_account_info->parent_head_name;
//                    $chart_of_account_transaction_details->chart_of_account_type = $cash_chart_of_account_info->head_type;
//                    $chart_of_account_transaction_details->debit = $request->paid_amount;
//                    $chart_of_account_transaction_details->credit = NULL;
//                    $chart_of_account_transaction_details->description = 'Card In For Sales';
//                    $chart_of_account_transaction_details->year = $year;
//                    $chart_of_account_transaction_details->month = $month;
//                    $chart_of_account_transaction_details->transaction_date = $transaction_date;
//                    $chart_of_account_transaction_details->transaction_date_time = $transaction_date_time;
//                    $chart_of_account_transaction_details->save();
//                }elseif($request->payment_type == 'Bkash'){
//                    $cash_chart_of_account_info = ChartOfAccount::where('head_name','Bkash')->first();
//                    $chart_of_account_transaction_details = new ChartOfAccountTransactionDetail();
//                    $chart_of_account_transaction_details->chart_of_account_transaction_id = $insert_id;
//                    $chart_of_account_transaction_details->chart_of_account_id = $cash_chart_of_account_info->id;
//                    $chart_of_account_transaction_details->chart_of_account_number = $cash_chart_of_account_info->head_code;
//                    $chart_of_account_transaction_details->chart_of_account_name = 'Bkash';
//                    $chart_of_account_transaction_details->chart_of_account_parent_name = $cash_chart_of_account_info->parent_head_name;
//                    $chart_of_account_transaction_details->chart_of_account_type = $cash_chart_of_account_info->head_type;
//                    $chart_of_account_transaction_details->debit = $request->paid_amount;
//                    $chart_of_account_transaction_details->credit = NULL;
//                    $chart_of_account_transaction_details->description = 'Bkash In For Sales';
//                    $chart_of_account_transaction_details->year = $year;
//                    $chart_of_account_transaction_details->month = $month;
//                    $chart_of_account_transaction_details->transaction_date = $transaction_date;
//                    $chart_of_account_transaction_details->transaction_date_time = $transaction_date_time;
//                    $chart_of_account_transaction_details->save();
//                }elseif($request->payment_type == 'Nogod'){
//                    $cash_chart_of_account_info = ChartOfAccount::where('head_name','Nogod')->first();
//                    $chart_of_account_transaction_details = new ChartOfAccountTransactionDetail();
//                    $chart_of_account_transaction_details->chart_of_account_transaction_id = $insert_id;
//                    $chart_of_account_transaction_details->chart_of_account_id = $cash_chart_of_account_info->id;
//                    $chart_of_account_transaction_details->chart_of_account_number = $cash_chart_of_account_info->head_code;
//                    $chart_of_account_transaction_details->chart_of_account_name = 'Nogod';
//                    $chart_of_account_transaction_details->chart_of_account_parent_name = $cash_chart_of_account_info->parent_head_name;
//                    $chart_of_account_transaction_details->chart_of_account_type = $cash_chart_of_account_info->head_type;
//                    $chart_of_account_transaction_details->debit = $request->paid_amount;
//                    $chart_of_account_transaction_details->credit = NULL;
//                    $chart_of_account_transaction_details->description = 'Nogod In For Sales';
//                    $chart_of_account_transaction_details->year = $year;
//                    $chart_of_account_transaction_details->month = $month;
//                    $chart_of_account_transaction_details->transaction_date = $transaction_date;
//                    $chart_of_account_transaction_details->transaction_date_time = $transaction_date_time;
//                    $chart_of_account_transaction_details->save();
//                }elseif($request->payment_type == 'Rocket'){
//                    $cash_chart_of_account_info = ChartOfAccount::where('head_name','Rocket')->first();
//                    $chart_of_account_transaction_details = new ChartOfAccountTransactionDetail();
//                    $chart_of_account_transaction_details->chart_of_account_transaction_id = $insert_id;
//                    $chart_of_account_transaction_details->chart_of_account_id = $cash_chart_of_account_info->id;
//                    $chart_of_account_transaction_details->chart_of_account_number = $cash_chart_of_account_info->head_code;
//                    $chart_of_account_transaction_details->chart_of_account_name = 'Rocket';
//                    $chart_of_account_transaction_details->chart_of_account_parent_name = $cash_chart_of_account_info->parent_head_name;
//                    $chart_of_account_transaction_details->chart_of_account_type = $cash_chart_of_account_info->head_type;
//                    $chart_of_account_transaction_details->debit = $request->paid_amount;
//                    $chart_of_account_transaction_details->credit = NULL;
//                    $chart_of_account_transaction_details->description = 'Rocket In For Sales';
//                    $chart_of_account_transaction_details->year = $year;
//                    $chart_of_account_transaction_details->month = $month;
//                    $chart_of_account_transaction_details->transaction_date = $transaction_date;
//                    $chart_of_account_transaction_details->transaction_date_time = $transaction_date_time;
//                    $chart_of_account_transaction_details->save();
//                }elseif($request->payment_type == 'Upay'){
//                    $cash_chart_of_account_info = ChartOfAccount::where('head_name','Upay')->first();
//                    $chart_of_account_transaction_details = new ChartOfAccountTransactionDetail();
//                    $chart_of_account_transaction_details->chart_of_account_transaction_id = $insert_id;
//                    $chart_of_account_transaction_details->chart_of_account_id = $cash_chart_of_account_info->id;
//                    $chart_of_account_transaction_details->chart_of_account_number = $cash_chart_of_account_info->head_code;
//                    $chart_of_account_transaction_details->chart_of_account_name = 'Upay';
//                    $chart_of_account_transaction_details->chart_of_account_parent_name = $cash_chart_of_account_info->parent_head_name;
//                    $chart_of_account_transaction_details->chart_of_account_type = $cash_chart_of_account_info->head_type;
//                    $chart_of_account_transaction_details->debit = $request->paid_amount;
//                    $chart_of_account_transaction_details->credit = NULL;
//                    $chart_of_account_transaction_details->description = 'Upay In For Sales';
//                    $chart_of_account_transaction_details->year = $year;
//                    $chart_of_account_transaction_details->month = $month;
//                    $chart_of_account_transaction_details->transaction_date = $transaction_date;
//                    $chart_of_account_transaction_details->transaction_date_time = $transaction_date_time;
//                    $chart_of_account_transaction_details->save();
//                }else{
//
//                }
//            }






            if($request->payment_type == 'SSL Commerz'){
                $product_pos_sale = DB::table('product_sales')
                    ->leftJoin('users','product_sales.user_id','users.id')
                    ->leftJoin('parties','product_sales.party_id','parties.id')
                    ->leftJoin('warehouses','product_sales.warehouse_id','warehouses.id')
                    ->leftJoin('stores','product_sales.store_id','stores.id')
                    ->where('product_sales.sale_type','pos_sale')
                    ->where('product_sales.id',$insert_id)
                    ->select('product_sales.id','product_sales.invoice_no','product_sales.discount_type','product_sales.discount_amount','product_sales.total_vat_amount','product_sales.total_amount','product_sales.paid_amount','product_sales.due_amount','product_sales.sale_date_time','users.name as user_name','parties.id as customer_id','parties.name as customer_name','warehouses.id as warehouse_id','warehouses.name as warehouse_name','stores.id as store_id','stores.name as store_name','stores.address as store_address')
                    ->first();

                return response()->json(['success'=>true,'transaction_id' => $transaction_id,'payment_type' => $request->payment_type,'product_pos_sale' => $product_pos_sale], $this->successStatus);
            }else{

                $product_pos_sale = DB::table('product_sales')
                    ->leftJoin('users','product_sales.user_id','users.id')
                    ->leftJoin('parties','product_sales.party_id','parties.id')
                    ->leftJoin('warehouses','product_sales.warehouse_id','warehouses.id')
                    ->leftJoin('stores','product_sales.store_id','stores.id')
                    ->where('product_sales.sale_type','pos_sale')
                    ->where('product_sales.id',$insert_id)
                    ->select('product_sales.id','product_sales.invoice_no','product_sales.discount_type','product_sales.discount_amount','product_sales.total_vat_amount','product_sales.total_amount','product_sales.paid_amount','product_sales.due_amount','product_sales.sale_date_time','users.name as user_name','parties.id as customer_id','parties.name as customer_name','warehouses.id as warehouse_id','warehouses.name as warehouse_name','stores.id as store_id','stores.name as store_name','stores.address as store_address')
                    ->first();

                return response()->json(['success'=>true,'product_pos_sale' => $product_pos_sale], $this->successStatus);
            }
        }else{
            return response()->json(['success'=>false,'response'=>'No Inserted Successfully!'], $this->failStatus);
        }
    }

    public function productPOSSaleEdit(Request $request){
        //dd($request->all());
        $this->validate($request, [
            'product_sale_id'=> 'required',
            'party_id'=> 'required',
            'store_id'=> 'required',
            'paid_amount'=> 'required',
            'due_amount'=> 'required',
            'total_amount'=> 'required',
            'payment_type'=> 'required',
        ]);

        $user_id = Auth::user()->id;
        $date = date('Y-m-d');
        $date_time = date('Y-m-d H:i:s');
        $store_id = $request->store_id;
        $warehouse_id = Store::where('id',$store_id)->pluck('warehouse_id')->first();


        // product purchase
        $productSale = ProductSale::find($request->product_sale_id);
        $productSale ->user_id = $user_id;
        $productSale ->party_id = $request->party_id;
        $productSale ->warehouse_id = $warehouse_id;
        $productSale ->store_id = $store_id;
        $productSale ->discount_type = $request->discount_type ? $request->discount_type : NULL;
        $productSale ->discount_amount = $request->discount_amount ? $request->discount_amount : 0;
        $productSale ->paid_amount = $request->paid_amount;
        $productSale ->due_amount = $request->due_amount;
        $productSale ->total_vat_amount = $request->total_vat_amount;
        $productSale ->total_amount = $request->total_amount;
        $productSale->update();
        $affectedRows = $productSale->id;
        if($affectedRows)
        {
            foreach ($request->products as $data) {
                $product_id = $data['product_id'];
                $barcode = Product::where('id',$product_id)->pluck('barcode')->first();

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

                $product_sale_detail_id = $data['product_sale_detail_id'];
                // product purchase detail
                $purchase_sale_detail = ProductSaleDetail::find($product_sale_detail_id);
                $previous_sale_qty = $purchase_sale_detail->qty;
                $purchase_sale_detail->product_unit_id = $data['product_unit_id'];
                $purchase_sale_detail->product_brand_id = $data['product_brand_id'] ? $data['product_brand_id'] : NULL;
                $purchase_sale_detail->product_id = $product_id;
                $purchase_sale_detail->qty = $data['qty'];
                $purchase_sale_detail->discount = $discount;
                $purchase_sale_detail->price = $data['mrp_price'];
                $purchase_sale_detail->vat_amount = $data['vat_amount'];
                $purchase_sale_detail->sub_total = ($data['qty']*$data['mrp_price']) + ($data['qty']*$data['vat_amount']);
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
            $transaction->amount = $request->paid_amount;
            $transaction->update();

            // payment paid
            $payment_collection = PaymentCollection::where('product_sale_id',$request->product_sale_id)->first();
            $payment_collection->user_id = $user_id;
            $payment_collection->party_id = $request->party_id;
            $payment_collection->warehouse_id = $warehouse_id;
            $payment_collection->store_id = $store_id;
            $payment_collection->collection_amount = $request->paid_amount;
            $payment_collection->due_amount = $request->due_amount;
            $payment_collection->current_collection_amount = $request->paid_amount;
            $payment_collection->update();


            return response()->json(['success'=>true,'response' => 'Updated Successfully.'], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Updated Successfully!'], $this->failStatus);
        }
    }

    public function productPOSSaleDelete(Request $request){
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
            return response()->json(['success'=>true,'response' =>'Sale Successfully Deleted!'], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'Sale Not Deleted!'], $this->failStatus);
        }
    }

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
}