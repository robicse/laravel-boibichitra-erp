<?php

namespace App\Http\Controllers\API;

use App\Helpers\APIHelpers;
use App\Http\Controllers\Controller;
use App\Http\Resources\ProductPurchaseCollection;
use App\PaymentPaid;
use App\Product;
use App\ProductPurchase;
use App\ProductPurchaseDetail;
use App\ProductPurchaseReturn;
use App\ProductPurchaseReturnDetail;
use App\ProductSale;
use App\Stock;
use App\Transaction;
use App\WarehouseCurrentStock;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ProductPurchaseController extends Controller
{
    public $successStatus = 200;
    public $authStatus = 401;
    public $failStatus = 402;
    public $ExistsStatus = 403;
    public $validationStatus = 404;

    public function productUnitAndBrand(Request $request){
        $product_brand_and_unit = DB::table('products')
            ->leftJoin('product_units','products.product_unit_id','product_units.id')
            ->leftJoin('product_brands','products.product_brand_id','product_brands.id')
            ->where('products.id',$request->product_id)
            ->select('products.purchase_price','products.selling_price','product_units.id as product_unit_id','product_units.name as product_unit_name','product_brands.id as product_brand_id','product_brands.name as product_brand_name')
            ->get();

        if($product_brand_and_unit)
        {
            $success['product_brand_and_unit'] =  $product_brand_and_unit;
            return response()->json(['success'=>true,'response' => $success], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Product List Found!'], $this->failStatus);
        }
    }

    public function productWholePurchaseList(){
        $product_whole_purchases = DB::table('product_purchases')
            ->leftJoin('users','product_purchases.user_id','users.id')
            ->leftJoin('parties','product_purchases.party_id','parties.id')
            ->leftJoin('warehouses','product_purchases.warehouse_id','warehouses.id')
            ->where('product_purchases.purchase_type','whole_purchase')
            ->select('product_purchases.id','product_purchases.invoice_no','product_purchases.discount_type','product_purchases.discount_amount','product_purchases.total_amount','product_purchases.paid_amount','product_purchases.due_amount','product_purchases.purchase_date_time','users.name as user_name','parties.id as supplier_id','parties.name as supplier_name','warehouses.id as warehouse_id','warehouses.name as warehouse_name')
            ->orderBy('product_purchases.id','desc')
            ->get();

        if(count($product_whole_purchases) > 0)
        {
            $product_whole_purchase_arr = [];
            foreach ($product_whole_purchases as $data){
                $payment_type = DB::table('transactions')->where('ref_id',$data->id)->where('transaction_type','whole_purchase')->pluck('payment_type')->first();

                $nested_data['id']=$data->id;
                $nested_data['invoice_no']=ucfirst($data->invoice_no);
                $nested_data['discount_type']=$data->discount_type;
                $nested_data['discount_amount']=$data->discount_amount;
                //$nested_data['total_vat_amount']=$data->total_vat_amount;
                $nested_data['total_amount']=$data->total_amount;
                $nested_data['paid_amount']=$data->paid_amount;
                $nested_data['due_amount']=$data->due_amount;
                $nested_data['purchase_date_time']=$data->purchase_date_time;
                $nested_data['user_name']=$data->user_name;
                $nested_data['supplier_id']=$data->supplier_id;
                $nested_data['supplier_name']=$data->supplier_name;
                $nested_data['warehouse_id']=$data->warehouse_id;
                $nested_data['warehouse_name']=$data->warehouse_name;
                $nested_data['payment_type']=$payment_type;

                array_push($product_whole_purchase_arr,$nested_data);
            }
            $success['product_whole_purchases'] =  $product_whole_purchase_arr;
            return response()->json(['success'=>true,'response' => $success], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Product Whole Purchase List Found!'], $this->failStatus);
        }
    }

    public function productWholePurchaseListPagination(Request $request){

        return new ProductPurchaseCollection(ProductPurchase::where('purchase_type','whole_purchase')->latest()->paginate(12));
    }

    public function productWholePurchaseListPaginationWithSearch(Request $request){
        try {
            if($request->search){
                $product_whole_purchases = ProductPurchase::join('parties','product_purchases.party_id','parties.id')
                ->where('product_purchases.purchase_type','whole_purchase')
                ->where(function ($q) use ($request){
                    $q->where('product_purchases.invoice_no','like','%'.$request->search.'%')
                        ->orWhere('parties.name','like','%'.$request->search.'%');
                })
                ->select(
                    'product_purchases.id',
                    'product_purchases.invoice_no',
                    'product_purchases.sub_total',
                    'product_purchases.discount_type',
                    'product_purchases.discount_percent',
                    'product_purchases.discount_amount',
                    'product_purchases.total_amount',
                    'product_purchases.paid_amount',
                    'product_purchases.due_amount',
                    'product_purchases.purchase_date_time',
                    'product_purchases.user_id',
                    'product_purchases.party_id',
                    'product_purchases.warehouse_id'
                )
                ->latest('product_purchases.id','desc')->paginate(12);

            }else{
                $product_whole_purchases = ProductPurchase::where('purchase_type','whole_purchase')->latest()->paginate(12);
            }
            if($product_whole_purchases === null){
                $response = APIHelpers::createAPIResponse(true,404,'No Purchase Found.',null);
                return response()->json($response,404);
            }else{
                return new ProductPurchaseCollection($product_whole_purchases);
            }
        } catch (\Exception $e) {
            //return $e->getMessage();
            $response = APIHelpers::createAPIResponse(false,500,'Internal Server Error.',null);
            return response()->json($response,500);
        }
    }

    public function productWholePurchaseListSearch(Request $request){
        if($request->search) {
            $product_whole_purchase_lists = DB::table('product_purchases')
                ->join('product_purchase_details', 'product_purchases.id', 'product_purchase_details.product_purchase_id')
                ->leftJoin('products', 'product_purchase_details.product_id', 'products.id')
                ->leftJoin('product_units', 'product_purchase_details.product_unit_id', 'product_units.id')
                ->leftJoin('product_brands', 'product_purchase_details.product_brand_id', 'product_brands.id')
                ->leftJoin('parties','product_purchases.party_id','parties.id')
                ->where('product_purchases.invoice_no','like','%'.$request->search.'%')
                ->orWhere('parties.name','like','%'.$request->search.'%')
                ->select('products.id as product_id', 'products.name as product_name', 'product_units.id as product_unit_id', 'product_units.name as product_unit_name', 'product_brands.id as product_brand_id', 'product_brands.name as product_brand_name', 'product_purchase_details.qty', 'product_purchase_details.id as product_purchase_detail_id', 'product_purchase_details.price', 'product_purchase_details.mrp_price')
                ->paginate(12);

            if ($product_whole_purchase_lists) {
                $success['product_whole_purchase_lists'] = $product_whole_purchase_lists;
                return response()->json(['success' => true, 'response' => $success], $this->successStatus);
            } else {
                return response()->json(['success' => false, 'response' => 'No Product Whole Purchase List Found!'], $this->failStatus);
            }
        }
    }

    public function productWholePurchaseDetails(Request $request){
        $product_purchase_details = DB::table('product_purchases')
            ->join('product_purchase_details','product_purchases.id','product_purchase_details.product_purchase_id')
            ->leftJoin('products','product_purchase_details.product_id','products.id')
            ->leftJoin('product_units','product_purchase_details.product_unit_id','product_units.id')
            ->leftJoin('product_brands','product_purchase_details.product_brand_id','product_brands.id')
            ->where('product_purchases.id',$request->product_purchase_id)
            ->select(
                'product_purchases.warehouse_id',
                'products.id as product_id',
                'products.name as product_name',
                'product_units.id as product_unit_id',
                'product_units.name as product_unit_name',
                'product_brands.id as product_brand_id',
                'product_brands.name as product_brand_name',
                'product_purchase_details.qty',
                'product_purchase_details.id as product_purchase_detail_id',
                'product_purchase_details.price',
                'product_purchase_details.mrp_price'
            )
            ->get();

        $product_purchase_detail_arr = [];
        if(count($product_purchase_details) > 0){
            foreach ($product_purchase_details as $product_purchase_detail){
                $current_qty = warehouseProductCurrentStock($product_purchase_detail->warehouse_id,$product_purchase_detail->product_id);

                $nested_data['product_id'] = $product_purchase_detail->product_id;
                $nested_data['product_name'] = $product_purchase_detail->product_name;
                $nested_data['product_unit_id'] = $product_purchase_detail->product_unit_id;
                $nested_data['product_unit_name'] = $product_purchase_detail->product_unit_name;
                $nested_data['product_brand_id'] = $product_purchase_detail->product_brand_id;
                $nested_data['product_brand_name'] = $product_purchase_detail->product_brand_name;
                $nested_data['qty'] = $product_purchase_detail->qty;
                $nested_data['product_purchase_detail_id'] = $product_purchase_detail->product_purchase_detail_id;
                $nested_data['price'] = $product_purchase_detail->price;
                $nested_data['mrp_price'] = $product_purchase_detail->mrp_price;
                $nested_data['current_qty'] = $current_qty;


                array_push($product_purchase_detail_arr,$nested_data);

            }

            $supplier_details = DB::table('parties')
                ->join('product_purchases','product_purchases.party_id','parties.id')
                ->where('product_purchases.id',$request->product_purchase_id)
                ->select('parties.id as supplier_id','parties.name as supplier_name','parties.phone as supplier_phone','parties.address as supplier_address')
                ->first();

            $success['product_whole_purchase_details'] =  $product_purchase_detail_arr;
            $success['supplier_details'] =  $supplier_details;
            return response()->json(['success'=>true,'response' => $success], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Product Whole Purchase Detail Found!'], $this->failStatus);
        }
    }

    public function productWholePurchaseCreate(Request $request){


//        $test = [
//            "party_id" => 15,
//            "warehouse_id" => 1,
//            "products" => [
//                0 =>
//                    [
//                        "product_id" => 4,
//                        "product_name" => "test product",
//                        "product_unit_id" => 1,
//                        "product_unit_name" => "Pcs",
//                        "product_brand_id" => 4,
//                        "product_brand_name" => "Brand Test",
//                        "price" => 200,
//                        "mrp_price" => 250,
//                        "qty" => 1,
//                    ],
//                1 =>
//                    [
//                        "product_id" => 6,
//                        "product_name" => "test product 1",
//                        "product_unit_id" => 2,
//                        "product_unit_name" => "Set",
//                        "product_brand_id" => 5,
//                        "product_brand_name" => "Brand Test 1",
//                        "price" => 300,
//                        "mrp_price" => 350,
//                        "qty" => 2,
//                    ]
//            ],
//            "total_amount" => 500,
//            "paid_amount" => 400,
//            "due_amount" => 100,
//            "payment_type" => "Cash"
//        ];

//        dd($test['products'][0]['product_id']);

//        [0=>["product_id" => 4,"product_name" => "test product","product_unit_id" => 1,"product_unit_name" => "Pcs","product_brand_id" => 4,"product_brand_name" => "Brand Test","price" => 200,"qty" => 1], 1=>["product_id" => 6,"product_name" => "test product 1","product_unit_id" => 2,"product_unit_name" => "Set","product_brand_id" => 5,"product_brand_name" => "Brand Test 1","qty" => 2,]]




        //dd($request->all());
        //return response()->json(['success'=>true,'response' => $request->all()], $this->successStatus);







        try {
            $this->validate($request, [
                'party_id'=> 'required',
                'warehouse_id'=> 'required',
                'paid_amount'=> 'required',
                'due_amount'=> 'required',
                'total_amount'=> 'required',
                'payment_type'=> 'required',
            ]);

            $get_invoice_no = ProductPurchase::latest()->pluck('invoice_no')->first();
            if(!empty($get_invoice_no)){
                $get_invoice = str_replace("purchase-","",$get_invoice_no);
                $invoice_no = $get_invoice+1;
            }else{
                $invoice_no = 1000;
            }
            $final_invoice = 'purchase-'.$invoice_no;

            $date = date('Y-m-d');
            $date_time = date('Y-m-d h:i:s');

            $user_id = Auth::user()->id;

            // product purchase
            $productPurchase = new ProductPurchase();
            $productPurchase ->invoice_no = $final_invoice;
            $productPurchase ->user_id = $user_id;
            $productPurchase ->party_id = $request->party_id;
            $productPurchase ->warehouse_id = $request->warehouse_id;
            $productPurchase ->purchase_type = 'whole_purchase';
            $productPurchase ->sub_total = $request->sub_total;
            $productPurchase ->discount_type = $request->discount_type ? $request->discount_type : NULL;
            $productPurchase ->discount_percent = $request->discount_percent ? $request->discount_percent : 0;
            $productPurchase ->discount_amount = $request->discount_amount ? $request->discount_amount : 0;
            $productPurchase ->after_discount_amount = $request->after_discount_amount ? $request->after_discount_amount : 0;
            $productPurchase ->paid_amount = $request->paid_amount;
            $productPurchase ->due_amount = $request->due_amount;
            $productPurchase ->total_amount = $request->total_amount;
            $productPurchase ->purchase_date = $date;
            $productPurchase ->purchase_date_time = $date_time;
            $productPurchase->save();
            $insert_id = $productPurchase->id;

            if($insert_id)
            {
                foreach ($request->products as $data) {

                    $product_id =  $data['product_id'];

                    $barcode = Product::where('id',$product_id)->pluck('barcode')->first();

                    // product purchase detail
                    $purchase_purchase_detail = new ProductPurchaseDetail();
                    $purchase_purchase_detail->product_purchase_id = $insert_id;
                    $purchase_purchase_detail->product_unit_id = $data['product_unit_id'];
                    $purchase_purchase_detail->product_brand_id = $data['product_brand_id'] ? $data['product_brand_id'] : NULL;
                    $purchase_purchase_detail->product_id = $product_id;
                    $purchase_purchase_detail->qty = $data['qty'];
                    $purchase_purchase_detail->price = $data['price'];
                    $purchase_purchase_detail->mrp_price = $data['mrp_price'];
                    $purchase_purchase_detail->sub_total = $data['qty']*$data['price'];
                    $purchase_purchase_detail->barcode = $barcode;
                    $purchase_purchase_detail->save();

                    $check_previous_stock = Stock::where('product_id',$product_id)->latest()->pluck('current_stock')->first();
                    if(!empty($check_previous_stock)){
                        $previous_stock = $check_previous_stock;
                    }else{
                        $previous_stock = 0;
                    }

                    // product stock
                    $stock = new Stock();
                    $stock->ref_id = $insert_id;
                    $stock->user_id = $user_id;
                    $stock->warehouse_id = $request->warehouse_id;
                    $stock->product_id = $product_id;
                    $stock->product_unit_id = $data['product_unit_id'];
                    $stock->product_brand_id = $data['product_brand_id'] ? $data['product_brand_id'] : NULL;
                    $stock->stock_type = 'whole_purchase';
                    $stock->stock_where = 'warehouse';
                    $stock->stock_in_out = 'stock_in';
                    $stock->previous_stock = $previous_stock;
                    $stock->stock_in = $data['qty'];
                    $stock->stock_out = 0;
                    $stock->current_stock = $previous_stock + $data['qty'];
                    $stock->stock_date = $date;
                    $stock->stock_date_time = $date_time;
                    $stock->save();

                    // warehouse current stock
                    $check_exists_warehouse_current_stock = WarehouseCurrentStock::where('warehouse_id',$request->warehouse_id)
                        ->where('product_id',$product_id)
                        ->first();
                    if($check_exists_warehouse_current_stock){
                        $warehouse_current_stock_update = WarehouseCurrentStock::find($check_exists_warehouse_current_stock->id);
                        $warehouse_current_stock_update->current_stock=$check_exists_warehouse_current_stock->current_stock + $data['qty'];
                        $warehouse_current_stock_update->save();
                    }else{
                        $warehouse_current_stock = new WarehouseCurrentStock();
                        $warehouse_current_stock->warehouse_id=$request->warehouse_id;
                        $warehouse_current_stock->product_id=$product_id;
                        $warehouse_current_stock->current_stock=$data['qty'];
                        $warehouse_current_stock->save();
                    }
                }

                // transaction
                $transaction = new Transaction();
                $transaction->ref_id = $insert_id;
                $transaction->invoice_no = $final_invoice;
                $transaction->user_id = $user_id;
                $transaction->warehouse_id = $request->warehouse_id;
                $transaction->party_id = $request->party_id;
                $transaction->transaction_type = 'whole_purchase';
                $transaction->payment_type = $request->payment_type;
                $transaction->amount = $request->paid_amount;
                $transaction->transaction_date = $date;
                $transaction->transaction_date_time = $date_time;
                $transaction->save();
                $transaction_id = $transaction->id;

                // payment paid
                $payment_paid = new PaymentPaid();
                $payment_paid->invoice_no = $final_invoice;
                $payment_paid->product_purchase_id = $insert_id;
                $payment_paid->user_id = $user_id;
                $payment_paid->party_id = $request->party_id;
                $payment_paid->paid_type = 'Purchase';
                $payment_paid->paid_amount = $request->paid_amount;
                $payment_paid->due_amount = $request->due_amount;
                $payment_paid->current_paid_amount = $request->paid_amount;
                $payment_paid->paid_date = $date;
                $payment_paid->paid_date_time = $date_time;
                $payment_paid->save();


//                if($request->payment_type == 'SSL Commerz'){
//                    return response()->json(['success'=>true,'transaction_id' => $transaction_id,'payment_type' => $request->payment_type], $this->successStatus);
//                }else{
//                    return response()->json(['success'=>true,'response' => 'Inserted Successfully.'], $this->successStatus);
//                }
                $response = APIHelpers::createAPIResponse(false,201,'Product Purchase Added Successfully.',null);
                return response()->json($response,201);
            }else{
                $response = APIHelpers::createAPIResponse(true,400,'Product Purchase Updated Failed.',null);
                return response()->json($response,400);
            }
        } catch (\Exception $e) {
            //return $e->getMessage();
            $response = APIHelpers::createAPIResponse(false,500,'Internal Server Error.',null);
            return response()->json($response,500);
        }
    }

    public function productWholePurchaseEdit(Request $request){
        try {
            $this->validate($request, [
                'product_purchase_id'=> 'required',
                'party_id'=> 'required',
                'warehouse_id'=> 'required',
                'paid_amount'=> 'required',
                'due_amount'=> 'required',
                'total_amount'=> 'required',
                'payment_type'=> 'required',
            ]);

            $user_id = Auth::user()->id;
            $date = date('Y-m-d');
            $date_time = date('Y-m-d H:i:s');

            // product purchase
            $productPurchase = ProductPurchase::find($request->product_purchase_id);
            $productPurchase ->user_id = $user_id;
            $productPurchase ->party_id = $request->party_id;
            $productPurchase ->warehouse_id = $request->warehouse_id;
            $productPurchase ->sub_total = $request->sub_total;
            $productPurchase ->discount_type = $request->discount_type ? $request->discount_type : NULL;
            $productPurchase ->discount_percent = $request->discount_percent ? $request->discount_percent : 0;
            $productPurchase ->discount_amount = $request->discount_amount ? $request->discount_amount : 0;
            $productPurchase ->after_discount_amount = $request->after_discount_amount ? $request->after_discount_amount : 0;
            $productPurchase ->total_amount = $request->total_amount;
            $productPurchase ->paid_amount = $request->paid_amount;
            $productPurchase ->due_amount = $request->due_amount;
            $productPurchase->update();
            $affectedRows = $productPurchase->id;
            if($affectedRows)
            {
                foreach ($request->products as $data) {
                    $product_id = $data['product_id'];
                    $barcode = Product::where('id',$product_id)->pluck('barcode')->first();

                    $product_purchase_detail_id = $data['product_purchase_detail_id'];

                    if($product_purchase_detail_id != ''){
                        // product purchase detail
                        $purchase_purchase_detail = ProductPurchaseDetail::find($product_purchase_detail_id);
                        $previous_purchase_qty = $purchase_purchase_detail->qty;
                        $purchase_purchase_detail->product_unit_id = $data['product_unit_id'];
                        $purchase_purchase_detail->product_brand_id = $data['product_brand_id'] ? $data['product_brand_id'] : NULL;
                        $purchase_purchase_detail->product_id = $product_id;
                        $purchase_purchase_detail->qty = $data['qty'];
                        $purchase_purchase_detail->price = $data['price'];
                        $purchase_purchase_detail->mrp_price = $data['mrp_price'];
                        $purchase_purchase_detail->sub_total = $data['qty']*$data['price'];
                        $purchase_purchase_detail->barcode = $barcode;
                        $purchase_purchase_detail->update();

                        // product stock
                        $stock_row = Stock::where('warehouse_id',$request->warehouse_id)->where('product_id',$product_id)->latest()->first();
                        $current_stock = $stock_row->current_stock;

                        // warehouse current stock
                        $warehouse_current_stock_update = WarehouseCurrentStock::where('warehouse_id',$request->warehouse_id)
                            ->where('product_id',$product_id)
                            ->first();
                        $exists_current_stock = $warehouse_current_stock_update->current_stock;

                        if($stock_row->stock_in != $data['qty']){

                            if($data['qty'] > $stock_row->stock_in){
                                $new_stock_in = $data['qty'] - $previous_purchase_qty;

                                $stock = new Stock();
                                $stock->ref_id=$request->product_purchase_id;
                                $stock->user_id=$user_id;
                                $stock->product_unit_id= $data['product_unit_id'];
                                $stock->product_brand_id= $data['product_brand_id'] ? $data['product_brand_id'] : NULL;
                                $stock->product_id= $product_id;
                                $stock->stock_type='whole_purchase_increase';
                                $stock->warehouse_id= $productPurchase->warehouse_id;
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
                                $warehouse_current_stock_update->current_stock=$exists_current_stock + $new_stock_in;
                                $warehouse_current_stock_update->save();
                            }else{
                                $new_stock_out = $previous_purchase_qty - $data['qty'];

                                $stock = new Stock();
                                $stock->ref_id=$request->product_purchase_id;
                                $stock->user_id=$user_id;
                                $stock->product_unit_id= $data['product_unit_id'];
                                $stock->product_brand_id= $data['product_brand_id'] ? $data['product_brand_id'] : NULL;
                                $stock->product_id= $product_id;
                                $stock->stock_type='whole_purchase_decrease';
                                $stock->warehouse_id= $productPurchase->warehouse_id;
                                $stock->store_id=NULL;
                                $stock->stock_where='warehouse';
                                $stock->stock_in_out='stock_in';
                                $stock->previous_stock=$current_stock;
                                $stock->stock_in=$new_stock_out;
                                $stock->stock_out=0;
                                $stock->current_stock=$current_stock - $new_stock_out;
                                $stock->stock_date=$date;
                                $stock->stock_date_time=$date_time;
                                $stock->save();

                                // warehouse current stock
                                $warehouse_current_stock_update->current_stock=$exists_current_stock - $new_stock_out;
                                $warehouse_current_stock_update->save();
                            }
                        }
                    }

                    // new product
                    if($request->product_type == 'new'){
                        // product purchase detail
                        $purchase_purchase_detail = new ProductPurchaseDetail();
                        $purchase_purchase_detail->product_purchase_id = $productPurchase->ref_id;
                        $purchase_purchase_detail->product_unit_id = $data['product_unit_id'];
                        $purchase_purchase_detail->product_brand_id = $data['product_brand_id'] ? $data['product_brand_id'] : NULL;
                        $purchase_purchase_detail->product_id = $product_id;
                        $purchase_purchase_detail->qty = $data['qty'];
                        $purchase_purchase_detail->price = $data['price'];
                        $purchase_purchase_detail->mrp_price = $data['mrp_price'];
                        $purchase_purchase_detail->sub_total = $data['qty']*$data['price'];
                        $purchase_purchase_detail->barcode = $barcode;
                        $purchase_purchase_detail->save();

                        $check_previous_stock = Stock::where('product_id',$product_id)->latest()->pluck('current_stock')->first();
                        if(!empty($check_previous_stock)){
                            $previous_stock = $check_previous_stock;
                        }else{
                            $previous_stock = 0;
                        }

                        // product stock
                        $stock = new Stock();
                        $stock->ref_id = $productPurchase->ref_id;
                        $stock->user_id = $user_id;
                        $stock->warehouse_id = $request->warehouse_id;
                        $stock->product_id = $product_id;
                        $stock->product_unit_id = $data['product_unit_id'];
                        $stock->product_brand_id = $data['product_brand_id'] ? $data['product_brand_id'] : NULL;
                        $stock->stock_type = 'whole_purchase';
                        $stock->stock_where = 'warehouse';
                        $stock->stock_in_out = 'stock_in';
                        $stock->previous_stock = $previous_stock;
                        $stock->stock_in = $data['qty'];
                        $stock->stock_out = 0;
                        $stock->current_stock = $previous_stock + $data['qty'];
                        $stock->stock_date = $date;
                        $stock->stock_date_time = $date_time;
                        $stock->save();

                        // warehouse current stock
                        $check_exists_warehouse_current_stock = WarehouseCurrentStock::where('warehouse_id',$request->warehouse_id)
                            ->where('product_id',$product_id)
                            ->first();
                        if($check_exists_warehouse_current_stock){
                            $warehouse_current_stock_update = WarehouseCurrentStock::find($check_exists_warehouse_current_stock->id);
                            $warehouse_current_stock_update->current_stock=$check_exists_warehouse_current_stock->current_stock + $data['qty'];
                            $warehouse_current_stock_update->save();
                        }else{
                            $warehouse_current_stock = new WarehouseCurrentStock();
                            $warehouse_current_stock->warehouse_id=$request->warehouse_id;
                            $warehouse_current_stock->product_id=$product_id;
                            $warehouse_current_stock->current_stock=$data['qty'];
                            $warehouse_current_stock->save();
                        }
                    }

                }

                // transaction
                $transaction = Transaction::where('ref_id',$request->product_purchase_id)->first();
                $transaction->user_id = $user_id;
                $transaction->warehouse_id = $request->warehouse_id;
                $transaction->party_id = $request->party_id;
                $transaction->payment_type = $request->payment_type;
                $transaction->amount = $request->paid_amount;
                $transaction->update();

                // payment paid
                $payment_paid = PaymentPaid::where('product_purchase_id',$request->product_purchase_id)->first();
                $payment_paid->user_id = $user_id;
                $payment_paid->party_id = $request->party_id;
                $payment_paid->paid_amount = $request->paid_amount;
                $payment_paid->due_amount = $request->due_amount;
                $payment_paid->current_paid_amount = $request->paid_amount;
                $payment_paid->update();

                $response = APIHelpers::createAPIResponse(false,200,'Product Purchase Updated Successfully.',null);
                return response()->json($response,200);
            }else{
                $response = APIHelpers::createAPIResponse(true,400,'Product Purchase Updated Failed.',null);
                return response()->json($response,400);
            }
        } catch (\Exception $e) {
            //return $e->getMessage();
            $response = APIHelpers::createAPIResponse(false,500,'Internal Server Error.',null);
            return response()->json($response,500);
        }
    }

    public function productWholePurchaseDelete(Request $request){
        try {
            $check_exists_product_purchase = DB::table("product_purchases")->where('id',$request->product_purchase_id)->pluck('id')->first();
            if($check_exists_product_purchase == null){
                $response = APIHelpers::createAPIResponse(true,404,'No Warehouse Found.',null);
                return response()->json($response,404);
            }

            $productPurchase = ProductPurchase::find($request->product_purchase_id);
            if($productPurchase){
                $user_id = Auth::user()->id;
                $date = date('Y-m-d');
                $date_time = date('Y-m-d H:i:s');

                $product_purchase_details = DB::table('product_purchase_details')->where('product_purchase_id',$request->product_purchase_id)->get();

                if(count($product_purchase_details) > 0){
                    foreach ($product_purchase_details as $product_purchase_detail){
                        // current stock
                        $stock_row = Stock::where('stock_where','warehouse')->where('warehouse_id',$productPurchase->warehouse_id)->where('product_id',$product_purchase_detail->product_id)->latest('id')->first();
                        $current_stock = $stock_row->current_stock;

                        $stock = new Stock();
                        $stock->ref_id=$productPurchase->id;
                        $stock->user_id=$user_id;
                        $stock->product_unit_id= $product_purchase_detail->product_unit_id;
                        $stock->product_brand_id= $product_purchase_detail->product_brand_id;
                        $stock->product_id= $product_purchase_detail->product_id;
                        $stock->stock_type='whole_purchase_delete';
                        $stock->warehouse_id= $productPurchase->warehouse_id;
                        $stock->store_id=NULL;
                        $stock->stock_where='warehouse';
                        $stock->stock_in_out='stock_out';
                        $stock->previous_stock=$current_stock;
                        $stock->stock_in=0;
                        $stock->stock_out=$product_purchase_detail->qty;
                        $stock->current_stock=$current_stock + $product_purchase_detail->qty;
                        $stock->stock_date=$date;
                        $stock->stock_date_time=$date_time;
                        $stock->save();


                        $warehouse_current_stock = WarehouseCurrentStock::where('warehouse_id',$productPurchase->warehouse_id)->where('product_id',$product_purchase_detail->product_id)->first();
                        $exists_current_stock = $warehouse_current_stock->current_stock;
                        $warehouse_current_stock->current_stock=$exists_current_stock - $product_purchase_detail->qty;
                        $warehouse_current_stock->update();
                    }
                }
            }
            $delete_purchase = $productPurchase->delete();

            DB::table('product_purchase_details')->where('product_purchase_id',$request->product_purchase_id)->delete();
            //DB::table('stocks')->where('ref_id',$request->product_purchase_id)->delete();
            DB::table('transactions')->where('ref_id',$request->product_purchase_id)->delete();
            DB::table('payment_paids')->where('product_purchase_id',$request->product_purchase_id)->delete();

            if($delete_purchase)
            {
                $response = APIHelpers::createAPIResponse(false,200,'Product Purchase Successfully Soft Deleted.',null);
                return response()->json($response,200);
            }else{
                $response = APIHelpers::createAPIResponse(true,400,'Product Purchase Updated Failed.',null);
                return response()->json($response,400);
            }
        } catch (\Exception $e) {
            //return $e->getMessage();
            $response = APIHelpers::createAPIResponse(false,500,'Internal Server Error.',null);
            return response()->json($response,500);
        }
    }

    public function productWholePurchaseSingleProductRemove(Request $request){
        $check_exists_product_purchase = DB::table("product_purchases")->where('id',$request->product_purchase_id)->pluck('id')->first();
        if($check_exists_product_purchase == null){
            return response()->json(['success'=>false,'response'=>'No Product Purchase Found!'], $this->failStatus);
        }

        $productPurchase = ProductPurchase::find($request->product_purchase_id);
        if($productPurchase) {

            //$discount_amount = $productPurchase->discount_amount;
            $paid_amount = $productPurchase->paid_amount;
            $due_amount = $productPurchase->due_amount;
            //$total_vat_amount = $productPurchase->total_vat_amount;
            $total_amount = $productPurchase->total_amount;

            $product_purchase_detail = DB::table('product_purchase_details')->where('id', $request->product_purchase_detail_id)->first();
            $product_unit_id = $product_purchase_detail->product_unit_id;
            $product_brand_id = $product_purchase_detail->product_brand_id;
            $product_id = $product_purchase_detail->product_id;
            $qty = $product_purchase_detail->qty;
            //return response()->json(['success'=>true,'response' =>$product_sale_detail], $this->successStatus);
            if ($product_purchase_detail) {

                //$remove_discount = $product_sale_detail->discount;
                //$remove_vat_amount = $product_purchase_detail->vat_amount;
                $remove_sub_total = $product_purchase_detail->sub_total;


                //$productSale->discount_amount = $discount_amount - $remove_discount;
                //$productPurchase->discount_amount = $total_vat_amount - $remove_vat_amount;
                $productPurchase->due_amount = $due_amount - $remove_sub_total;
                $productPurchase->total_amount = $total_amount - $remove_sub_total;
                $productPurchase->save();

                // delete single product
                //$product_sale_detail->delete();
                DB::table('product_purchase_details')->delete($product_purchase_detail->id);
            }



            $user_id = Auth::user()->id;
            $date = date('Y-m-d');
            $date_time = date('Y-m-d H:i:s');
            // current stock
            $stock_row = Stock::where('stock_where','warehouse')->where('warehouse_id',$productPurchase->warehouse_id)->where('product_id',$product_id)->latest('id')->first();
            $current_stock = $stock_row->current_stock;

            $stock = new Stock();
            $stock->ref_id=$productPurchase->id;
            $stock->user_id=$user_id;
            $stock->product_unit_id= $product_unit_id;
            $stock->product_brand_id= $product_brand_id;
            $stock->product_id= $product_id;
            $stock->stock_type='whole_purchase_delete';
            $stock->warehouse_id= $productPurchase->warehouse_id;
            $stock->store_id=NULL;
            $stock->stock_where='warehouse';
            $stock->stock_in_out='stock_out';
            $stock->previous_stock=$current_stock;
            $stock->stock_in=0;
            $stock->stock_out=$qty;
            $stock->current_stock=$current_stock - $qty;
            $stock->stock_date=$date;
            $stock->stock_date_time=$date_time;
            $stock->save();

            $warehouse_current_stock = WarehouseCurrentStock::where('warehouse_id',$productPurchase->warehouse_id)->where('product_id',$product_id)->first();
            $exists_current_stock = $warehouse_current_stock->current_stock;
            $warehouse_current_stock->current_stock=$exists_current_stock - $qty;
            $warehouse_current_stock->update();

            return response()->json(['success'=>true,'response' =>'Single Product Purchase Remove Successfully Removed!'], $this->successStatus);
        } else{
            return response()->json(['success'=>false,'response'=>'Single Product Purchase Remove Not Deleted!'], $this->failStatus);
        }
    }


    // not working now
    public function productPurchaseRemove(Request $request){

        $this->validate($request, [
            'product_purchase_id'=> 'required',
            'party_id'=> 'required',
            'warehouse_id'=> 'required',
            'paid_amount'=> 'required',
            'due_amount'=> 'required',
            'total_amount'=> 'required',
            'product_id'=> 'required',
            'sub_total'=> 'required',
            'payment_type'=> 'required',
            'product_purchase_detail_id'=> 'required',
        ]);



        $user_id = Auth::user()->id;
        $date = date('Y-m-d');
        $date_time = date('Y-m-d h:i:s');

        // product purchase
        $productPurchase = ProductPurchase::find($request->product_purchase_id);
        $productPurchase->user_id = $user_id;
        $productPurchase->total_amount = $request->total_amount - $request->sub_total;
        $affectedRows = $productPurchase->update();

        if($affectedRows)
        {

            $product_id = $request->product_id;
            $product_info = Product::where('id',$product_id)->first();

            $product_purchase_detail_id = $request->product_purchase_detail_id;


            // product stock
            $stock_row = Stock::where('warehouse_id',$request->warehouse_id)->where('product_id',$product_id)->latest()->first();

            $current_stock = $stock_row->current_stock;

            // warehouse current stock
            $warehouse_current_stock_update = WarehouseCurrentStock::where('warehouse_id',$request->warehouse_id)
                ->where('product_id',$product_id)
                ->first();
            $exists_current_stock = $warehouse_current_stock_update->current_stock;


            $stock = new Stock();
            $stock->ref_id=$request->product_purchase_id;
            $stock->user_id=$user_id;
            $stock->product_unit_id= $product_info->product_unit_id;
            $stock->product_brand_id= $product_info->product_brand_id ? $product_info->product_brand_id : NULL;
            $stock->product_id= $product_id;
            $stock->stock_type='whole_purchase_delete';
            $stock->warehouse_id= $request->warehouse_id;
            $stock->store_id=NULL;
            $stock->stock_where='warehouse';
            $stock->stock_in_out='stock_out';
            $stock->previous_stock=$current_stock;
            $stock->stock_in=0;
            $stock->stock_out=$request->qty;
            $stock->current_stock=$current_stock - $request->qty;
            $stock->stock_date=$date;
            $stock->stock_date_time=$date_time;
            $stock->save();

            // warehouse current stock
            $warehouse_current_stock_update->current_stock=$exists_current_stock - $request->qty;
            $warehouse_current_stock_update->save();


            //work on
            // transaction
            $transaction = new Transaction();
            $transaction->ref_id = $request->product_purchase_id;
            $transaction->invoice_no = $productPurchase->invoice_no;
            $transaction->user_id = $user_id;
            $transaction->warehouse_id = $request->warehouse_id;
            $transaction->party_id = $request->party_id;
            $transaction->transaction_type = 'whole_purchase_delete';
            $transaction->payment_type = 'Cash';
            $transaction->amount = $request->sub_total;
            $transaction->transaction_date = $date;
            $transaction->transaction_date_time = $date_time;
            $transaction->save();

            // payment paid
//            $payment_paid = new PaymentPaid();
//            $payment_paid->invoice_no = $productPurchase->invoice_no;
//            $payment_paid->product_purchase_id = $request->product_purchase_id;
//            $payment_paid->user_id = $user_id;
//            $payment_paid->party_id = $request->party_id;
//            $payment_paid->paid_type = 'Purchase';
//            $payment_paid->paid_amount = $request->sub_total;
//            $payment_paid->due_amount = $request->due_amount;
//            $payment_paid->current_paid_amount = $request->sub_total;
//            $payment_paid->paid_date = $date;
//            $payment_paid->paid_date_time = $date_time;
//            $payment_paid->save();

            $payment_paid = PaymentPaid::where('invoice_no',$productPurchase->invoice_no)->where('paid_type','Purchase')->first();
            $previous_paid_amount = $payment_paid->paid_amount;

            $payment_paid->paid_amount = $previous_paid_amount - $request->sub_total;
            $payment_paid->due_amount = $previous_paid_amount - $request->due_amount;
            $payment_paid->current_paid_amount = $previous_paid_amount - $request->sub_total;
            $payment_paid->save();


            // product purchase detail delete
            ProductPurchaseDetail::where('id',$product_purchase_detail_id)->delete();


            return response()->json(['success'=>true,'response' => 'Removed Successfully.'], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Removed Successfully!'], $this->failStatus);
        }
    }

    public function productPOSPurchaseList(){
        $product_pos_purchases = DB::table('product_purchases')
            ->leftJoin('users','product_purchases.user_id','users.id')
            ->leftJoin('parties','product_purchases.party_id','parties.id')
            ->leftJoin('warehouses','product_purchases.warehouse_id','warehouses.id')
            ->where('product_purchases.purchase_type','pos_purchase')
            ->select('product_purchases.id','product_purchases.invoice_no','product_purchases.discount_type','product_purchases.discount_amount','product_purchases.total_amount','product_purchases.paid_amount','product_purchases.due_amount','product_purchases.purchase_date_time','users.name as user_name','parties.id as supplier_id','parties.name as supplier_name','warehouses.id as warehouse_id','warehouses.name as warehouse_name')
            ->orderBy('product_purchases.id','desc')
            ->get();

        if($product_pos_purchases)
        {
            $product_pos_purchases_arr = [];
            foreach ($product_pos_purchases as $data){
                $payment_type = DB::table('transactions')->where('ref_id',$data->id)->where('transaction_type','pos_purchase')->pluck('payment_type')->first();

                $nested_data['id']=$data->id;
                $nested_data['invoice_no']=ucfirst($data->invoice_no);
                $nested_data['discount_type']=$data->discount_type;
                $nested_data['discount_amount']=$data->discount_amount;
                $nested_data['total_amount']=$data->total_amount;
                $nested_data['paid_amount']=$data->paid_amount;
                $nested_data['due_amount']=$data->due_amount;
                $nested_data['purchase_date_time']=$data->purchase_date_time;
                $nested_data['user_name']=$data->user_name;
                $nested_data['supplier_id']=$data->supplier_id;
                $nested_data['supplier_name']=$data->supplier_name;
                $nested_data['warehouse_id']=$data->warehouse_id;
                $nested_data['warehouse_name']=$data->warehouse_name;
                $nested_data['payment_type']=$payment_type;

                array_push($product_pos_purchases_arr,$nested_data);
            }

            $success['product_pos_purchases'] =  $product_pos_purchases_arr;
            return response()->json(['success'=>true,'response' => $success], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Product POS Purchase List Found!'], $this->failStatus);
        }
    }

    public function productPOSPurchaseDetails(Request $request){
        $product_pos_purchase_details = DB::table('product_purchases')
            ->join('product_purchase_details','product_purchases.id','product_purchase_details.product_purchase_id')
            ->leftJoin('products','product_purchase_details.product_id','products.id')
            ->leftJoin('product_units','product_purchase_details.product_unit_id','product_units.id')
            ->leftJoin('product_brands','product_purchase_details.product_brand_id','product_brands.id')
            ->where('product_purchases.id',$request->product_purchase_id)
            ->select('products.id as product_id','products.name as product_name','product_units.id as product_unit_id','product_units.name as product_unit_name','product_brands.id as product_brand_id','product_brands.name as product_brand_name','product_purchase_details.qty','product_purchase_details.id as product_purchase_detail_id','product_purchase_details.price','product_purchase_details.mrp_price')
            ->orderBy('product_purchases.id','desc')
            ->get();

        if($product_pos_purchase_details)
        {
            $success['product_pos_purchase_details'] =  $product_pos_purchase_details;
            return response()->json(['success'=>true,'response' => $success], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Product POS Purchase Detail Found!'], $this->failStatus);
        }
    }

    public function productPOSPurchaseCreate(Request $request){

        $this->validate($request, [
            'party_id'=> 'required',
            'warehouse_id'=> 'required',
            'paid_amount'=> 'required',
            'due_amount'=> 'required',
            'total_amount'=> 'required',
            'payment_type'=> 'required',
        ]);

        $get_invoice_no = ProductPurchase::latest()->pluck('invoice_no')->first();
        if(!empty($get_invoice_no)){
            $get_invoice = str_replace("purchase-","",$get_invoice_no);
            $invoice_no = $get_invoice+1;
        }else{
            $invoice_no = 1000;
        }
        $final_invoice = 'purchase-'.$invoice_no;

        $date = date('Y-m-d');
        $date_time = date('Y-m-d h:i:s');

        $user_id = Auth::user()->id;

        // product purchase
        $productPurchase = new ProductPurchase();
        $productPurchase ->invoice_no = $final_invoice;
        $productPurchase ->user_id = $user_id;
        $productPurchase ->party_id = $request->party_id;
        $productPurchase ->warehouse_id = $request->warehouse_id;
        $productPurchase ->purchase_type = 'pos_purchase';
        $productPurchase ->discount_type = $request->discount_type ? $request->discount_type : NULL;
        $productPurchase ->discount_percent = $request->discount_percent ? $request->discount_percent : 0;
        $productPurchase ->discount_amount = $request->discount_amount ? $request->discount_amount : 0;
        $productPurchase ->after_discount_amount = $request->after_discount_amount ? $request->after_discount_amount : 0;
        $productPurchase ->paid_amount = $request->paid_amount;
        $productPurchase ->due_amount = $request->due_amount;
        $productPurchase ->total_amount = $request->total_amount;
        $productPurchase ->purchase_date = $date;
        $productPurchase ->purchase_date_time = $date_time;
        $productPurchase->save();
        $insert_id = $productPurchase->id;

        if($insert_id)
        {
            foreach ($request->products as $data) {

                $product_id =  $data['product_id'];

                $barcode = Product::where('id',$product_id)->pluck('barcode')->first();

                // product purchase detail
                $purchase_purchase_detail = new ProductPurchaseDetail();
                $purchase_purchase_detail->product_purchase_id = $insert_id;
                $purchase_purchase_detail->product_unit_id = $data['product_unit_id'];
                $purchase_purchase_detail->product_brand_id = $data['product_brand_id'] ? $data['product_brand_id'] : NULL;
                $purchase_purchase_detail->product_id = $product_id;
                $purchase_purchase_detail->qty = $data['qty'];
                $purchase_purchase_detail->price = $data['price'];
                $purchase_purchase_detail->mrp_price = $data['mrp_price'];
                $purchase_purchase_detail->sub_total = $data['qty']*$data['price'];
                $purchase_purchase_detail->barcode = $barcode;
                $purchase_purchase_detail->save();

                $check_previous_stock = Stock::where('product_id',$product_id)->latest()->pluck('current_stock')->first();
                if(!empty($check_previous_stock)){
                    $previous_stock = $check_previous_stock;
                }else{
                    $previous_stock = 0;
                }

                // product stock
                $stock = new Stock();
                $stock->ref_id = $insert_id;
                $stock->user_id = $user_id;
                $stock->warehouse_id = $request->warehouse_id;
                $stock->product_id = $product_id;
                $stock->product_unit_id = $data['product_unit_id'];
                $stock->product_brand_id = $data['product_brand_id'] ? $data['product_brand_id'] : NULL;
                $stock->stock_type = 'pos_purchase';
                $stock->stock_where = 'warehouse';
                $stock->stock_in_out = 'stock_in';
                $stock->previous_stock = $previous_stock;
                $stock->stock_in = $data['qty'];
                $stock->stock_out = 0;
                $stock->current_stock = $previous_stock + $data['qty'];
                $stock->stock_date = $date;
                $stock->stock_date_time = $date_time;
                $stock->save();

                // warehouse current stock
                $check_exists_warehouse_current_stock = WarehouseCurrentStock::where('warehouse_id',$request->warehouse_id)
                    ->where('product_id',$product_id)
                    ->first();
                if($check_exists_warehouse_current_stock){
                    $warehouse_current_stock_update = WarehouseCurrentStock::find($check_exists_warehouse_current_stock->id);
                    $warehouse_current_stock_update->current_stock=$check_exists_warehouse_current_stock->current_stock + $data['qty'];
                    $warehouse_current_stock_update->save();
                }else{
                    $warehouse_current_stock = new WarehouseCurrentStock();
                    $warehouse_current_stock->warehouse_id=$request->warehouse_id;
                    $warehouse_current_stock->product_id=$product_id;
                    $warehouse_current_stock->current_stock=$data['qty'];
                    $warehouse_current_stock->save();
                }
            }

            // transaction
            $transaction = new Transaction();
            $transaction->ref_id = $insert_id;
            $transaction->invoice_no = $final_invoice;
            $transaction->user_id = $user_id;
            $transaction->warehouse_id = $request->warehouse_id;
            $transaction->party_id = $request->party_id;
            $transaction->transaction_type = 'pos_purchase';
            $transaction->payment_type = $request->payment_type;
            $transaction->amount = $request->paid_amount;
            $transaction->transaction_date = $date;
            $transaction->transaction_date_time = $date_time;
            $transaction->save();
            $transaction_id = $transaction->id;

            // payment paid
            $payment_paid = new PaymentPaid();
            $payment_paid->invoice_no = $final_invoice;
            $payment_paid->product_purchase_id = $insert_id;
            $payment_paid->user_id = $user_id;
            $payment_paid->party_id = $request->party_id;
            $payment_paid->paid_type = 'Purchase';
            $payment_paid->paid_amount = $request->paid_amount;
            $payment_paid->due_amount = $request->due_amount;
            $payment_paid->current_paid_amount = $request->paid_amount;
            $payment_paid->paid_date = $date;
            $payment_paid->paid_date_time = $date_time;
            $payment_paid->save();


            if($request->payment_type == 'SSL Commerz'){
                return response()->json(['success'=>true,'transaction_id' => $transaction_id,'payment_type' => $request->payment_type], $this->successStatus);
            }else{
                return response()->json(['success'=>true,'response' => 'Inserted Successfully.'], $this->successStatus);
            }
        }else{
            return response()->json(['success'=>false,'response'=>'No Role Created!'], $this->failStatus);
        }
    }

    public function productPOSPurchaseEdit(Request $request){
        $this->validate($request, [
            'product_purchase_id'=> 'required',
            'party_id'=> 'required',
            'warehouse_id'=> 'required',
            'paid_amount'=> 'required',
            'due_amount'=> 'required',
            'total_amount'=> 'required',
            'payment_type'=> 'required',
        ]);

        $user_id = Auth::user()->id;
        $date = date('Y-m-d');
        $date_time = date('Y-m-d H:i:s');

        // product purchase
        $productPurchase = ProductPurchase::find($request->product_purchase_id);
        $productPurchase ->user_id = $user_id;
        $productPurchase ->party_id = $request->party_id;
        $productPurchase ->warehouse_id = $request->warehouse_id;
        $productPurchase ->discount_type = $request->discount_type ? $request->discount_type : NULL;
        $productPurchase ->discount_percent = $request->discount_percent ? $request->discount_percent : 0;
        $productPurchase ->discount_amount = $request->discount_amount ? $request->discount_amount : 0;
        $productPurchase ->after_discount_amount = $request->after_discount_amount ? $request->after_discount_amount : 0;
        $productPurchase ->paid_amount = $request->paid_amount;
        $productPurchase ->due_amount = $request->due_amount;
        $productPurchase ->total_amount = $request->total_amount;
        $productPurchase->update();
        $affectedRows = $productPurchase->id;
        if($affectedRows)
        {
            foreach ($request->products as $data) {
                $product_id = $data['product_id'];
                $barcode = Product::where('id',$product_id)->pluck('barcode')->first();

                $product_purchase_detail_id = $data['product_purchase_detail_id'];
                // product purchase detail
                $purchase_purchase_detail = ProductPurchaseDetail::find($product_purchase_detail_id);
                $previous_purchase_qty = $purchase_purchase_detail->qty;
                $purchase_purchase_detail->product_unit_id = $data['product_unit_id'];
                $purchase_purchase_detail->product_brand_id = $data['product_brand_id'] ? $data['product_brand_id'] : NULL;
                $purchase_purchase_detail->product_id = $product_id;
                $purchase_purchase_detail->qty = $data['qty'];
                $purchase_purchase_detail->price = $data['price'];
                $purchase_purchase_detail->mrp_price = $data['mrp_price'];
                $purchase_purchase_detail->sub_total = $data['qty']*$data['price'];
                $purchase_purchase_detail->barcode = $barcode;
                $purchase_purchase_detail->update();


                // product stock
                $stock_row = Stock::where('warehouse_id',$request->warehouse_id)->where('product_id',$product_id)->latest()->first();
                $current_stock = $stock_row->current_stock;

                // warehouse current stock
                $warehouse_current_stock_update = WarehouseCurrentStock::where('warehouse_id',$request->warehouse_id)
                    ->where('product_id',$product_id)
                    ->first();
                $exists_current_stock = $warehouse_current_stock_update->current_stock;

                if($stock_row->stock_in != $data['qty']){

                    if($data['qty'] > $stock_row->stock_in){
                        $new_stock_in = $data['qty'] - $previous_purchase_qty;

                        $stock = new Stock();
                        $stock->ref_id=$request->product_purchase_id;
                        $stock->user_id=$user_id;
                        $stock->product_unit_id= $data['product_unit_id'];
                        $stock->product_brand_id= $data['product_brand_id'] ? $data['product_brand_id'] : NULL;
                        $stock->product_id= $product_id;
                        $stock->stock_type='pos_purchase_increase';
                        $stock->warehouse_id= $productPurchase->warehouse_id;
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
                        $warehouse_current_stock_update->current_stock=$exists_current_stock + $new_stock_in;
                        $warehouse_current_stock_update->save();
                    }else{
                        $new_stock_out = $previous_purchase_qty - $data['qty'];

                        $stock = new Stock();
                        $stock->ref_id=$request->product_purchase_id;
                        $stock->user_id=$user_id;
                        $stock->product_unit_id= $data['product_unit_id'];
                        $stock->product_brand_id= $data['product_brand_id'] ? $data['product_brand_id'] : NULL;
                        $stock->product_id= $product_id;
                        $stock->stock_type='pos_purchase_decrease';
                        $stock->warehouse_id= $productPurchase->warehouse_id;
                        $stock->store_id=NULL;
                        $stock->stock_where='warehouse';
                        $stock->stock_in_out='stock_in';
                        $stock->previous_stock=$current_stock;
                        $stock->stock_in=0;
                        $stock->stock_out=$new_stock_out;
                        $stock->current_stock=$current_stock - $new_stock_out;
                        $stock->stock_date=$date;
                        $stock->stock_date_time=$date_time;
                        $stock->save();

                        // warehouse current stock
                        $warehouse_current_stock_update->current_stock=$exists_current_stock - $new_stock_out;
                        $warehouse_current_stock_update->save();
                    }
                }
            }

            // transaction
            $transaction = Transaction::where('ref_id',$request->product_purchase_id)->first();
            $transaction->user_id = $user_id;
            $transaction->warehouse_id = $request->warehouse_id;
            $transaction->party_id = $request->party_id;
            $transaction->payment_type = $request->payment_type;
            $transaction->amount = $request->paid_amount;
            $transaction->update();

            // payment paid
            $payment_paid = PaymentPaid::where('product_purchase_id',$request->product_purchase_id)->first();
            $payment_paid->user_id = $user_id;
            $payment_paid->party_id = $request->party_id;
            $payment_paid->paid_amount = $request->paid_amount;
            $payment_paid->due_amount = $request->due_amount;
            $payment_paid->current_paid_amount = $request->paid_amount;
            $payment_paid->update();


            return response()->json(['success'=>true,'response' => 'Updated Successfully.'], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Updated Successfully!'], $this->failStatus);
        }
    }

    public function productPOSPurchaseDelete(Request $request){
        $check_exists_product_purchase = DB::table("product_purchases")->where('id',$request->product_purchase_id)->pluck('id')->first();
        if($check_exists_product_purchase == null){
            return response()->json(['success'=>false,'response'=>'No Product Purchase Found!'], $this->failStatus);
        }

        $productPurchase = ProductPurchase::find($request->product_purchase_id);
        if($productPurchase){
            $user_id = Auth::user()->id;
            $date = date('Y-m-d');
            $date_time = date('Y-m-d H:i:s');

            $product_purchase_details = DB::table('product_purchase_details')->where('product_purchase_id',$request->product_purchase_id)->get();

            if(count($product_purchase_details) > 0){
                foreach ($product_purchase_details as $product_purchase_detail){
                    // current stock
                    $stock_row = Stock::where('stock_where','warehouse')->where('warehouse_id',$productPurchase->warehouse_id)->where('product_id',$product_purchase_detail->product_id)->latest('id')->first();
                    $current_stock = $stock_row->current_stock;

                    $stock = new Stock();
                    $stock->ref_id=$productPurchase->id;
                    $stock->user_id=$user_id;
                    $stock->product_unit_id= $product_purchase_detail->product_unit_id;
                    $stock->product_brand_id= $product_purchase_detail->product_brand_id;
                    $stock->product_id= $product_purchase_detail->product_id;
                    $stock->stock_type='pos_purchase_delete';
                    $stock->warehouse_id= $productPurchase->warehouse_id;
                    $stock->store_id=NULL;
                    $stock->stock_where='warehouse';
                    $stock->stock_in_out='stock_out';
                    $stock->previous_stock=$current_stock;
                    $stock->stock_in=0;
                    $stock->stock_out=$product_purchase_detail->qty;
                    $stock->current_stock=$current_stock + $product_purchase_detail->qty;
                    $stock->stock_date=$date;
                    $stock->stock_date_time=$date_time;
                    $stock->save();


                    $warehouse_current_stock = WarehouseCurrentStock::where('warehouse_id',$productPurchase->warehouse_id)->where('product_id',$product_purchase_detail->product_id)->first();
                    $exists_current_stock = $warehouse_current_stock->current_stock;
                    $warehouse_current_stock->current_stock=$exists_current_stock - $product_purchase_detail->qty;
                    $warehouse_current_stock->update();
                }
            }
        }
        $delete_purchase = $productPurchase->delete();

        DB::table('product_purchase_details')->where('product_purchase_id',$request->product_purchase_id)->delete();
        //DB::table('stocks')->where('ref_id',$request->product_purchase_id)->delete();
        DB::table('transactions')->where('ref_id',$request->product_purchase_id)->delete();
        DB::table('payment_paids')->where('product_purchase_id',$request->product_purchase_id)->delete();

        if($delete_purchase)
        {
            return response()->json(['success'=>true,'response' => 'Purchase Successfully Deleted!'], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Purchase Deleted!'], $this->failStatus);
        }
    }

    // product purchase invoice list
    public function productPurchaseInvoiceList(){
        $product_purchase_invoices = DB::table('product_purchases')
            ->select('id','invoice_no')
            ->get();

        if($product_purchase_invoices)
        {
            $success['product_purchase_invoices'] =  $product_purchase_invoices;
            return response()->json(['success'=>true,'response' => $success], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Product Purchase List Found!'], $this->failStatus);
        }
    }

    // product purchase invoice list
    public function productPurchaseInvoiceListPagination(){
        $product_purchase_invoices = DB::table('product_purchases')
            ->select('id','invoice_no')
            ->paginate(12);

        if($product_purchase_invoices)
        {
            $success['product_purchase_invoices'] =  $product_purchase_invoices;
            return response()->json(['success'=>true,'response' => $success], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Product Purchase List Found!'], $this->failStatus);
        }
    }

    // product purchase invoice list
    public function productPurchaseInvoiceListPaginationWithSearch(Request $request){
        try {
            if($request->search){
                $product_purchase_invoices = DB::table('product_purchases')
                    ->where('invoice_no','like','%'.$request->search.'%')
                    ->select('id','invoice_no')
                    ->paginate(12);
            }else{
                $product_purchase_invoices = DB::table('product_purchases')
                    ->select('id','invoice_no')
                    ->paginate(12);
            }

            if($product_purchase_invoices === null){
                $response = APIHelpers::createAPIResponse(true,404,'No Product Purchase List Found.',null);
                return response()->json($response,404);
            }else{
                $response = APIHelpers::createAPIResponse(false,200,'',$product_purchase_invoices);
                return response()->json($response,200);
            }
        } catch (\Exception $e) {
            //return $e->getMessage();
            $response = APIHelpers::createAPIResponse(false,500,'Internal Server Error.',null);
            return response()->json($response,500);
        }
    }

    public function productPurchaseReturnCreate(Request $request){
        try {
            // required and unique
            $validator = Validator::make($request->all(), [
                'party_id'=> 'required',
                'warehouse_id'=> 'required',
                'paid_amount'=> 'required',
                'due_amount'=> 'required',
                'total_amount'=> 'required',
                'payment_type'=> 'required',
                'product_purchase_invoice_no'=> 'required',
            ]);

            if ($validator->fails()) {
                $response = APIHelpers::createAPIResponse(true,400,$validator->errors(),null);
                return response()->json($response,400);
            }

            $product_purchase_id = ProductPurchase::where('invoice_no',$request->product_purchase_invoice_no)->pluck('id')->first();

            $get_invoice_no = ProductPurchaseReturn::latest('id','desc')->pluck('invoice_no')->first();
            if(!empty($get_invoice_no)){
                $get_invoice = str_replace("purchase-return","",$get_invoice_no);
                $invoice_no = $get_invoice+1;
            }else{
                $invoice_no = 5000;
            }
            $final_invoice = 'purchase-return'.$invoice_no;

            $date = date('Y-m-d');
            $date_time = date('Y-m-d h:i:s');

            $user_id = Auth::user()->id;

            // product purchase
            $productPurchaseReturn = new ProductPurchaseReturn();
            $productPurchaseReturn ->invoice_no = $final_invoice;
            $productPurchaseReturn ->product_purchase_invoice_no = $request->product_purchase_invoice_no;
            $productPurchaseReturn ->user_id = $user_id;
            $productPurchaseReturn ->party_id = $request->party_id;
            $productPurchaseReturn ->warehouse_id = $request->warehouse_id;
            $productPurchaseReturn ->product_purchase_return_type = 'purchase_return';
            $productPurchaseReturn ->discount_type = $request->discount_type ? $request->discount_type : NULL;
            $productPurchaseReturn ->discount_amount = $request->discount_amount ? $request->discount_amount : 0;
            $productPurchaseReturn ->paid_amount = $request->total_amount;
            $productPurchaseReturn ->due_amount = $request->due_amount;
            $productPurchaseReturn ->total_amount = $request->total_amount;
            $productPurchaseReturn ->product_purchase_return_date = $date;
            $productPurchaseReturn ->product_purchase_return_date_time = $date_time;
            $productPurchaseReturn->save();
            $insert_id = $productPurchaseReturn->id;

            if($insert_id)
            {
                foreach ($request->products as $data) {

                    if($data['qty'] > 0){
                        $product_id =  $data['product_id'];

                        $barcode = Product::where('id',$product_id)->pluck('barcode')->first();

                        // product purchase detail
                        $purchase_purchase_return_detail = new ProductPurchaseReturnDetail();
                        $purchase_purchase_return_detail->pro_pur_return_id = $insert_id;
                        $purchase_purchase_return_detail->pro_pur_detail_id = $data['product_purchase_detail_id'];
                        $purchase_purchase_return_detail->product_unit_id = $data['product_unit_id'];
                        $purchase_purchase_return_detail->product_brand_id = $data['product_brand_id'] ? $data['product_brand_id'] : NULL;
                        $purchase_purchase_return_detail->product_id = $product_id;
                        $purchase_purchase_return_detail->barcode = $barcode;
                        $purchase_purchase_return_detail->qty = $data['qty'];
                        $purchase_purchase_return_detail->price = $data['price'];
                        $purchase_purchase_return_detail->sub_total = $data['qty']*$data['price'];
                        $purchase_purchase_return_detail->save();

                        $check_previous_stock = Stock::where('product_id',$product_id)->where('stock_where','warehouse')->latest('id','desc')->pluck('current_stock')->first();
                        if(!empty($check_previous_stock)){
                            $previous_stock = $check_previous_stock;
                        }else{
                            $previous_stock = 0;
                        }

                        // product stock
                        $stock = new Stock();
                        $stock->ref_id = $insert_id;
                        $stock->user_id = $user_id;
                        $stock->warehouse_id = $request->warehouse_id;
                        $stock->product_id = $product_id;
                        $stock->product_unit_id = $data['product_unit_id'];
                        $stock->product_brand_id = $data['product_brand_id'] ? $data['product_brand_id'] : NULL;
                        $stock->stock_type = 'purchase_return';
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
                        $warehouse_current_stock_update = WarehouseCurrentStock::where('warehouse_id',$request->warehouse_id)
                            ->where('product_id',$product_id)
                            ->first();
                        $exists_current_stock = $warehouse_current_stock_update->current_stock;
                        $update_warehouse_current_stock = $exists_current_stock - $data['qty'];
                        $warehouse_current_stock_update->current_stock=$update_warehouse_current_stock;
                        $warehouse_current_stock_update->save();
                    }
                }

                // transaction
                $transaction = new Transaction();
                $transaction->ref_id = $insert_id;
                $transaction->invoice_no = $final_invoice;
                $transaction->user_id = $user_id;
                $transaction->warehouse_id = $request->warehouse_id;
                $transaction->party_id = $request->party_id;
                $transaction->transaction_type = 'purchase_return';
                $transaction->payment_type = $request->payment_type;
                $transaction->amount = $request->total_amount;
                $transaction->transaction_date = $date;
                $transaction->transaction_date_time = $date_time;
                $transaction->save();

                // payment paid
                $payment_paid = new PaymentPaid();
                $payment_paid->invoice_no = $final_invoice;
                $payment_paid->product_purchase_id = $product_purchase_id;
                $payment_paid->product_purchase_return_id = $insert_id;
                $payment_paid->user_id = $user_id;
                $payment_paid->party_id = $request->party_id;
                $payment_paid->paid_type = 'Return';
                $payment_paid->paid_amount = $request->total_amount;
                $payment_paid->due_amount = $request->due_amount;
                $payment_paid->current_paid_amount = $request->total_amount;
                $payment_paid->paid_date = $date;
                $payment_paid->paid_date_time = $date_time;
                $payment_paid->save();


                $response = APIHelpers::createAPIResponse(false,201,'Purchase Return Added Successfully.',null);
                return response()->json($response,201);
            }else{
                $response = APIHelpers::createAPIResponse(true,400,'Purchase Return Added Failed.',null);
                return response()->json($response,400);
            }
        } catch (\Exception $e) {
            //return $e->getMessage();
            $response = APIHelpers::createAPIResponse(false,500,'Internal Server Error.',null);
            return response()->json($response,500);
        }
    }

    public function productPurchaseReturnSingleProductRemove(Request $request){
        $check_exists_product_purchase_return = DB::table("product_purchase_returns")->where('id',$request->product_purchase_return_id)->pluck('id')->first();
        if($check_exists_product_purchase_return == null){
            return response()->json(['success'=>false,'response'=>'No Product Purchase Return Found!'], $this->failStatus);
        }

        $productPurchaseReturn = ProductPurchaseReturn::find($request->product_purchase_return_id);
        if($productPurchaseReturn) {

            //$discount_amount = $productPurchaseReturn->discount_amount;
            $paid_amount = $productPurchaseReturn->paid_amount;
            $due_amount = $productPurchaseReturn->due_amount;
            //$total_vat_amount = $productPurchaseReturn->total_vat_amount;
            $total_amount = $productPurchaseReturn->total_amount;

            $product_purchase_return_detail = DB::table('product_purchase_return_details')->where('id', $request->product_purchase_return_detail_id)->first();
            $product_unit_id = $product_purchase_return_detail->product_unit_id;
            $product_brand_id = $product_purchase_return_detail->product_brand_id;
            $product_id = $product_purchase_return_detail->product_id;
            $qty = $product_purchase_return_detail->qty;

            if ($product_purchase_return_detail) {

                //$remove_discount = $product_sale_detail->discount;
                //$remove_vat_amount = $product_purchase_detail->vat_amount;
                $remove_sub_total = $product_purchase_return_detail->sub_total;


                //$productSale->discount_amount = $discount_amount - $remove_discount;
                //$productPurchase->discount_amount = $total_vat_amount - $remove_vat_amount;
                $productPurchaseReturn->paid_amount = $paid_amount - $remove_sub_total;
                $productPurchaseReturn->due_amount = $due_amount - $remove_sub_total;
                $productPurchaseReturn->total_amount = $total_amount - $remove_sub_total;
                $productPurchaseReturn->save();

                $transaction = Transaction::where('invoice_no',$productPurchaseReturn->invoice_no)->first();
                if($transaction){
                    $transaction->amount=$total_amount - $remove_sub_total;
                    $transaction->save();
                }

                $payment_paid = PaymentPaid::where('invoice_no',$productPurchaseReturn->invoice_no)->first();
                if($payment_paid){
                    $payment_paid->paid_amount=$total_amount - $remove_sub_total;
                    $payment_paid->due_amount=$total_amount - $remove_sub_total;
                    $payment_paid->current_paid_amount=$total_amount - $remove_sub_total;
                    $payment_paid->save();
                }

                // delete single product
                //$product_sale_detail->delete();
                DB::table('product_purchase_return_details')->delete($product_purchase_return_detail->id);
            }



            $user_id = Auth::user()->id;
            $date = date('Y-m-d');
            $date_time = date('Y-m-d H:i:s');
            // current stock
            $stock_row = Stock::where('stock_where','warehouse')->where('warehouse_id',$productPurchaseReturn->warehouse_id)->where('product_id',$product_id)->latest('id')->first();
            $current_stock = $stock_row->current_stock;

            $stock = new Stock();
            $stock->ref_id=$productPurchaseReturn->id;
            $stock->user_id=$user_id;
            $stock->product_unit_id= $product_unit_id;
            $stock->product_brand_id= $product_brand_id;
            $stock->product_id= $product_id;
            $stock->stock_type='product_purchase_return_delete';
            $stock->warehouse_id= $productPurchaseReturn->warehouse_id;
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

            $warehouse_current_stock = WarehouseCurrentStock::where('warehouse_id',$productPurchaseReturn->warehouse_id)->where('product_id',$product_id)->first();
            $exists_current_stock = $warehouse_current_stock->current_stock;
            $warehouse_current_stock->current_stock=$exists_current_stock + $qty;
            $warehouse_current_stock->update();

            return response()->json(['success'=>true,'response' =>'Single Product Purchase Return Remove Successfully Removed!'], $this->successStatus);
        } else{
            return response()->json(['success'=>false,'response'=>'Single Product Purchase Return Remove Not Deleted!'], $this->failStatus);
        }
    }

    public function productPurchaseDetails(Request $request){
        //dd($request->all());
        $this->validate($request, [
            'product_purchase_invoice_no'=> 'required',
        ]);

        $product_purchases = DB::table('product_purchases')
            ->leftJoin('users','product_purchases.user_id','users.id')
            ->leftJoin('parties','product_purchases.party_id','parties.id')
            ->leftJoin('warehouses','product_purchases.warehouse_id','warehouses.id')
            ->where('product_purchases.invoice_no',$request->product_purchase_invoice_no)
            ->select('product_purchases.id','product_purchases.invoice_no','product_purchases.discount_type','product_purchases.discount_amount','product_purchases.total_amount','product_purchases.paid_amount','product_purchases.due_amount','product_purchases.purchase_date_time','users.name as user_name','parties.id as supplier_id','parties.name as supplier_name','warehouses.id as warehouse_id','warehouses.name as warehouse_name')
            ->first();

        if($product_purchases){

            $product_pos_purchase_details = DB::table('product_purchases')
                ->join('product_purchase_details','product_purchases.id','product_purchase_details.product_purchase_id')
                ->leftJoin('products','product_purchase_details.product_id','products.id')
                ->leftJoin('product_units','product_purchase_details.product_unit_id','product_units.id')
                ->leftJoin('product_brands','product_purchase_details.product_brand_id','product_brands.id')
                ->where('product_purchases.invoice_no',$request->product_purchase_invoice_no)
                ->select(
                    'products.id as product_id',
                    'products.name as product_name',
                    'product_units.id as product_unit_id',
                    'product_units.name as product_unit_name',
                    'product_brands.id as product_brand_id',
                    'product_brands.name as product_brand_name',
                    'product_purchase_details.qty',
                    //'product_purchase_details.qty as current_qty',
                    'product_purchase_details.id as product_purchase_detail_id',
                    'product_purchase_details.price',
                    'product_purchase_details.mrp_price'
                )
                ->get();

            $product_purchase_arr = [];
            if(count($product_pos_purchase_details) > 0){
                foreach ($product_pos_purchase_details as $product_pos_purchase_detail){
                    $already_return_qty = 0;
                    $return_qty = DB::table('product_purchase_return_details')
                        ->select('pro_pur_detail_id','product_id',DB::raw('SUM(qty) as qty'))
                        ->where('pro_pur_detail_id',$product_pos_purchase_detail->product_purchase_detail_id)
                        ->where('product_id',$product_pos_purchase_detail->product_id)
                        ->groupBy('pro_pur_detail_id','product_id')
                        ->first();

                    if(!empty($return_qty)){
                        $already_return_qty = (int) $return_qty->qty;
                    }

                    $nested_data['product_id'] = $product_pos_purchase_detail->product_id;
                    $nested_data['product_name'] = $product_pos_purchase_detail->product_name;
                    $nested_data['product_unit_id'] = $product_pos_purchase_detail->product_unit_id;
                    $nested_data['product_unit_name'] = $product_pos_purchase_detail->product_unit_name;
                    $nested_data['product_brand_id'] = $product_pos_purchase_detail->product_brand_id;
                    $nested_data['product_brand_name'] = $product_pos_purchase_detail->product_brand_name;
                    //$nested_data['qty'] = $product_pos_purchase_detail->qty;
                    $nested_data['purchase_qty'] = $product_pos_purchase_detail->qty;
                    //$nested_data['current_qty'] = $product_pos_purchase_detail->current_qty;
                    $nested_data['already_return_qty'] = $already_return_qty;
                    $nested_data['exists_return_qty'] = $product_pos_purchase_detail->qty - $already_return_qty;
                    $nested_data['product_purchase_detail_id'] = $product_pos_purchase_detail->product_purchase_detail_id;
                    $nested_data['price'] = $product_pos_purchase_detail->price;
                    $nested_data['mrp_price'] = $product_pos_purchase_detail->mrp_price;

                    array_push($product_purchase_arr,$nested_data);

                }
            }

            $success['product_purchases'] = $product_purchases;
            $success['product_pos_purchase_details'] = $product_purchase_arr;
            return response()->json(['success'=>true,'response' => $success], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Product Purchase Data Found!'], $this->failStatus);
        }
    }

    public function productPurchaseReturnList(){
        try {
            $product_purchase_return_list = DB::table('product_purchase_returns')
                ->leftJoin('users','product_purchase_returns.user_id','users.id')
                ->leftJoin('parties','product_purchase_returns.party_id','parties.id')
                ->leftJoin('warehouses','product_purchase_returns.warehouse_id','warehouses.id')
                //->where('product_purchases.purchase_type','whole_purchase')
                ->select(
                    'product_purchase_returns.id',
                    'product_purchase_returns.invoice_no',
                    'product_purchase_returns.product_purchase_invoice_no',
                    'product_purchase_returns.discount_type',
                    'product_purchase_returns.discount_amount',
                    'product_purchase_returns.total_amount',
                    'product_purchase_returns.paid_amount',
                    'product_purchase_returns.due_amount',
                    'product_purchase_returns.product_purchase_return_date_time',
                    'users.name as user_name',
                    'parties.id as supplier_id',
                    'parties.name as supplier_name',
                    'warehouses.id as warehouse_id',
                    'warehouses.name as warehouse_name'
                )
                ->orderBy('product_purchase_returns.id','desc')
                ->get();

            if(count($product_purchase_return_list) > 0)
            {
                $product_purchase_return_arr = [];
                foreach ($product_purchase_return_list as $data){
                    $payment_type = DB::table('transactions')->where('ref_id',$data->id)->where('transaction_type','whole_purchase')->pluck('payment_type')->first();

                    $nested_data['id']=$data->id;
                    $nested_data['invoice_no']=ucfirst($data->invoice_no);
                    $nested_data['product_purchase_invoice_no']=$data->product_purchase_invoice_no;
                    $nested_data['discount_type']=$data->discount_type;
                    $nested_data['discount_amount']=$data->discount_amount;
                    $nested_data['total_amount']=$data->total_amount;
                    $nested_data['paid_amount']=$data->paid_amount;
                    $nested_data['due_amount']=$data->due_amount;
                    $nested_data['product_purchase_return_date_time']=$data->product_purchase_return_date_time;
                    $nested_data['user_name']=$data->user_name;
                    $nested_data['supplier_id']=$data->supplier_id;
                    $nested_data['supplier_name']=$data->supplier_name;
                    $nested_data['warehouse_id']=$data->warehouse_id;
                    $nested_data['warehouse_name']=$data->warehouse_name;
                    $nested_data['payment_type']=$payment_type;

                    array_push($product_purchase_return_arr,$nested_data);
                }

                if(count($product_purchase_return_arr) === 0){
                    $response = APIHelpers::createAPIResponse(true,404,'No Product Purchase Return Found.',null);
                    return response()->json($response,404);
                }else{
                    $response = APIHelpers::createAPIResponse(false,200,'',$product_purchase_return_arr);
                    return response()->json($response,200);
                }
            }
        } catch (\Exception $e) {
            //return $e->getMessage();
            $response = APIHelpers::createAPIResponse(false,500,'Internal Server Error.',null);
            return response()->json($response,500);
        }
    }

    public function productPurchaseReturnDetails(Request $request){
        try {
            $product_purchase_return_details = DB::table('product_purchase_returns')
                ->join('product_purchase_return_details','product_purchase_returns.id','product_purchase_return_details.pro_pur_return_id')
                ->leftJoin('products','product_purchase_return_details.product_id','products.id')
                ->leftJoin('product_units','product_purchase_return_details.product_unit_id','product_units.id')
                ->leftJoin('product_brands','product_purchase_return_details.product_brand_id','product_brands.id')
                ->where('product_purchase_return_details.pro_pur_return_id',$request->product_purchase_return_id)
                ->select(
                    'products.id as product_id',
                    'products.name as product_name',
                    'product_units.id as product_unit_id',
                    'product_units.name as product_unit_name',
                    'product_brands.id as product_brand_id',
                    'product_brands.name as product_brand_name',
                    'product_purchase_return_details.qty',
                    'product_purchase_return_details.id as product_purchase_return_detail_id',
                    'product_purchase_return_details.price'
                )
                ->get();

            if($product_purchase_return_details === null){
                $response = APIHelpers::createAPIResponse(true,404,'No Purchase Return Detail Found.',null);
                return response()->json($response,404);
            }else{
                $response = APIHelpers::createAPIResponse(false,200,'',$product_purchase_return_details);
                return response()->json($response,200);
            }
        } catch (\Exception $e) {
            //return $e->getMessage();
            $response = APIHelpers::createAPIResponse(false,500,'Internal Server Error.',null);
            return response()->json($response,500);
        }
    }

    public function productPurchaseReturnDetailsPdf(Request $request){
        try {
            $product_purchase_return_details = DB::table('product_purchase_returns')
                ->join('product_purchase_return_details','product_purchase_returns.id','product_purchase_return_details.pro_pur_return_id')
                ->join('parties','product_purchase_returns.party_id','parties.id')
                ->leftJoin('products','product_purchase_return_details.product_id','products.id')
                ->leftJoin('product_units','product_purchase_return_details.product_unit_id','product_units.id')
                ->leftJoin('product_brands','product_purchase_return_details.product_brand_id','product_brands.id')
                ->where('product_purchase_return_details.pro_pur_return_id',$request->product_purchase_return_id)
                ->select(
                    'products.id as product_id',
                    'products.name as product_name',
                    'product_units.id as product_unit_id',
                    'product_units.name as product_unit_name',
                    'product_brands.id as product_brand_id',
                    'product_brands.name as product_brand_name',
                    'product_purchase_return_details.qty',
                    'product_purchase_return_details.id as product_purchase_return_detail_id',
                    'product_purchase_return_details.price',
                    'product_purchase_returns.product_purchase_return_date',
                    'parties.name',
                    'parties.phone',
                    'parties.email',
                    'parties.address'
                )
                ->get();

            if($product_purchase_return_details === null){
                $response = APIHelpers::createAPIResponse(true,404,'No Purchase Return Detail Found.',null);
                return response()->json($response,404);
            }else{
                $response = APIHelpers::createAPIResponse(false,200,'',$product_purchase_return_details);
                return response()->json($response,200);
            }
        } catch (\Exception $e) {
            //return $e->getMessage();
            $response = APIHelpers::createAPIResponse(false,500,'Internal Server Error.',null);
            return response()->json($response,500);
        }
    }

    public function productWholePurchaseCreateWithLowProduct(Request $request){

        $this->validate($request, [
            //'user_id'=> 'required',
            'party_id'=> 'required',
            'warehouse_id'=> 'required',
            'paid_amount'=> 'required',
            'due_amount'=> 'required',
            'total_amount'=> 'required',
            'payment_type'=> 'required',
        ]);


        $get_invoice_no = ProductPurchase::latest()->pluck('invoice_no')->first();
        if(!empty($get_invoice_no)){
            $get_invoice = str_replace("purchase-","",$get_invoice_no);
            $invoice_no = $get_invoice+1;
        }else{
            $invoice_no = 1000;
        }
        $final_invoice = 'purchase-'.$invoice_no;

        $date = date('Y-m-d');
        $date_time = date('Y-m-d h:i:s');

        $user_id = Auth::user()->id;

        // product purchase
        $productPurchase = new ProductPurchase();
        $productPurchase ->invoice_no = $final_invoice;
        $productPurchase ->user_id = $user_id;
        $productPurchase ->party_id = $request->party_id;
        $productPurchase ->warehouse_id = $request->warehouse_id;
        $productPurchase ->purchase_type = 'whole_purchase';
        $productPurchase ->sub_total = $request->sub_total;
        $productPurchase ->total_amount = $request->total_amount;
        $productPurchase ->paid_amount = $request->paid_amount;
        $productPurchase ->due_amount = $request->due_amount;
        $productPurchase ->purchase_date = $date;
        $productPurchase ->purchase_date_time = $date_time;
        $productPurchase->save();
        $insert_id = $productPurchase->id;

        if($insert_id)
        {
            $product_id =  $request->product_id;

            $barcode = Product::where('id',$product_id)->pluck('barcode')->first();

            // product purchase detail
            $purchase_purchase_detail = new ProductPurchaseDetail();
            $purchase_purchase_detail->product_purchase_id = $insert_id;
            $purchase_purchase_detail->product_unit_id = $request->product_unit_id;
            $purchase_purchase_detail->product_brand_id = $request->product_brand_id ? $request->product_brand_id : NULL;
            $purchase_purchase_detail->product_id = $product_id;
            $purchase_purchase_detail->qty = $request->qty;
            $purchase_purchase_detail->price = $request->price;
            $purchase_purchase_detail->mrp_price = $request->mrp_price;
            $purchase_purchase_detail->sub_total = $request->qty*$request->price;
            $purchase_purchase_detail->barcode = $barcode;
            $purchase_purchase_detail->save();

            $check_previous_stock = Stock::where('product_id',$product_id)->latest()->pluck('current_stock')->first();
            if(!empty($check_previous_stock)){
                $previous_stock = $check_previous_stock;
            }else{
                $previous_stock = 0;
            }

            // product stock
            $stock = new Stock();
            $stock->ref_id = $insert_id;
            $stock->user_id = $user_id;
            $stock->warehouse_id = $request->warehouse_id;
            $stock->product_id = $product_id;
            $stock->product_unit_id = $request->product_unit_id;
            $purchase_purchase_detail->product_brand_id = $request->product_brand_id ? $request->product_brand_id : NULL;
            $stock->stock_type = 'whole_purchase';
            $stock->stock_where = 'warehouse';
            $stock->stock_in_out = 'stock_in';
            $stock->previous_stock = $previous_stock;
            $stock->stock_in = $request->qty;
            $stock->stock_out = 0;
            $stock->current_stock = $previous_stock +$request->qty;
            $stock->stock_date = $date;
            $stock->stock_date_time = $date_time;
            $stock->save();

            // warehouse current stock
            $check_exists_warehouse_current_stock = WarehouseCurrentStock::where('warehouse_id',$request->warehouse_id)
                ->where('product_id',$product_id)
                ->first();
            if($check_exists_warehouse_current_stock){
                $warehouse_current_stock_update = WarehouseCurrentStock::find($check_exists_warehouse_current_stock->id);
                $warehouse_current_stock_update->current_stock=$check_exists_warehouse_current_stock->current_stock + $data['qty'];
                $warehouse_current_stock_update->save();
            }else{
                $warehouse_current_stock = new WarehouseCurrentStock();
                $warehouse_current_stock->warehouse_id=$request->warehouse_id;
                $warehouse_current_stock->product_id=$product_id;
                $warehouse_current_stock->current_stock=$request->qty;
                $warehouse_current_stock->save();
            }


            // transaction
            $transaction = new Transaction();
            $transaction->ref_id = $insert_id;
            $transaction->invoice_no = $final_invoice;
            $transaction->user_id = $user_id;
            $transaction->warehouse_id = $request->warehouse_id;
            $transaction->party_id = $request->party_id;
            $transaction->transaction_type = 'whole_purchase';
            $transaction->payment_type = $request->payment_type;
            $transaction->amount = $request->paid_amount;
            $transaction->transaction_date = $date;
            $transaction->transaction_date_time = $date_time;
            $transaction->save();

            // payment paid
            $payment_paid = new PaymentPaid();
            $payment_paid->invoice_no = $final_invoice;
            $payment_paid->product_purchase_id = $insert_id;
            $payment_paid->user_id = $user_id;
            $payment_paid->party_id = $request->party_id;
            $payment_paid->paid_type = 'Purchase';
            $payment_paid->paid_amount = $request->paid_amount;
            $payment_paid->due_amount = $request->due_amount;
            $payment_paid->current_paid_amount = $request->paid_amount;
            $payment_paid->paid_date = $date;
            $payment_paid->paid_date_time = $date_time;
            $payment_paid->save();


            return response()->json(['success'=>true,'response' => 'Inserted Successfully.'], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Inserted Created!'], $this->failStatus);
        }
    }
}
