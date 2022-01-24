<?php

namespace App\Http\Controllers\API;

use App\Helpers\APIHelpers;
use App\Http\Controllers\Controller;
use App\Product;
use App\ProductPurchase;
use App\Stock;
use App\StockTransfer;
use App\StockTransferDetail;
use App\StockTransferRequest;
use App\StockTransferRequestDetail;
use App\StoreStockReturn;
use App\StoreStockReturnDetail;
use App\User;
use App\warehouseCurrentStock;
use App\WarehouseStoreCurrentStock;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class StockController extends Controller
{
    public $successStatus = 200;
    public $authStatus = 401;
    public $failStatus = 402;
    public $ExistsStatus = 403;
    public $validationStatus = 404;

    public function warehouseStockList(){
//        $warehouse_stock_list = DB::table('stocks')
//            ->leftJoin('users','stocks.user_id','users.id')
//            ->leftJoin('warehouses','stocks.warehouse_id','warehouses.id')
//            ->leftJoin('product_units','stocks.product_unit_id','product_units.id')
//            ->leftJoin('product_brands','stocks.product_brand_id','product_brands.id')
//            ->leftJoin('products','stocks.product_id','products.id')
//            ->where('stocks.stock_where','warehouse')
//            ->select('stocks.id as stock_id','users.name as stock_by_user','warehouses.name as warehouse_name','product_units.name as product_unit_name','product_brands.name as product_brand_name','products.name as product_name','stocks.stock_type','stocks.stock_where','stocks.stock_in_out','stocks.previous_stock','stocks.stock_in','stocks.stock_out','stocks.current_stock','stocks.stock_date','stocks.stock_date_time')
//            ->latest('stocks.id','desc')
//            ->get();

        $warehouse_stock_list = Stock::leftJoin('users','stocks.user_id', '=', 'users.id')
            ->leftJoin('warehouses','stocks.warehouse_id','warehouses.id')
            ->leftJoin('product_units','stocks.product_unit_id','product_units.id')
            ->leftJoin('product_brands','stocks.product_brand_id','product_brands.id')
            ->leftJoin('products','stocks.product_id','products.id')
            ->where('stocks.stock_where','warehouse')
            ->select('stocks.id as stock_id','users.name as stock_by_user','warehouses.name as warehouse_name','product_units.name as product_unit_name','product_brands.name as product_brand_name','products.name as product_name','stocks.stock_type','stocks.stock_where','stocks.stock_in_out','stocks.previous_stock','stocks.stock_in','stocks.stock_out','stocks.current_stock','stocks.stock_date','stocks.stock_date_time')
            ->latest('stocks.id','desc')
            ->paginate(10);

        if($warehouse_stock_list)
        {
            $success['warehouse_stock_list'] =  $warehouse_stock_list;
            return response()->json(['success'=>true,'response' => $success], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Warehouse Stock List Found!'], $this->failStatus);
        }
    }

    public function warehouseStockLowList(){

        $warehouse_stock_low_list = DB::table('stocks')
            ->leftJoin('users','stocks.user_id','users.id')
            ->leftJoin('warehouses','stocks.warehouse_id','warehouses.id')
            ->leftJoin('product_units','stocks.product_unit_id','product_units.id')
            ->leftJoin('product_brands','stocks.product_brand_id','product_brands.id')
            ->leftJoin('products','stocks.product_id','products.id')
            ->where('stocks.stock_where','warehouse')
            //->where('stocks.current_stock','<',2)
            ->whereIn('stocks.id', function($query) {
                $query->from('stocks')->where('current_stock','<', 2)->groupBy('product_id')->selectRaw('MAX(id)');
            })
            ->select('stocks.id as stock_id','users.name as stock_by_user','warehouses.name as warehouse_name','product_units.name as product_unit_name','product_brands.name as product_brand_name','products.id as product_id','products.name as product_name','stocks.stock_type','stocks.stock_where','stocks.previous_stock','stocks.stock_in','stocks.stock_out','stocks.current_stock','stocks.stock_date','stocks.stock_date_time')
            ->latest('stocks.id','desc')
            ->get();

        if($warehouse_stock_low_list)
        {
            $success['warehouse_stock_low_list'] =  $warehouse_stock_low_list;
            return response()->json(['success'=>true,'response' => $success], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Warehouse Stock Low List Found!'], $this->failStatus);
        }
    }

    public function warehouseCurrentStockList(Request $request){

        $warehouse_stock_product_list = DB::table('warehouse_current_stocks')
            ->join('warehouses','warehouse_current_stocks.warehouse_id','warehouses.id')
            ->leftJoin('products','warehouse_current_stocks.product_id','products.id')
            ->leftJoin('product_units','products.product_unit_id','product_units.id')
            ->leftJoin('product_brands','products.product_brand_id','product_brands.id')
            ->where('warehouse_current_stocks.warehouse_id',$request->warehouse_id)
            ->select('warehouse_current_stocks.*','warehouses.name as warehouse_name','products.name as product_name','products.purchase_price','products.selling_price','products.whole_sale_price','products.item_code','products.barcode','products.image','products.vat_status','products.vat_percentage','products.vat_amount','product_units.id as product_unit_id','product_units.name as product_unit_name','product_brands.id as product_brand_id','product_brands.name as product_brand_name')
            ->get();

        $warehouse_stock_product = [];
        foreach($warehouse_stock_product_list as $stock_row){

            $nested_data['stock_id'] = $stock_row->id;
            $nested_data['warehouse_id'] = $stock_row->warehouse_id;
            $nested_data['warehouse_name'] = $stock_row->warehouse_name;
            $nested_data['product_id'] = $stock_row->product_id;
            $nested_data['product_name'] = $stock_row->product_name;
            $nested_data['purchase_price'] = $stock_row->purchase_price;
            $nested_data['selling_price'] = $stock_row->selling_price;
            $nested_data['whole_sale_price'] = $stock_row->whole_sale_price;
            $nested_data['item_code'] = $stock_row->item_code;
            $nested_data['barcode'] = $stock_row->barcode;
            $nested_data['image'] = $stock_row->image;
            $nested_data['vat_status'] = $stock_row->vat_status;
            $nested_data['vat_percentage'] = $stock_row->vat_percentage;
            $nested_data['vat_amount'] = $stock_row->vat_amount;
            $nested_data['product_unit_id'] = $stock_row->product_unit_id;
            $nested_data['product_unit_name'] = $stock_row->product_unit_name;
            $nested_data['product_brand_id'] = $stock_row->product_brand_id;
            $nested_data['product_brand_name'] = $stock_row->product_brand_name;
            $nested_data['current_stock'] = $stock_row->current_stock;

            array_push($warehouse_stock_product,$nested_data);

        }

        if($warehouse_stock_product)
        {
            $success['warehouse_current_stock_list'] =  $warehouse_stock_product;
            return response()->json(['success'=>true,'response' => $success], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Warehouse Current Stock List Found!'], $this->failStatus);
        }
    }

    public function warehouseCurrentStockListWithoutZero(Request $request){
        $warehouse_stock_product_list = DB::table('warehouse_current_stocks')
            ->join('warehouses','warehouse_current_stocks.warehouse_id','warehouses.id')
            ->leftJoin('products','warehouse_current_stocks.product_id','products.id')
            ->leftJoin('product_units','products.product_unit_id','product_units.id')
            ->leftJoin('product_brands','products.product_brand_id','product_brands.id')
            ->where('warehouse_current_stocks.warehouse_id',$request->warehouse_id)
            ->where('warehouse_current_stocks.current_stock','!=',0)
            ->select('warehouse_current_stocks.*','warehouses.name as warehouse_name','products.name as product_name','products.purchase_price','products.selling_price','products.item_code','products.barcode','products.image','products.vat_status','products.vat_percentage','products.vat_amount','product_units.id as product_unit_id','product_units.name as product_unit_name','product_brands.id as product_brand_id','product_brands.name as product_brand_name')
            ->get();

        $warehouse_stock_product = [];
        foreach($warehouse_stock_product_list as $stock_row){

            $nested_data['stock_id'] = $stock_row->id;
            $nested_data['warehouse_id'] = $stock_row->warehouse_id;
            $nested_data['warehouse_name'] = $stock_row->warehouse_name;
            $nested_data['product_id'] = $stock_row->product_id;
            $nested_data['product_name'] = $stock_row->product_name;
            $nested_data['purchase_price'] = $stock_row->purchase_price;
            $nested_data['selling_price'] = $stock_row->selling_price;
            $nested_data['item_code'] = $stock_row->item_code;
            $nested_data['barcode'] = $stock_row->barcode;
            $nested_data['image'] = $stock_row->image;
            $nested_data['product_unit_id'] = $stock_row->product_unit_id;
            $nested_data['product_unit_name'] = $stock_row->product_unit_name;
            $nested_data['product_brand_id'] = $stock_row->product_brand_id;
            $nested_data['product_brand_name'] = $stock_row->product_brand_name;
            $nested_data['current_stock'] = $stock_row->current_stock;

            array_push($warehouse_stock_product,$nested_data);

        }

        if($warehouse_stock_product)
        {
            $success['warehouse_current_stock_list'] =  $warehouse_stock_product;
            return response()->json(['success'=>true,'response' => $success], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Warehouse Current Stock List Found!'], $this->failStatus);
        }
    }

    public function storeToWarehouseStockRequestCreate(Request $request){
        try {
            // required and unique
            $validator = Validator::make($request->all(), [
                'request_from_store_id'=> 'required',
                'request_to_warehouse_id'=> 'required',
            ]);

            if ($validator->fails()) {
                $response = APIHelpers::createAPIResponse(true,400,$validator->errors(),null);
                return response()->json($response,400);
            }

            $date = date('Y-m-d');
            $date_time = date('Y-m-d h:i:s');

            $user_id = Auth::user()->id;
            $request_to_warehouse_id = $request->request_to_warehouse_id;
            $request_from_store_id = $request->request_from_store_id;


            $get_invoice_no = StockTransferRequest::latest()->pluck('invoice_no')->first();
            if(!empty($get_invoice_no)){
                $get_invoice = str_replace("STRN-","",$get_invoice_no);
                $invoice_no = $get_invoice+1;
            }else{
                $invoice_no = 2000;
            }

            $final_invoice = 'STRN-'.$invoice_no;
            $stock_transfer_request = new StockTransferRequest();
            $stock_transfer_request->invoice_no=$final_invoice;
            $stock_transfer_request->request_to_warehouse_id = $request_to_warehouse_id;
            $stock_transfer_request->request_from_store_id = $request_from_store_id;
            $stock_transfer_request->request_by_user_id=$user_id;
            $stock_transfer_request->request_date=$date;
            $stock_transfer_request->request_remarks=$request->request_remarks;
            $stock_transfer_request->request_status='Pending';
            //$stock_transfer_request->received_by_user_id=NULL;
            //$stock_transfer_request->received_status='Pending';
            $stock_transfer_request->save();
            $stock_transfer_request_insert_id = $stock_transfer_request->id;


            foreach ($request->products as $data) {

                $product_id = $data['product_id'];
                $product_info = Product::where('id',$product_id)->first();

                $stock_transfer_request_detail = new StockTransferRequestDetail();
                $stock_transfer_request_detail->stock_transfer_request_id = $stock_transfer_request_insert_id;
                $stock_transfer_request_detail->product_unit_id = $data['product_unit_id'];
                $stock_transfer_request_detail->product_brand_id = $data['product_brand_id'] ? $data['product_brand_id'] : NULL;
                $stock_transfer_request_detail->product_id = $product_id;
                $stock_transfer_request_detail->barcode = $product_info->barcode;
                $stock_transfer_request_detail->request_qty = $data['qty'];
                $stock_transfer_request_detail->send_qty = 0;
                $stock_transfer_request_detail->received_qty = 0;
                $stock_transfer_request_detail->price = $product_info->purchase_price;
                //$stock_transfer_request_detail->vat_amount = $data['qty']*$product_info->whole_sale_price;
                $stock_transfer_request_detail->vat_amount = 0;
                $stock_transfer_request_detail->sub_total = $data['qty']*$product_info->purchase_price;
                $stock_transfer_request_detail->received_date = $date;
                $stock_transfer_request_detail->save();
            }

            $response = APIHelpers::createAPIResponse(false,201,'Stock Transfer Request Added Successfully.',null);
            return response()->json($response,201);
        } catch (\Exception $e) {
            //return $e->getMessage();
            $response = APIHelpers::createAPIResponse(false,500,'Internal Server Error.',null);
            return response()->json($response,500);
        }
    }

    public function storeToWarehouseStockRequestEdit(Request $request){
//        try {
            // required and unique
            $validator = Validator::make($request->all(), [
                'stock_transfer_request_id'=> 'required',
                'request_from_store_id'=> 'required',
                'request_to_warehouse_id'=> 'required',
            ]);

            if ($validator->fails()) {
                $response = APIHelpers::createAPIResponse(true,400,$validator->errors(),null);
                return response()->json($response,400);
            }

            $user_id = Auth::user()->id;
            $request_to_warehouse_id = $request->request_to_warehouse_id;
            $request_from_store_id = $request->request_from_store_id;

            $stock_transfer_request = StockTransferRequest::find($request->stock_transfer_request_id);
            $stock_transfer_request->request_to_warehouse_id = $request_to_warehouse_id;
            $stock_transfer_request->request_from_store_id = $request_from_store_id;
            $stock_transfer_request->request_by_user_id=$user_id;
            $stock_transfer_request->request_remarks=$request->request_remarks;
            $affectedRow = $stock_transfer_request->save();


            foreach ($request->products as $data) {

                $product_id = $data['product_id'];
                $product_info = Product::where('id',$product_id)->first();

                $stock_transfer_request_detail_id = $data['stock_transfer_request_detail_id'];
                $stock_transfer_request_detail = StockTransferRequestDetail::find($stock_transfer_request_detail_id);
                $stock_transfer_request_detail->product_unit_id = $data['product_unit_id'];
                $stock_transfer_request_detail->product_brand_id = $data['product_brand_id'] ? $data['product_brand_id'] : NULL;
                $stock_transfer_request_detail->product_id = $product_id;
                $stock_transfer_request_detail->barcode = $product_info->barcode;
                $stock_transfer_request_detail->request_qty = $data['qty'];
                $stock_transfer_request_detail->price = $product_info->purchase_price;
                $stock_transfer_request_detail->vat_amount = 0;
                $stock_transfer_request_detail->sub_total = ($data['qty']*$product_info->purchase_price);
                $stock_transfer_request_detail->save();
            }

            if($affectedRow){
                $response = APIHelpers::createAPIResponse(false,200,'Stock Transfer Request Updated Successfully.',null);
                return response()->json($response,200);
            }else{
                $response = APIHelpers::createAPIResponse(true,400,'Stock Transfer Request Updated Failed.',null);
                return response()->json($response,400);
            }
//        } catch (\Exception $e) {
//            //return $e->getMessage();
//            $response = APIHelpers::createAPIResponse(false,500,'Internal Server Error.',null);
//            return response()->json($response,500);
//        }
    }

    public function storeToWarehouseStockRequestList(){
        try {
        $stock_transfer_request_lists = DB::table('stock_transfer_requests')
            ->leftJoin('users','stock_transfer_requests.request_by_user_id','users.id')
            ->leftJoin('warehouses','stock_transfer_requests.request_to_warehouse_id','warehouses.id')
            ->leftJoin('stores','stock_transfer_requests.request_from_store_id','stores.id')
            //->where('stock_transfers.sale_type','whole_sale')
            ->select('stock_transfer_requests.id','stock_transfer_requests.invoice_no','stock_transfer_requests.request_date','stock_transfer_requests.request_remarks','stock_transfer_requests.view_by_user_id','stock_transfer_requests.view_status','users.name as user_name','warehouses.id as warehouse_id','warehouses.name as warehouse_name','stores.id as store_id','stores.name as store_name','stores.phone as store_phone','stores.email as store_email','stores.address as store_address')
            ->orderBy('stock_transfer_requests.id','desc')
            ->get();

        if($stock_transfer_request_lists)
        {
            $data = [];
            foreach($stock_transfer_request_lists as $stock_transfer_request_list){
                $nested_data['id']=$stock_transfer_request_list->id;
                $nested_data['invoice_no']=$stock_transfer_request_list->invoice_no;
                $nested_data['request_date']=$stock_transfer_request_list->request_date;
                $nested_data['request_remarks']=$stock_transfer_request_list->request_remarks;
                $nested_data['user_name']=$stock_transfer_request_list->user_name;
                $nested_data['warehouse_id']=$stock_transfer_request_list->warehouse_id;
                $nested_data['warehouse_name']=$stock_transfer_request_list->warehouse_name;
                $nested_data['store_id']=$stock_transfer_request_list->store_id;
                $nested_data['store_name']=$stock_transfer_request_list->store_name;
                $nested_data['store_phone']=$stock_transfer_request_list->store_phone;
                $nested_data['store_email']=$stock_transfer_request_list->store_email;
                $nested_data['store_address']=$stock_transfer_request_list->store_address;
                $nested_data['view_status']=$stock_transfer_request_list->view_status;
                $nested_data['view_by_user_id']=$stock_transfer_request_list->view_by_user_id;

                $view_by_user_id = $stock_transfer_request_list->view_by_user_id;
                $nested_data['view_user']= '';
                if($view_by_user_id != NULL){
                    $nested_data['view_user']=User::where('id',$view_by_user_id)->pluck('name')->first();
                }

                array_push($data, $nested_data);
            }

            $success['stock_transfer_request_list'] =  $data;
            return response()->json(['success'=>true,'response' => $success], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Stock Transfer Request List Found!'], $this->failStatus);
        }
        } catch (\Exception $e) {
            //return $e->getMessage();
            $response = APIHelpers::createAPIResponse(false,500,'Internal Server Error.',null);
            return response()->json($response,500);
        }
    }

    public function storeToWarehouseStockRequestDetails(Request $request){
        try {
            $stock_transfer_request_details = DB::table('stock_transfer_requests')
                ->join('stock_transfer_request_details','stock_transfer_requests.id','stock_transfer_request_details.stock_transfer_request_id')
                ->leftJoin('products','stock_transfer_request_details.product_id','products.id')
                ->leftJoin('product_units','stock_transfer_request_details.product_unit_id','product_units.id')
                ->leftJoin('product_brands','stock_transfer_request_details.product_brand_id','product_brands.id')
                ->where('stock_transfer_requests.id',$request->stock_transfer_request_id)
                ->select(
                    'products.id as product_id',
                    'products.name as product_name',
                    'product_units.id as product_unit_id',
                    'product_units.name as product_unit_name',
                    'product_brands.id as product_brand_id',
                    'product_brands.name as product_brand_name',
                    'stock_transfer_requests.request_from_store_id',
                    'stock_transfer_requests.request_to_warehouse_id',
                    'stock_transfer_request_details.request_qty as qty',
                    'stock_transfer_request_details.id as stock_transfer_request_detail_id',
                    'stock_transfer_request_details.price',
                    'stock_transfer_request_details.sub_total',
                    'stock_transfer_request_details.vat_amount'
                )
                ->get();

            if(count($stock_transfer_request_details) === 0){
                $response = APIHelpers::createAPIResponse(true,404,'No Stock Transfer Request Details Found.',null);
                return response()->json($response,404);
            }else{
                $store_stock_request_arr = [];
                foreach ($stock_transfer_request_details as $stock_transfer_request_detail){
                    $current_stock = warehouseProductCurrentStock($stock_transfer_request_detail->request_to_warehouse_id,$stock_transfer_request_detail->product_id);

                    $nested_data['product_id']=$stock_transfer_request_detail->product_id;
                    $nested_data['product_name']=$stock_transfer_request_detail->product_name;
                    $nested_data['product_unit_id']=$stock_transfer_request_detail->product_unit_id;
                    $nested_data['product_unit_name']=$stock_transfer_request_detail->product_unit_name;
                    $nested_data['product_brand_id']=$stock_transfer_request_detail->product_brand_id;
                    $nested_data['product_brand_name']=$stock_transfer_request_detail->product_brand_name;
                    $nested_data['qty']=$stock_transfer_request_detail->qty;
                    $nested_data['stock_transfer_request_detail_id']=$stock_transfer_request_detail->stock_transfer_request_detail_id;
                    $nested_data['price']=$stock_transfer_request_detail->price;
                    $nested_data['sub_total']=$stock_transfer_request_detail->sub_total;
                    $nested_data['vat_amount']=$stock_transfer_request_detail->vat_amount;
                    $nested_data['current_stock']=$current_stock;

                    array_push($store_stock_request_arr,$nested_data);
                }
                $response = APIHelpers::createAPIResponse(false,200,'',$store_stock_request_arr);
                return response()->json($response,200);
            }
        } catch (\Exception $e) {
            //return $e->getMessage();
            $response = APIHelpers::createAPIResponse(false,500,'Internal Server Error.',null);
            return response()->json($response,500);
        }
    }

    public function storeToWarehouseStockRequestDelete(Request $request){
        try {
            $check_exists_stock_transfer_request = DB::table("stock_transfer_requests")->where('id',$request->stock_transfer_request_id)->pluck('id')->first();
            if($check_exists_stock_transfer_request == null){
                $response = APIHelpers::createAPIResponse(true,404,'No Stock Transfer Request Found.',null);
                return response()->json($response,404);
            }

            $stockTransferRequest = StockTransferRequest::find($request->stock_transfer_request_id);
            $delete_stockTransferRequest = $stockTransferRequest->delete();

            DB::table('stock_transfer_request_details')->where('stock_transfer_request_id',$request->stock_transfer_request_id)->delete();

            if($delete_stockTransferRequest)
            {
                $response = APIHelpers::createAPIResponse(false,200,'Stock Transfer Successfully Soft Deleted.',null);
                return response()->json($response,200);
            }else{
                $response = APIHelpers::createAPIResponse(true,400,'Stock Transfer Updated Failed.',null);
                return response()->json($response,400);
            }
        } catch (\Exception $e) {
            //return $e->getMessage();
            $response = APIHelpers::createAPIResponse(false,500,'Internal Server Error.',null);
            return response()->json($response,500);
        }
    }

    public function storeToWarehouseStockRequestSingleProductRemove(Request $request){
        $check_exists_stock_transfer_request = DB::table("stock_transfer_requests")->where('id',$request->stock_transfer_request_id)->pluck('id')->first();
        if($check_exists_stock_transfer_request == null){
            return response()->json(['success'=>false,'response'=>'No Stock Transfer Request Found!'], $this->failStatus);
        }

        $affected_row = DB::table('stock_transfer_request_details')->delete($request->stock_transfer_request_detail_id);
        if($affected_row) {
            return response()->json(['success'=>true,'response' =>'Single Product Successfully Removed!'], $this->successStatus);
        } else{
            return response()->json(['success'=>false,'response'=>'Single Product Not Deleted!'], $this->failStatus);
        }
    }

    public function storeToWarehouseStockRequestViewUpdate(Request $request){
        $this->validate($request, [
            'stock_transfer_request_id'=> 'required',
        ]);

        $stock_transfer_request = StockTransferRequest::find($request->stock_transfer_request_id);
        $stock_transfer_request->view_by_user_id = Auth::user()->id;
        $stock_transfer_request->view_status = $request->view_status;
        $affectedRow = $stock_transfer_request->save();

        if($affectedRow){
            return response()->json(['success'=>true,'response' => 'Stock Transfer Request View Successfully Updated.'], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Stock Transfer Request View Successfully Updated.!'], $this->failStatus);
        }
    }

    public function warehouseToStoreStockCreate(Request $request){
        try {
            // required and unique
            $validator = Validator::make($request->all(), [
                'warehouse_id'=> 'required',
                'store_id'=> 'required',
            ]);

            if ($validator->fails()) {
                $response = APIHelpers::createAPIResponse(true,400,$validator->errors(),null);
                return response()->json($response,400);
            }

            $date = date('Y-m-d');
            $date_time = date('Y-m-d h:i:s');

            $user_id = Auth::user()->id;
            $warehouse_id = $request->warehouse_id;
            $store_id = $request->store_id;
            $miscellaneous_comment = $request->miscellaneous_comment;
            $miscellaneous_charge = $request->miscellaneous_charge ? $request->miscellaneous_charge : 0;


            $get_invoice_no = StockTransfer::latest()->pluck('invoice_no')->first();
            if(!empty($get_invoice_no)){
                $get_invoice = str_replace("STN-","",$get_invoice_no);
                $invoice_no = $get_invoice+1;
            }else{
                $invoice_no = 1000;
            }

            $sub_total = 0;
            $total_amount = 0;
            //$total_vat_amount = 0;
            foreach ($request->products as $data) {
                $product_id = $data['product_id'];
                //$price = Product::where('id',$product_id)->pluck('purchase_price')->first();
                $Product_info = Product::where('id',$product_id)->first();
                //$total_vat_amount += ($data['qty']*$Product_info->vat_amount);
                //$total_amount += ($data['qty']*$Product_info->vat_amount) + ($data['qty']*$Product_info->purchase_price);
                $sub_total += $data['qty']*$Product_info->purchase_price;
                $total_amount += $data['qty']*$Product_info->purchase_price;
            }

            $total_amount += $miscellaneous_charge;

            $final_invoice = 'STN-'.$invoice_no;
            $stock_transfer = new StockTransfer();
            $stock_transfer->invoice_no=$final_invoice;
            $stock_transfer->user_id=Auth::user()->id;
            $stock_transfer->warehouse_id = $warehouse_id;
            $stock_transfer->store_id = $store_id;
            $stock_transfer->sub_total = $sub_total;
            $stock_transfer->total_vat_amount = 0;
            $stock_transfer->miscellaneous_comment = $miscellaneous_comment;
            $stock_transfer->miscellaneous_charge = $miscellaneous_charge;
            $stock_transfer->total_amount = $total_amount;
            $stock_transfer->paid_amount = 0;
            $stock_transfer->due_amount = $total_amount;
            $stock_transfer->issue_date = $date;
            $stock_transfer->due_date = $date;
            $stock_transfer->save();
            $stock_transfer_insert_id = $stock_transfer->id;

            $insert_id = false;

            foreach ($request->products as $data) {

                $product_id = $data['product_id'];
                $product_info = Product::where('id',$product_id)->first();


                $stock_transfer_detail = new StockTransferDetail();
                $stock_transfer_detail->stock_transfer_id = $stock_transfer_insert_id;
                $stock_transfer_detail->product_unit_id = $data['product_unit_id'];
                $stock_transfer_detail->product_brand_id = $data['product_brand_id'] ? $data['product_brand_id'] : NULL;
                $stock_transfer_detail->product_id = $product_id;
                $stock_transfer_detail->barcode = $product_info->barcode;
                $stock_transfer_detail->qty = $data['qty'];
                //$stock_transfer_detail->vat_amount = $data['qty']*$product_info->vat_percentage;
                $stock_transfer_detail->vat_amount = 0;
                $stock_transfer_detail->price = $product_info->purchase_price;
                //$stock_transfer_detail->sub_total = ($data['qty']*$product_info->vat_percentage) + ($data['qty']*$product_info->purchase_price);
                $stock_transfer_detail->sub_total = $data['qty']*$product_info->purchase_price;
                $stock_transfer_detail->issue_date = $date;
                $stock_transfer_detail->save();


                $check_previous_warehouse_current_stock = Stock::where('warehouse_id',$warehouse_id)
                    ->where('product_id',$product_id)
                    ->where('stock_where','warehouse')
                    ->latest('id','desc')
                    ->pluck('current_stock')
                    ->first();

                if($check_previous_warehouse_current_stock){
                    $previous_warehouse_current_stock = $check_previous_warehouse_current_stock;
                }else{
                    $previous_warehouse_current_stock = 0;
                }

                // stock out warehouse product
                $stock = new Stock();
                $stock->ref_id = $stock_transfer_insert_id;
                $stock->user_id = $user_id;
                $stock->warehouse_id = $warehouse_id;
                $stock->store_id = NULL;
                $stock->product_id = $product_id;
                $stock->product_unit_id = $data['product_unit_id'];
                $stock->product_brand_id = $data['product_brand_id'] ? $data['product_brand_id'] : NULL;
                $stock->stock_type = 'from_warehouse_to_store';
                $stock->stock_where = 'warehouse';
                $stock->stock_in_out = 'stock_out';
                $stock->previous_stock = $previous_warehouse_current_stock;
                $stock->stock_in = 0;
                $stock->stock_out = $data['qty'];
                $stock->current_stock = $previous_warehouse_current_stock - $data['qty'];
                $stock->stock_date = $date;
                $stock->stock_date_time = $date_time;
                $stock->save();


                $check_previous_store_current_stock = Stock::where('warehouse_id',$warehouse_id)
                    ->where('store_id',$store_id)
                    ->where('product_id',$product_id)
                    ->where('stock_where','store')
                    ->latest('id','desc')
                    ->pluck('current_stock')
                    ->first();

                if($check_previous_store_current_stock){
                    $previous_store_current_stock = $check_previous_store_current_stock;
                }else{
                    $previous_store_current_stock = 0;
                }

                // stock in store product
                $stock = new Stock();
                $stock->ref_id = $stock_transfer_insert_id;
                $stock->user_id = $user_id;
                $stock->warehouse_id = $warehouse_id;
                $stock->store_id = $store_id;
                $stock->product_id = $product_id;
                $stock->product_unit_id = $data['product_unit_id'];
                $stock->product_brand_id = $data['product_brand_id'] ? $data['product_brand_id'] : NULL;
                $stock->stock_type = 'from_warehouse_to_store';
                $stock->stock_where = 'store';
                $stock->stock_in_out = 'stock_in';
                $stock->previous_stock = $previous_store_current_stock;
                $stock->stock_in = $data['qty'];
                $stock->stock_out = 0;
                $stock->current_stock = $previous_store_current_stock + $data['qty'];
                $stock->stock_date = $date;
                $stock->stock_date_time = $date_time;
                $stock->save();
                $insert_id = $stock->id;

                // warehouse current stock
                $warehouse_current_stock_update = WarehouseCurrentStock::where('warehouse_id',$request->warehouse_id)
                    ->where('product_id',$product_id)
                    ->first();
                $exists_current_stock = $warehouse_current_stock_update->current_stock;
                $final_warehouse_current_stock = $exists_current_stock - $data['qty'];
                $warehouse_current_stock_update->current_stock=$final_warehouse_current_stock;
                $warehouse_current_stock_update->save();

                // warehouse store current stock
                $check_exists_warehouse_store_current_stock = WarehouseStoreCurrentStock::where('warehouse_id',$warehouse_id)
                    ->where('store_id',$store_id)
                    ->where('product_id',$product_id)
                    ->first();

                if($check_exists_warehouse_store_current_stock){
                    $exists_current_stock = $check_exists_warehouse_store_current_stock->current_stock;
                    $final_warehouse_current_stock = $exists_current_stock + $data['qty'];
                    $check_exists_warehouse_store_current_stock->current_stock=$final_warehouse_current_stock;
                    $check_exists_warehouse_store_current_stock->save();
                }else{
                    $warehouse_store_current_stock = new WarehouseStoreCurrentStock();
                    $warehouse_store_current_stock->warehouse_id=$warehouse_id;
                    $warehouse_store_current_stock->store_id=$store_id;
                    $warehouse_store_current_stock->product_id=$product_id;
                    $warehouse_store_current_stock->current_stock=$data['qty'];
                    $warehouse_store_current_stock->save();
                }
            }

            // transaction
    //        $transaction = new Transaction();
    //        $transaction->ref_id = $stock_transfer_insert_id;
    //        $transaction->invoice_no = $final_invoice;
    //        $transaction->user_id = $user_id;
    //        $transaction->warehouse_id = $request->warehouse_id;
    //        $transaction->party_id = $request->party_id;
    //        $transaction->transaction_type = '';
    //        $transaction->payment_type = 'Cash';
    //        $transaction->amount = $request->total_amount;
    //        $transaction->transaction_date = $date;
    //        $transaction->transaction_date_time = $date_time;
    //        $transaction->save();

            $response = APIHelpers::createAPIResponse(false,201,'Warehouse To Store Stock Added Successfully.',null);
            return response()->json($response,201);
        } catch (\Exception $e) {
            //return $e->getMessage();
            $response = APIHelpers::createAPIResponse(false,500,'Internal Server Error.',null);
            return response()->json($response,500);
        }
    }

    public function warehouseToStoreStockEdit(Request $request){
        try {
            // required and unique
            $validator = Validator::make($request->all(), [
                'stock_transfer_id'=> 'required',
                'warehouse_id'=> 'required',
                'store_id'=> 'required',
            ]);

            if ($validator->fails()) {
                $response = APIHelpers::createAPIResponse(true,400,$validator->errors(),null);
                return response()->json($response,400);
            }

            $date = date('Y-m-d');
            $date_time = date('Y-m-d h:i:s');

            $user_id = Auth::user()->id;
            $warehouse_id = $request->warehouse_id;
            $store_id = $request->store_id;
            $miscellaneous_comment = $request->miscellaneous_comment;
            $miscellaneous_charge = $request->miscellaneous_charge ? $request->miscellaneous_charge : 0;




            $sub_total = 0;
            $total_amount = 0;

            foreach ($request->products as $data) {
                $product_id = $data['product_id'];
                $Product_info = Product::where('id',$product_id)->first();
                $sub_total += $data['qty']*$Product_info->purchase_price;
                $total_amount += $data['qty']*$Product_info->purchase_price;
            }

            $total_amount += $miscellaneous_charge;


            $stock_transfer = StockTransfer::find($request->stock_transfer_id);
            $stock_transfer->user_id=Auth::user()->id;
            $stock_transfer->warehouse_id = $warehouse_id;
            $stock_transfer->store_id = $store_id;
            $stock_transfer->sub_total = $sub_total;
            $stock_transfer->total_vat_amount = 0;
            $stock_transfer->miscellaneous_comment = $miscellaneous_comment;
            $stock_transfer->miscellaneous_charge = $miscellaneous_charge;
            $stock_transfer->total_amount = $total_amount;
            $stock_transfer->paid_amount = 0;
            $stock_transfer->due_amount = $total_amount;
            $stock_transfer->issue_date = $date;
            $stock_transfer->due_date = $date;
            $affectedRow = $stock_transfer->save();

            if($affectedRow){
                foreach ($request->products as $data) {

                    $product_id = $data['product_id'];
                    $product_info = Product::where('id',$product_id)->first();


                    $stock_transfer_detail = StockTransferDetail::where('id',$data['stock_transfer_detail_id'])->first();
                    $previous_stock_transfer_qty = $stock_transfer_detail->qty;
                    $stock_transfer_detail->product_unit_id = $data['product_unit_id'];
                    $stock_transfer_detail->product_brand_id = $data['product_brand_id'] ? $data['product_brand_id'] : NULL;
                    $stock_transfer_detail->product_id = $product_id;
                    $stock_transfer_detail->barcode = $product_info->barcode;
                    $stock_transfer_detail->qty = $data['qty'];
                    $stock_transfer_detail->vat_amount = 0;
                    $stock_transfer_detail->price = $product_info->purchase_price;
                    $stock_transfer_detail->sub_total = $data['qty']*$product_info->purchase_price;
                    $stock_transfer_detail->issue_date = $date;
                    $stock_transfer_detail->save();

                    // product stock
                    $stock_row = Stock::where('warehouse_id',$warehouse_id)->where('product_id',$product_id)->latest()->first();
                    $current_stock = $stock_row->current_stock;

                    // warehouse current stock
                    $warehouse_current_stock_update = WarehouseCurrentStock::where('warehouse_id',$request->warehouse_id)
                        ->where('product_id',$product_id)
                        ->first();
                    $exists_current_stock = $warehouse_current_stock_update->current_stock;


                    // warehouse store current stock
                    $warehouse_store_current_stock_update = WarehouseStoreCurrentStock::where('warehouse_id',$request->warehouse_id)
                        ->where('store_id',$store_id)
                        ->where('product_id',$product_id)
                        ->first();
                    $exists_warehouse_store_current_stock = $warehouse_store_current_stock_update->current_stock;
                    if($stock_row->stock_in != $data['qty']){
                        if($data['qty'] > $stock_row->stock_in){
                            $new_stock_out = $data['qty'] - $previous_stock_transfer_qty;

                            // stock out warehouse product
                            $stock = new Stock();
                            $stock->ref_id = $request->stock_transfer_id;
                            $stock->user_id = $user_id;
                            $stock->warehouse_id = $warehouse_id;
                            $stock->store_id = NULL;
                            $stock->product_id = $product_id;
                            $stock->product_unit_id = $data['product_unit_id'];
                            $stock->product_brand_id = $data['product_brand_id'] ? $data['product_brand_id'] : NULL;
                            $stock->stock_type = 'from_warehouse_to_store';
                            $stock->stock_where = 'warehouse';
                            $stock->stock_in_out = 'stock_out';
                            $stock->previous_stock = $current_stock;
                            $stock->stock_in = 0;
                            $stock->stock_out=$new_stock_out;
                            $stock->current_stock=$current_stock - $new_stock_out;
                            $stock->stock_date = $date;
                            $stock->stock_date_time = $date_time;
                            $stock->save();

                            // warehouse current stock
                            $warehouse_current_stock_update->current_stock=$exists_current_stock - $new_stock_out;
                            $warehouse_current_stock_update->save();


                            $new_stock_in = $data['qty'] - $previous_stock_transfer_qty;
                            // stock in store product
                            $stock = new Stock();
                            $stock->ref_id = $request->stock_transfer_id;
                            $stock->user_id = $user_id;
                            $stock->warehouse_id = $warehouse_id;
                            $stock->store_id = $store_id;
                            $stock->product_id = $product_id;
                            $stock->product_unit_id = $data['product_unit_id'];
                            $stock->product_brand_id = $data['product_brand_id'] ? $data['product_brand_id'] : NULL;
                            $stock->stock_type = 'from_warehouse_to_store';
                            $stock->stock_where = 'store';
                            $stock->stock_in_out = 'stock_in';
                            $stock->previous_stock = $exists_warehouse_store_current_stock;
                            $stock->stock_in = $new_stock_in;
                            $stock->stock_out = 0;
                            $stock->current_stock = $exists_warehouse_store_current_stock + $new_stock_in;
                            $stock->stock_date = $date;
                            $stock->stock_date_time = $date_time;
                            $stock->save();

                            // warehouse store current stock
                            $warehouse_store_current_stock_update->current_stock=$exists_warehouse_store_current_stock + $new_stock_in;
                            $warehouse_store_current_stock_update->save();

                        }else{
                            $new_stock_in = $previous_stock_transfer_qty - $data['qty'];

                            // stock out warehouse product
                            $stock = new Stock();
                            $stock->ref_id = $request->stock_transfer_id;
                            $stock->user_id = $user_id;
                            $stock->warehouse_id = $warehouse_id;
                            $stock->store_id = NULL;
                            $stock->product_id = $product_id;
                            $stock->product_unit_id = $data['product_unit_id'];
                            $stock->product_brand_id = $data['product_brand_id'] ? $data['product_brand_id'] : NULL;
                            $stock->stock_type = 'from_warehouse_to_store';
                            $stock->stock_where = 'warehouse';
                            $stock->stock_in_out = 'stock_out';
                            $stock->previous_stock = $current_stock;
                            $stock->stock_in=$new_stock_in;
                            $stock->stock_out = 0;
                            $stock->current_stock=$current_stock + $new_stock_in;
                            $stock->stock_date = $date;
                            $stock->stock_date_time = $date_time;
                            $stock->save();

                            // warehouse current stock
                            $warehouse_current_stock_update->current_stock=$exists_current_stock + $new_stock_in;
                            $warehouse_current_stock_update->save();


                            $new_stock_out = $previous_stock_transfer_qty - $data['qty'];
                            // stock in store product
                            $stock = new Stock();
                            $stock->ref_id = $request->stock_transfer_id;
                            $stock->user_id = $user_id;
                            $stock->warehouse_id = $warehouse_id;
                            $stock->store_id = $store_id;
                            $stock->product_id = $product_id;
                            $stock->product_unit_id = $data['product_unit_id'];
                            $stock->product_brand_id = $data['product_brand_id'] ? $data['product_brand_id'] : NULL;
                            $stock->stock_type = 'from_warehouse_to_store';
                            $stock->stock_where = 'store';
                            $stock->stock_in_out = 'stock_in';
                            $stock->previous_stock = $exists_warehouse_store_current_stock;
                            $stock->stock_in = 0;
                            $stock->stock_out = $new_stock_out;
                            $stock->current_stock = $exists_warehouse_store_current_stock - $new_stock_out;
                            $stock->stock_date = $date;
                            $stock->stock_date_time = $date_time;
                            $stock->save();

                            // warehouse store current stock
                            $warehouse_store_current_stock_update->current_stock=$exists_warehouse_store_current_stock - $new_stock_out;
                            $warehouse_store_current_stock_update->save();
                        }
                    }
                }
            }

            if($affectedRow){
                $response = APIHelpers::createAPIResponse(false,200,'Warehouse To Store Stock Updated Successfully.',null);
                return response()->json($response,200);
            }else{
                $response = APIHelpers::createAPIResponse(true,400,'Warehouse To Store Stock Updated Failed.',null);
                return response()->json($response,400);
            }
        } catch (\Exception $e) {
            //return $e->getMessage();
            $response = APIHelpers::createAPIResponse(false,500,'Internal Server Error.',null);
            return response()->json($response,500);
        }
    }

    public function warehouseToStoreStockRemove(Request $request){
        try {
            // required and unique
            $validator = Validator::make($request->all(), [
                'stock_transfer_id'=> 'required',
                'warehouse_id'=> 'required',
                'store_id'=> 'required',
                'total_amount'=> 'required',
                'stock_transfer_detail_id'=> 'required',
                'product_id'=> 'required',
                'sub_total'=> 'required',
            ]);

            if ($validator->fails()) {
                $response = APIHelpers::createAPIResponse(true,400,$validator->errors(),null);
                return response()->json($response,400);
            }

            $date = date('Y-m-d');
            $date_time = date('Y-m-d h:i:s');

            $user_id = Auth::user()->id;
            $warehouse_id = $request->warehouse_id;
            $store_id = $request->store_id;

            $stock_transfer = StockTransfer::find($request->stock_transfer_id);
            $stock_transfer->user_id=Auth::user()->id;
            $stock_transfer->warehouse_id = $warehouse_id;
            $stock_transfer->store_id = $store_id;

            $stock_transfer->total_amount = $request->total_amount - $request->sub_total;
            $affectedRow = $stock_transfer->save();

            if($affectedRow){

                $product_id = $request->product_id;
                $product_info = Product::where('id',$product_id)->first();

                // product stock
                $stock_row = Stock::where('warehouse_id',$warehouse_id)->where('product_id',$product_id)->latest()->first();
                $current_stock = $stock_row->current_stock;

                // warehouse current stock
                $warehouse_current_stock_update = WarehouseCurrentStock::where('warehouse_id',$request->warehouse_id)
                    ->where('product_id',$product_id)
                    ->first();
                $exists_current_stock = $warehouse_current_stock_update->current_stock;


                // warehouse store current stock
                $warehouse_store_current_stock_update = WarehouseStoreCurrentStock::where('warehouse_id',$request->warehouse_id)
                    ->where('store_id',$store_id)
                    ->where('product_id',$product_id)
                    ->first();
                $exists_warehouse_store_current_stock = $warehouse_store_current_stock_update->current_stock;

                // stock out warehouse product
                $stock = new Stock();
                $stock->ref_id = $request->stock_transfer_id;
                $stock->user_id = $user_id;
                $stock->warehouse_id = $warehouse_id;
                $stock->store_id = $store_id;
                $stock->product_id = $product_id;
                $stock->product_unit_id = $product_info->product_unit_id;
                $stock->product_brand_id = $product_info->product_brand_id ? $product_info->product_brand_id : NULL;
                $stock->stock_type = 'warehouse_to_store_stock_delete';
                $stock->stock_where = 'warehouse';
                $stock->stock_in_out = 'stock_in';
                $stock->previous_stock = $current_stock;
                $stock->stock_in = $request->qty;
                $stock->stock_out = 0;
                $stock->current_stock = $current_stock + $request->qty;
                $stock->stock_date = $date;
                $stock->stock_date_time = $date_time;
                $stock->save();

                // warehouse current stock
                $warehouse_current_stock_update->current_stock=$exists_current_stock + $request->qty;
                $warehouse_current_stock_update->save();


                // stock in store product
                $stock = new Stock();
                $stock->ref_id = $request->stock_transfer_id;
                $stock->user_id = $user_id;
                $stock->warehouse_id = $warehouse_id;
                $stock->store_id = $store_id;
                $stock->product_id = $product_id;
                $stock->product_unit_id = $product_info->product_unit_id;
                $stock->product_brand_id = $product_info->product_brand_id ? $product_info->product_brand_id : NULL;
                $stock->stock_type = 'warehouse_to_store_stock_delete';
                $stock->stock_where = 'store';
                $stock->stock_in_out = 'stock_out';
                $stock->previous_stock = $exists_warehouse_store_current_stock;
                $stock->stock_in = 0;
                $stock->stock_out = $request->qty;
                $stock->current_stock = $exists_warehouse_store_current_stock - $request->qty;
                $stock->stock_date = $date;
                $stock->stock_date_time = $date_time;
                $stock->save();

                // warehouse store current stock
                $warehouse_store_current_stock_update->current_stock=$exists_warehouse_store_current_stock - $request->qty;
                $warehouse_store_current_stock_update->save();

                // delete stock transfer detail
                StockTransferDetail::where('id','stock_transfer_detail_id')->delete();

            }

            if($affectedRow)
            {
                $response = APIHelpers::createAPIResponse(false,200,'Warehouse To Store Stock Successfully Soft Deleted.',null);
                return response()->json($response,200);
            }else{
                $response = APIHelpers::createAPIResponse(true,400,'Warehouse To Store Stock Updated Failed.',null);
                return response()->json($response,400);
            }
        } catch (\Exception $e) {
            //return $e->getMessage();
            $response = APIHelpers::createAPIResponse(false,500,'Internal Server Error.',null);
            return response()->json($response,500);
        }
    }

    public function stockTransferSingleProductRemove(Request $request){
        $this->validate($request, [
            'stock_transfer_id'=> 'required',
            'stock_transfer_detail_id'=> 'required',
        ]);


        $check_exists_stock_transfers = DB::table("stock_transfers")->where('id',$request->stock_transfer_id)->pluck('id')->first();
        if($check_exists_stock_transfers == null){
            return response()->json(['success'=>false,'response'=>'No Product Purchase Found!'], $this->failStatus);
        }

        $stockTransfer = StockTransfer::find($request->stock_transfer_id);
        if($stockTransfer) {
            //$discount_amount = $stockTransfer->miscellaneous_charge;
            //$paid_amount = $stockTransfer->paid_amount;
            $due_amount = $stockTransfer->due_amount;
            //$total_vat_amount = $stockTransfer->total_vat_amount;
            $total_amount = $stockTransfer->total_amount;

            $stock_transfer_detail = DB::table('stock_transfer_details')->where('id', $request->stock_transfer_detail_id)->first();
            $product_unit_id = $stock_transfer_detail->product_unit_id;
            $product_brand_id = $stock_transfer_detail->product_brand_id;
            $product_id = $stock_transfer_detail->product_id;
            $qty = $stock_transfer_detail->qty;

            if ($stock_transfer_detail) {

                //$remove_discount = $stock_transfer_detail->discount;
                //$remove_vat_amount = $stock_transfer_detail->vat_amount;
                $remove_sub_total = $stock_transfer_detail->sub_total;


                //$productSale->discount_amount = $discount_amount - $remove_discount;
                //$productPurchase->discount_amount = $total_vat_amount - $remove_vat_amount;
                $stockTransfer->due_amount = $due_amount - $remove_sub_total;
                $stockTransfer->total_amount = $total_amount - $remove_sub_total;
                $stockTransfer->save();

                // delete single product
                //$product_sale_detail->delete();
                DB::table('stock_transfer_details')->delete($stock_transfer_detail->id);
            }

            $date = date('Y-m-d');
            $date_time = date('Y-m-d h:i:s');
            $user_id = Auth::user()->id;



            // product stock
            $stock_row = Stock::where('warehouse_id',$stockTransfer->warehouse_id)->where('product_id',$product_id)->latest()->first();
            $current_stock = $stock_row->current_stock;

            // warehouse current stock
            $warehouse_current_stock_update = WarehouseCurrentStock::where('warehouse_id',$stockTransfer->warehouse_id)
                ->where('product_id',$product_id)
                ->first();
            $exists_current_stock = $warehouse_current_stock_update->current_stock;


            // warehouse store current stock
            $warehouse_store_current_stock_update = WarehouseStoreCurrentStock::where('warehouse_id',$stockTransfer->warehouse_id)
                ->where('store_id',$stockTransfer->store_id)
                ->where('product_id',$product_id)
                ->first();
            $exists_warehouse_store_current_stock = $warehouse_store_current_stock_update->current_stock;

            // stock out warehouse product
            $stock = new Stock();
            $stock->ref_id = $request->stock_transfer_id;
            $stock->user_id = $user_id;
            $stock->warehouse_id = $stockTransfer->warehouse_id;
            $stock->store_id = $stockTransfer->store_id;
            $stock->product_id = $product_id;
            $stock->product_unit_id = $product_unit_id;
            $stock->product_brand_id = $product_brand_id ? $product_brand_id : NULL;
            $stock->stock_type = 'warehouse_to_store_stock_delete';
            $stock->stock_where = 'warehouse';
            $stock->stock_in_out = 'stock_in';
            $stock->previous_stock = $current_stock;
            $stock->stock_in = $qty;
            $stock->stock_out = 0;
            $stock->current_stock = $current_stock + $qty;
            $stock->stock_date = $date;
            $stock->stock_date_time = $date_time;
            $stock->save();

            // warehouse current stock
            $warehouse_current_stock_update->current_stock=$exists_current_stock + $qty;
            $warehouse_current_stock_update->save();


            // stock in store product
            $stock = new Stock();
            $stock->ref_id = $request->stock_transfer_id;
            $stock->user_id = $user_id;
            $stock->warehouse_id = $stockTransfer->warehouse_id;
            $stock->store_id = $stockTransfer->store_id;
            $stock->product_id = $product_id;
            $stock->product_unit_id = $product_unit_id;
            $stock->product_brand_id = $product_brand_id ? $product_brand_id : NULL;
            $stock->stock_type = 'warehouse_to_store_stock_delete';
            $stock->stock_where = 'store';
            $stock->stock_in_out = 'stock_out';
            $stock->previous_stock = $exists_warehouse_store_current_stock;
            $stock->stock_in = 0;
            $stock->stock_out = $qty;
            $stock->current_stock = $exists_warehouse_store_current_stock - $qty;
            $stock->stock_date = $date;
            $stock->stock_date_time = $date_time;
            $stock->save();

            // warehouse store current stock
            $warehouse_store_current_stock_update->current_stock=$exists_warehouse_store_current_stock - $qty;
            $affected_row = $warehouse_store_current_stock_update->save();
            if($affected_row){
                return response()->json(['success'=>true,'response' => 'Warehouse To Store Stock Removed Successfully.'], $this->successStatus);
            }else{
                return response()->json(['success'=>false,'response'=>'No Warehouse To Store Stock Removed Successfully!'], $this->failStatus);
            }
        }else{
            return response()->json(['success'=>false,'response'=>'No Warehouse To Store Stock Removed Successfully!'], $this->failStatus);
        }
    }


    public function storeCurrentStockList(Request $request){

        $store_stock_product_list = DB::table('warehouse_store_current_stocks')
            ->join('warehouses','warehouse_store_current_stocks.warehouse_id','warehouses.id')
            ->leftJoin('stores','warehouse_store_current_stocks.store_id','stores.id')
            ->leftJoin('products','warehouse_store_current_stocks.product_id','products.id')
            ->leftJoin('product_units','products.product_unit_id','product_units.id')
            ->leftJoin('product_brands','products.product_brand_id','product_brands.id')
            ->where('warehouse_store_current_stocks.store_id',$request->store_id)
            ->select('warehouse_store_current_stocks.*','warehouses.name as warehouse_name','stores.name as store_name','products.name as product_name','products.purchase_price','products.whole_sale_price','products.selling_price','products.item_code','products.barcode','products.image','products.vat_status','products.vat_percentage','products.vat_amount','products.vat_whole_amount','product_units.id as product_unit_id','product_units.name as product_unit_name','product_brands.id as product_brand_id','product_brands.name as product_brand_name')
            ->get();

        $store_stock_product = [];
        foreach($store_stock_product_list as $stock_row){
            $nested_data['stock_id'] = $stock_row->id;
            $nested_data['warehouse_id'] = $stock_row->warehouse_id;
            $nested_data['warehouse_name'] = $stock_row->warehouse_name;
            $nested_data['store_id'] = $stock_row->store_id;
            $nested_data['store_name'] = $stock_row->store_name;
            $nested_data['product_id'] = $stock_row->product_id;
            $nested_data['product_name'] = $stock_row->product_name;
            $nested_data['purchase_price'] = $stock_row->purchase_price;
            $nested_data['whole_sale_price'] = $stock_row->whole_sale_price;
            $nested_data['selling_price'] = $stock_row->selling_price;
            $nested_data['vat_status'] = $stock_row->vat_status;
            $nested_data['vat_percentage'] = $stock_row->vat_percentage;
            $nested_data['vat_amount'] = $stock_row->vat_amount;
            $nested_data['vat_whole_amount'] = $stock_row->vat_whole_amount;
            $nested_data['item_code'] = $stock_row->item_code;
            $nested_data['barcode'] = $stock_row->barcode;
            $nested_data['image'] = $stock_row->image;
            $nested_data['product_unit_id'] = $stock_row->product_unit_id;
            $nested_data['product_unit_name'] = $stock_row->product_unit_name;
            $nested_data['product_brand_id'] = $stock_row->product_brand_id;
            $nested_data['product_brand_name'] = $stock_row->product_brand_name;
            $nested_data['current_stock'] = $stock_row->current_stock;

            array_push($store_stock_product,$nested_data);

        }

        if($store_stock_product)
        {
            $success['store_current_stock_list'] =  $store_stock_product;
            return response()->json(['success'=>true,'response' => $success], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Store Current Stock List Found!'], $this->failStatus);
        }
    }

    public function storeCurrentProductStock(Request $request){

        $store_current_product_stock = DB::table('warehouse_store_current_stocks')
            ->where('product_id',$request->product_id)
            ->where('store_id',$request->store_id)
            ->pluck('current_stock')
            ->first();

        if($store_current_product_stock == null){
            $store_current_product_stock = 0;
        }

        $success['store_current_product_stock'] =  $store_current_product_stock;
        return response()->json(['success'=>true,'response' => $success], $this->successStatus);

    }

    public function storeCurrentStockListWithoutZero(Request $request){
        $store_stock_product_list = DB::table('warehouse_store_current_stocks')
            ->join('warehouses','warehouse_store_current_stocks.warehouse_id','warehouses.id')
            ->leftJoin('products','warehouse_store_current_stocks.product_id','products.id')
            ->leftJoin('product_units','products.product_unit_id','product_units.id')
            ->leftJoin('product_brands','products.product_brand_id','product_brands.id')
            ->where('warehouse_store_current_stocks.store_id',$request->store_id)
            ->where('warehouse_store_current_stocks.current_stock','!=',0)
            ->select('warehouse_store_current_stocks.*','warehouses.name as warehouse_name','products.name as product_name','products.purchase_price','products.whole_sale_price','products.selling_price','products.item_code','products.barcode','products.image','products.vat_status','products.vat_percentage','products.vat_amount','products.vat_whole_amount','product_units.id as product_unit_id','product_units.name as product_unit_name','product_brands.id as product_brand_id','product_brands.name as product_brand_name')
            ->get();

        $store_stock_product = [];
        foreach($store_stock_product_list as $stock_row){
            $nested_data['stock_id'] = $stock_row->id;
            $nested_data['warehouse_id'] = $stock_row->warehouse_id;
            $nested_data['warehouse_name'] = $stock_row->warehouse_name;
            $nested_data['product_id'] = $stock_row->product_id;
            $nested_data['product_name'] = $stock_row->product_name;
            $nested_data['purchase_price'] = $stock_row->purchase_price;
            $nested_data['whole_sale_price'] = $stock_row->whole_sale_price;
            $nested_data['selling_price'] = $stock_row->selling_price;
            $nested_data['vat_status'] = $stock_row->vat_status;
            $nested_data['vat_percentage'] = $stock_row->vat_percentage;
            $nested_data['vat_amount'] = $stock_row->vat_amount;
            $nested_data['vat_whole_amount'] = $stock_row->vat_whole_amount;
            $nested_data['item_code'] = $stock_row->item_code;
            $nested_data['barcode'] = $stock_row->barcode;
            $nested_data['image'] = $stock_row->image;
            $nested_data['product_unit_id'] = $stock_row->product_unit_id;
            $nested_data['product_unit_name'] = $stock_row->product_unit_name;
            $nested_data['product_brand_id'] = $stock_row->product_brand_id;
            $nested_data['product_brand_name'] = $stock_row->product_brand_name;
            $nested_data['current_stock'] = $stock_row->current_stock;

            array_push($store_stock_product,$nested_data);

        }

        if($store_stock_product)
        {
            $store_info = DB::table('stores')
                ->where('id',$request->store_id)
                ->select('name','phone','email','address')
                ->first();
            $success['store_info'] =  $store_info;
            $success['store_current_stock_list'] =  $store_stock_product;
            return response()->json(['success'=>true,'response' => $success], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Store Current Stock List Found!'], $this->failStatus);
        }
    }

    public function stockTransferList(){
        $stock_transfer_lists = DB::table('stock_transfers')
            ->leftJoin('users','stock_transfers.user_id','users.id')
            ->leftJoin('warehouses','stock_transfers.warehouse_id','warehouses.id')
            ->leftJoin('stores','stock_transfers.store_id','stores.id')
            //->where('stock_transfers.sale_type','whole_sale')
            ->select('stock_transfers.id','stock_transfers.invoice_no','stock_transfers.total_amount','stock_transfers.issue_date','stock_transfers.miscellaneous_comment','stock_transfers.miscellaneous_charge','stock_transfers.total_vat_amount','users.name as user_name','warehouses.id as warehouse_id','warehouses.name as warehouse_name','stores.id as store_id','stores.name as store_name','stores.phone as store_phone','stores.email as store_email','stores.address as store_address')
            ->orderBy('stock_transfers.id','desc')
            ->paginate(12);

        if($stock_transfer_lists)
        {
            $success['stock_transfer_list'] =  $stock_transfer_lists;
            return response()->json(['success'=>true,'response' => $success], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Stock Transfer List Found!'], $this->failStatus);
        }
    }

    public function stockTransferListWithSearch(Request $request){
        try {
            if($request->search){
                $stock_transfer_lists = DB::table('stock_transfers')
                    ->leftJoin('users','stock_transfers.user_id','users.id')
                    ->leftJoin('warehouses','stock_transfers.warehouse_id','warehouses.id')
                    ->leftJoin('stores','stock_transfers.store_id','stores.id')
                    ->where('stock_transfers.invoice_no','like','%'.$request->search.'%')
                    ->orWhere('warehouses.name','like','%'.$request->search.'%')
                    ->orWhere('stores.name','like','%'.$request->search.'%')
                    ->select('stock_transfers.id','stock_transfers.invoice_no','stock_transfers.sub_total','stock_transfers.total_amount','stock_transfers.issue_date','stock_transfers.created_at','stock_transfers.miscellaneous_comment','stock_transfers.miscellaneous_charge','stock_transfers.total_vat_amount','users.name as user_name','warehouses.id as warehouse_id','warehouses.name as warehouse_name','stores.id as store_id','stores.name as store_name','stores.phone as store_phone','stores.email as store_email','stores.address as store_address')
                    ->orderBy('stock_transfers.id','desc')
                    ->paginate(12);
            }else{
                $stock_transfer_lists = DB::table('stock_transfers')
                    ->leftJoin('users','stock_transfers.user_id','users.id')
                    ->leftJoin('warehouses','stock_transfers.warehouse_id','warehouses.id')
                    ->leftJoin('stores','stock_transfers.store_id','stores.id')
                    ->select('stock_transfers.id','stock_transfers.invoice_no','stock_transfers.sub_total','stock_transfers.total_amount','stock_transfers.issue_date','stock_transfers.created_at','stock_transfers.miscellaneous_comment','stock_transfers.miscellaneous_charge','stock_transfers.total_vat_amount','users.name as user_name','warehouses.id as warehouse_id','warehouses.name as warehouse_name','stores.id as store_id','stores.name as store_name','stores.phone as store_phone','stores.email as store_email','stores.address as store_address')
                    ->orderBy('stock_transfers.id','desc')
                    ->paginate(12);
            }

            if($stock_transfer_lists === null){
                $response = APIHelpers::createAPIResponse(true,404,'No Stock Transfer Found.',null);
                return response()->json($response,404);
            }else{
                $response = APIHelpers::createAPIResponse(false,200,'',$stock_transfer_lists);
                return response()->json($response,200);
            }
        } catch (\Exception $e) {
            //return $e->getMessage();
            $response = APIHelpers::createAPIResponse(false,500,'Internal Server Error.',null);
            return response()->json($response,500);
        }
    }

    public function stockTransferDetails(Request $request){
        try {
            $stock_transfer_details = DB::table('stock_transfers')
                ->join('stock_transfer_details','stock_transfers.id','stock_transfer_details.stock_transfer_id')
                ->leftJoin('products','stock_transfer_details.product_id','products.id')
                ->leftJoin('product_units','stock_transfer_details.product_unit_id','product_units.id')
                ->leftJoin('product_brands','stock_transfer_details.product_brand_id','product_brands.id')
                ->where('stock_transfers.id',$request->stock_transfer_id)
                ->select(
                    'products.id as product_id',
                    'products.name as product_name',
                    'product_units.id as product_unit_id',
                    'product_units.name as product_unit_name',
                    'product_brands.id as product_brand_id',
                    'product_brands.name as product_brand_name',
                    'stock_transfers.warehouse_id',
                    'stock_transfer_details.qty',
                    'stock_transfer_details.id as stock_transfer_detail_id',
                    'stock_transfer_details.price',
                    'stock_transfer_details.sub_total',
                    'stock_transfer_details.vat_amount'
                )
                ->get();

            $stock_transfer_arr = [];
            if(count($stock_transfer_details))
            {
                foreach($stock_transfer_details as $stock_transfer_detail){
                    $current_qty = warehouseProductCurrentStock($stock_transfer_detail->warehouse_id,$stock_transfer_detail->product_id);

                    $nested_data['product_id']=$stock_transfer_detail->product_id;
                    $nested_data['product_name']=$stock_transfer_detail->product_name;
                    $nested_data['product_unit_id']=$stock_transfer_detail->product_unit_id;
                    $nested_data['product_unit_name']=$stock_transfer_detail->product_unit_name;
                    $nested_data['product_brand_id']=$stock_transfer_detail->product_brand_id;
                    $nested_data['product_brand_name']=$stock_transfer_detail->product_brand_name;
                    $nested_data['qty']=$stock_transfer_detail->qty;
                    $nested_data['stock_transfer_detail_id']=$stock_transfer_detail->stock_transfer_detail_id;
                    $nested_data['price']=$stock_transfer_detail->price;
                    $nested_data['sub_total']=$stock_transfer_detail->sub_total;
                    $nested_data['vat_amount']=$stock_transfer_detail->vat_amount;
                    $nested_data['current_qty']=$current_qty;

                    array_push($stock_transfer_arr, $nested_data);
                }
            }

            $response = APIHelpers::createAPIResponse(false,200,'',$stock_transfer_arr);
            return response()->json($response,200);
        } catch (\Exception $e) {
            //return $e->getMessage();
            $response = APIHelpers::createAPIResponse(false,500,'Internal Server Error.',null);
            return response()->json($response,500);
        }
    }

    // store stock return create
    public function storeToWarehouseStockReturnCreate(Request $request){
        try {
            $validator = Validator::make($request->all(), [
                'return_from_store_id'=> 'required',
                'return_to_warehouse_id'=> 'required',
            ]);

            if ($validator->fails()) {
                $response = APIHelpers::createAPIResponse(true,400,$validator->errors(),null);
                return response()->json($response,400);
            }

            $date = date('Y-m-d');
            $date_time = date('Y-m-d h:i:s');

            $user_id = Auth::user()->id;
            $return_to_warehouse_id = $request->return_to_warehouse_id;
            $return_from_store_id = $request->return_from_store_id;


            $get_invoice_no = StoreStockReturn::latest()->pluck('invoice_no')->first();
            if(!empty($get_invoice_no)){
                $get_invoice = str_replace("SSRN-","",$get_invoice_no);
                $invoice_no = $get_invoice+1;
            }else{
                $invoice_no = 4000;
            }

            $final_invoice = 'SSRN-'.$invoice_no;
            $store_stock_return = new StoreStockReturn();
            $store_stock_return->invoice_no=$final_invoice;
            $store_stock_return->return_by_user_id=$user_id;
            $store_stock_return->return_from_store_id = $return_from_store_id;
            $store_stock_return->return_to_warehouse_id = $return_to_warehouse_id;
            $store_stock_return->return_remarks=$request->return_remarks;
            $store_stock_return->return_date=$date;
            $store_stock_return->return_date_time=$date_time;
            $store_stock_return->return_status='Pending';
            $store_stock_return->save();
            $store_stock_return_insert_id = $store_stock_return->id;


            foreach ($request->products as $data) {

                $product_id = $data['product_id'];
                $product_info = Product::where('id',$product_id)->first();

                $store_stock_return_detail = new StoreStockReturnDetail();
                $store_stock_return_detail->store_stock_return_id = $store_stock_return_insert_id;
                $store_stock_return_detail->product_unit_id = $data['product_unit_id'];
                $store_stock_return_detail->product_brand_id = $data['product_brand_id'] ? $data['product_brand_id'] : NULL;
                $store_stock_return_detail->product_id = $product_id;
                $store_stock_return_detail->barcode = $product_info->barcode;
                $store_stock_return_detail->qty = $data['qty'];
                $store_stock_return_detail->price = $product_info->purchase_price;
                $store_stock_return_detail->vat_amount = $data['qty']*$product_info->whole_sale_price;
                $store_stock_return_detail->sub_total = ($data['qty']*$product_info->whole_sale_price) + ($data['qty']*$product_info->purchase_price);
                $store_stock_return_detail->save();

                $warehouse_id = $request->return_to_warehouse_id;
                $store_id = $request->return_from_store_id;

                $check_previous_warehouse_current_stock = Stock::where('warehouse_id',$warehouse_id)
                    ->where('product_id',$product_id)
                    ->where('stock_where','warehouse')
                    ->latest('id','desc')
                    ->pluck('current_stock')
                    ->first();

                if($check_previous_warehouse_current_stock){
                    $previous_warehouse_current_stock = $check_previous_warehouse_current_stock;
                }else{
                    $previous_warehouse_current_stock = 0;
                }

                // stock in warehouse product
                $stock = new Stock();
                $stock->ref_id = $store_stock_return_insert_id;
                $stock->user_id = $user_id;
                $stock->warehouse_id = $warehouse_id;
                $stock->store_id = NULL;
                $stock->product_id = $product_id;
                $stock->product_unit_id = $data['product_unit_id'];
                $stock->product_brand_id = $data['product_brand_id'] ? $data['product_brand_id'] : NULL;
                $stock->stock_type = 'from_warehouse_to_store';
                $stock->stock_where = 'warehouse';
                $stock->stock_in_out = 'stock_in';
                $stock->previous_stock = $previous_warehouse_current_stock;
                $stock->stock_in = $data['qty'];
                $stock->stock_out = 0;
                $stock->current_stock = $previous_warehouse_current_stock + $data['qty'];
                $stock->stock_date = $date;
                $stock->stock_date_time = $date_time;
                $stock->save();


                $check_previous_store_current_stock = Stock::where('warehouse_id',$warehouse_id)
                    ->where('store_id',$store_id)
                    ->where('product_id',$product_id)
                    ->where('stock_where','store')
                    ->latest('id','desc')
                    ->pluck('current_stock')
                    ->first();

                if($check_previous_store_current_stock){
                    $previous_store_current_stock = $check_previous_store_current_stock;
                }else{
                    $previous_store_current_stock = 0;
                }

                // stock out store product
                $stock = new Stock();
                $stock->ref_id = $store_stock_return_insert_id;
                $stock->user_id = $user_id;
                $stock->warehouse_id = $warehouse_id;
                $stock->store_id = $store_id;
                $stock->product_id = $product_id;
                $stock->product_unit_id = $data['product_unit_id'];
                $stock->product_brand_id = $data['product_brand_id'] ? $data['product_brand_id'] : NULL;
                $stock->stock_type = 'from_warehouse_to_store';
                $stock->stock_where = 'store';
                $stock->stock_in_out = 'stock_out';
                $stock->previous_stock = $previous_store_current_stock;
                $stock->stock_in = 0;
                $stock->stock_out = $data['qty'];
                $stock->current_stock = $previous_store_current_stock - $data['qty'];
                $stock->stock_date = $date;
                $stock->stock_date_time = $date_time;
                $stock->save();
                $insert_id = $stock->id;

                // warehouse current stock
                $warehouse_current_stock_update = WarehouseCurrentStock::where('warehouse_id',$warehouse_id)
                    ->where('product_id',$product_id)
                    ->first();
                $exists_current_stock = $warehouse_current_stock_update->current_stock;
                $final_warehouse_current_stock = $exists_current_stock + $data['qty'];
                $warehouse_current_stock_update->current_stock=$final_warehouse_current_stock;
                $warehouse_current_stock_update->save();

                // warehouse store current stock
                $check_exists_warehouse_store_current_stock = WarehouseStoreCurrentStock::where('warehouse_id',$warehouse_id)
                    ->where('store_id',$store_id)
                    ->where('product_id',$product_id)
                    ->first();

                if($check_exists_warehouse_store_current_stock){
                    $exists_current_stock = $check_exists_warehouse_store_current_stock->current_stock;
                    $final_warehouse_current_stock = $exists_current_stock - $data['qty'];
                    $check_exists_warehouse_store_current_stock->current_stock=$final_warehouse_current_stock;
                    $check_exists_warehouse_store_current_stock->save();
                }else{
                    $warehouse_store_current_stock = new WarehouseStoreCurrentStock();
                    $warehouse_store_current_stock->warehouse_id=$warehouse_id;
                    $warehouse_store_current_stock->store_id=$store_id;
                    $warehouse_store_current_stock->product_id=$product_id;
                    $warehouse_store_current_stock->current_stock=$data['qty'];
                    $warehouse_store_current_stock->save();
                }

            }

            if($store_stock_return_insert_id){
                $response = APIHelpers::createAPIResponse(false,201,'Stock Transfer Created Added Successfully.',null);
                return response()->json($response,201);
            }else{
                $response = APIHelpers::createAPIResponse(true,400,'Stock Transfer Created Updated Failed.',null);
                return response()->json($response,400);
            }
        } catch (\Exception $e) {
            //return $e->getMessage();
            $response = APIHelpers::createAPIResponse(false,500,'Internal Server Error.',null);
            return response()->json($response,500);
        }
    }

    public function storeToWarehouseStockReturnEdit(Request $request){
        try {
            // required and unique
            $validator = Validator::make($request->all(), [
                'store_stock_return_id'=> 'required',
                'return_from_store_id'=> 'required',
                'return_to_warehouse_id'=> 'required',
            ]);

            if ($validator->fails()) {
                $response = APIHelpers::createAPIResponse(true,400,$validator->errors(),null);
                return response()->json($response,400);
            }

            $user_id = Auth::user()->id;
            $date = date('Y-m-d');
            $date_time = date('Y-m-d h:i:s');
            $return_from_store_id = $request->return_from_store_id;
            $return_to_warehouse_id = $request->return_to_warehouse_id;

            $store_stock_return = StoreStockReturn::find($request->store_stock_return_id);
            $store_stock_return->return_from_store_id = $return_from_store_id;
            $store_stock_return->return_to_warehouse_id = $return_to_warehouse_id;
            $store_stock_return->return_by_user_id=$user_id;
            $store_stock_return->return_remarks=$request->return_remarks;
            $affectedRow = $store_stock_return->save();


            foreach ($request->products as $data) {

                $product_id = $data['product_id'];
                $product_info = Product::where('id',$product_id)->first();

                $store_stock_return_detail_id = $data['store_stock_return_detail_id'];
                $store_stock_return_detail = StoreStockReturnDetail::find($store_stock_return_detail_id);
                $previous_store_stock_return_qty = $store_stock_return_detail->qty;
                $store_stock_return_detail->product_unit_id = $data['product_unit_id'];
                $store_stock_return_detail->product_brand_id = $data['product_brand_id'] ? $data['product_brand_id'] : NULL;
                $store_stock_return_detail->product_id = $product_id;
                $store_stock_return_detail->barcode = $product_info->barcode;
                $store_stock_return_detail->qty = $data['qty'];
                $store_stock_return_detail->price = $product_info->purchase_price;
                $store_stock_return_detail->vat_amount = $data['qty']*$product_info->whole_sale_price;
                $store_stock_return_detail->sub_total = ($data['qty']*$product_info->whole_sale_price) + ($data['qty']*$product_info->purchase_price);
                $store_stock_return_detail->save();




                $warehouse_id = $request->return_to_warehouse_id;
                $store_id = $request->return_from_store_id;

                // product stock
                $stock_row = Stock::where('warehouse_id',$warehouse_id)->where('product_id',$product_id)->latest()->first();
                $current_stock = $stock_row->current_stock;

                // warehouse current stock
                $warehouse_current_stock_update = WarehouseCurrentStock::where('warehouse_id',$request->warehouse_id)
                    ->where('product_id',$product_id)
                    ->first();
                $exists_current_stock = $warehouse_current_stock_update->current_stock;


                // warehouse store current stock
                $warehouse_store_current_stock_update = WarehouseStoreCurrentStock::where('warehouse_id',$request->warehouse_id)
                    ->where('store_id',$store_id)
                    ->where('product_id',$product_id)
                    ->first();
                $exists_warehouse_store_current_stock = $warehouse_store_current_stock_update->current_stock;
                if($stock_row->stock_in != $data['qty']){
                    if($data['qty'] > $stock_row->stock_in){
                        $new_stock_in = $data['qty'] - $previous_store_stock_return_qty;

                        // stock in warehouse product
                        $stock = new Stock();
                        $stock->ref_id = $store_stock_return->id;
                        $stock->user_id = $user_id;
                        $stock->warehouse_id = $warehouse_id;
                        $stock->store_id = NULL;
                        $stock->product_id = $product_id;
                        $stock->product_unit_id = $data['product_unit_id'];
                        $stock->product_brand_id = $data['product_brand_id'] ? $data['product_brand_id'] : NULL;
                        $stock->stock_type = 'from_warehouse_to_store';
                        $stock->stock_where = 'warehouse';
                        $stock->stock_in_out = 'stock_in';
                        $stock->previous_stock = $current_stock;
                        $stock->stock_in = $new_stock_in;
                        $stock->stock_out=0;
                        $stock->current_stock=$current_stock + $new_stock_in;
                        $stock->stock_date = $date;
                        $stock->stock_date_time = $date_time;
                        $stock->save();

                        // warehouse current stock
                        $warehouse_current_stock_update->current_stock=$exists_current_stock + $new_stock_in;
                        $warehouse_current_stock_update->save();


                        $new_stock_out = $data['qty'] - $previous_store_stock_return_qty;
                        // stock out store product
                        $stock = new Stock();
                        $stock->ref_id = $request->stock_transfer_id;
                        $stock->user_id = $user_id;
                        $stock->warehouse_id = $warehouse_id;
                        $stock->store_id = $store_id;
                        $stock->product_id = $product_id;
                        $stock->product_unit_id = $data['product_unit_id'];
                        $stock->product_brand_id = $data['product_brand_id'] ? $data['product_brand_id'] : NULL;
                        $stock->stock_type = 'from_warehouse_to_store';
                        $stock->stock_where = 'store';
                        $stock->stock_in_out = 'stock_in';
                        $stock->previous_stock = $exists_warehouse_store_current_stock;
                        $stock->stock_in = 0;
                        $stock->stock_out = $new_stock_out;
                        $stock->current_stock = $exists_warehouse_store_current_stock - $new_stock_out;
                        $stock->stock_date = $date;
                        $stock->stock_date_time = $date_time;
                        $stock->save();

                        // warehouse store current stock
                        $warehouse_store_current_stock_update->current_stock=$exists_warehouse_store_current_stock - $new_stock_out;
                        $warehouse_store_current_stock_update->save();

                    }else{
                        $new_stock_out = $previous_store_stock_return_qty - $data['qty'];

                        // stock out warehouse product
                        $stock = new Stock();
                        $stock->ref_id = $store_stock_return->id;
                        $stock->user_id = $user_id;
                        $stock->warehouse_id = $warehouse_id;
                        $stock->store_id = NULL;
                        $stock->product_id = $product_id;
                        $stock->product_unit_id = $data['product_unit_id'];
                        $stock->product_brand_id = $data['product_brand_id'] ? $data['product_brand_id'] : NULL;
                        $stock->stock_type = 'from_warehouse_to_store';
                        $stock->stock_where = 'warehouse';
                        $stock->stock_in_out = 'stock_out';
                        $stock->previous_stock = $current_stock;
                        $stock->stock_in=0;
                        $stock->stock_out = $new_stock_out;
                        $stock->current_stock=$current_stock - $new_stock_out;
                        $stock->stock_date = $date;
                        $stock->stock_date_time = $date_time;
                        $stock->save();

                        // warehouse current stock
                        $warehouse_current_stock_update->current_stock=$exists_current_stock - $new_stock_out;
                        $warehouse_current_stock_update->save();


                        $new_stock_in = $previous_store_stock_return_qty - $data['qty'];
                        // stock in store product
                        $stock = new Stock();
                        $stock->ref_id = $store_stock_return->id;
                        $stock->user_id = $user_id;
                        $stock->warehouse_id = $warehouse_id;
                        $stock->store_id = $store_id;
                        $stock->product_id = $product_id;
                        $stock->product_unit_id = $data['product_unit_id'];
                        $stock->product_brand_id = $data['product_brand_id'] ? $data['product_brand_id'] : NULL;
                        $stock->stock_type = 'from_warehouse_to_store';
                        $stock->stock_where = 'store';
                        $stock->stock_in_out = 'stock_in';
                        $stock->previous_stock = $exists_warehouse_store_current_stock;
                        $stock->stock_in = $new_stock_in;
                        $stock->stock_out = 0;
                        $stock->current_stock = $exists_warehouse_store_current_stock + $new_stock_out;
                        $stock->stock_date = $date;
                        $stock->stock_date_time = $date_time;
                        $stock->save();

                        // warehouse store current stock
                        $warehouse_store_current_stock_update->current_stock=$exists_warehouse_store_current_stock + $new_stock_out;
                        $warehouse_store_current_stock_update->save();
                    }
                }
            }

            if($affectedRow){
                $response = APIHelpers::createAPIResponse(false,200,'Stock Return Updated Successfully.',null);
                return response()->json($response,200);
            }else{
                $response = APIHelpers::createAPIResponse(true,400,'Stock Return Updated Failed.',null);
                return response()->json($response,400);
            }
        } catch (\Exception $e) {
            //return $e->getMessage();
            $response = APIHelpers::createAPIResponse(false,500,'Internal Server Error.',null);
            return response()->json($response,500);
        }
    }

    public function storeToWarehouseStockReturnList(){
        try {
            $stock_transfer_return_lists = DB::table('store_stock_returns')
                ->leftJoin('users','store_stock_returns.return_by_user_id','users.id')
                ->leftJoin('warehouses','store_stock_returns.return_to_warehouse_id','warehouses.id')
                ->leftJoin('stores','store_stock_returns.return_from_store_id','stores.id')
                ->select(
                    'store_stock_returns.id',
                    'store_stock_returns.invoice_no',
                    'store_stock_returns.return_date_time',
                    'store_stock_returns.return_remarks',
                    'users.name as user_name',
                    'warehouses.id as warehouse_id',
                    'warehouses.name as warehouse_name',
                    'stores.id as store_id',
                    'stores.name as store_name',
                    'stores.phone as store_phone',
                    'stores.email as store_email',
                    'stores.address as store_address'
                )
                ->get();

            if($stock_transfer_return_lists === null){
                $response = APIHelpers::createAPIResponse(true,404,'No Store Stock Return List Found.',null);
                return response()->json($response,404);
            }else{
                $response = APIHelpers::createAPIResponse(false,200,'',$stock_transfer_return_lists);
                return response()->json($response,200);
            }
        } catch (\Exception $e) {
            //return $e->getMessage();
            $response = APIHelpers::createAPIResponse(false,500,'Internal Server Error.',null);
            return response()->json($response,500);
        }
    }

    public function storeToWarehouseStockReturnDetails(Request $request){
        try {
            $store_stock_return_details = DB::table('store_stock_returns')
                ->join('store_stock_return_details','store_stock_returns.id','store_stock_return_details.store_stock_return_id')
                ->leftJoin('products','store_stock_return_details.product_id','products.id')
                ->leftJoin('product_units','store_stock_return_details.product_unit_id','product_units.id')
                ->leftJoin('product_brands','store_stock_return_details.product_brand_id','product_brands.id')
                ->where('store_stock_returns.id',$request->store_stock_return_id)
                ->select(
                    'products.id as product_id',
                    'products.name as product_name',
                    'product_units.id as product_unit_id',
                    'product_units.name as product_unit_name',
                    'product_brands.id as product_brand_id',
                    'product_brands.name as product_brand_name',
                    'store_stock_returns.return_from_store_id',
                    'store_stock_returns.return_to_warehouse_id',
                    'store_stock_return_details.qty',
                    'store_stock_return_details.id as stock_transfer_return_detail_id',
                    'store_stock_return_details.price',
                    'store_stock_return_details.sub_total',
                    'store_stock_return_details.vat_amount'
                )
                ->get();

            if(count($store_stock_return_details) === 0){
                $response = APIHelpers::createAPIResponse(true,404,'No Store Stock Return Details Found.',null);
                return response()->json($response,404);
            }else{
                $store_stock_return_arr = [];
                foreach ($store_stock_return_details as $store_stock_return_detail){
                    $current_stock = warehouseStoreProductCurrentStock($store_stock_return_detail->return_to_warehouse_id,$store_stock_return_detail->return_from_store_id,$store_stock_return_detail->product_id);

                    $nested_data['product_id']=$store_stock_return_detail->product_id;
                    $nested_data['product_name']=$store_stock_return_detail->product_name;
                    $nested_data['product_unit_id']=$store_stock_return_detail->product_unit_id;
                    $nested_data['product_unit_name']=$store_stock_return_detail->product_unit_name;
                    $nested_data['product_brand_id']=$store_stock_return_detail->product_brand_id;
                    $nested_data['product_brand_name']=$store_stock_return_detail->product_brand_name;
                    $nested_data['qty']=$store_stock_return_detail->qty;
                    $nested_data['stock_transfer_return_detail_id']=$store_stock_return_detail->stock_transfer_return_detail_id;
                    $nested_data['price']=$store_stock_return_detail->price;
                    $nested_data['sub_total']=$store_stock_return_detail->sub_total;
                    $nested_data['vat_amount']=$store_stock_return_detail->vat_amount;
                    $nested_data['current_stock']=$current_stock;

                    array_push($store_stock_return_arr,$nested_data);
                }
                $response = APIHelpers::createAPIResponse(false,200,'',$store_stock_return_arr);
                return response()->json($response,200);
            }
        } catch (\Exception $e) {
            //return $e->getMessage();
            $response = APIHelpers::createAPIResponse(false,500,'Internal Server Error.',null);
            return response()->json($response,500);
        }
    }

    function universalSearchStoreCurrentProductStock(Request $request){
        try {
            if($request->name === null || $request->barcode === null || $request->item_code === null){
                $response = APIHelpers::createAPIResponse(true,404,'No Data Found.',null);
                return response()->json($response,404);
            }
            $store_stocks = '';
            $warehouse_stocks = '';
            if($request->name){
                $store_stocks = DB::table('products')
                    ->join('warehouse_store_current_stocks','products.id','warehouse_store_current_stocks.product_id')
                    ->join('stores','warehouse_store_current_stocks.store_id','stores.id')
                    //->where('products.name','like','%'.$request->name.'%')
                    ->where('products.name',$request->name)
                    ->select('stores.name as store_name','products.name as product_name','products.selling_price as price','warehouse_store_current_stocks.current_stock')
                    ->get();

                $warehouse_stocks = DB::table('products')
                    ->join('warehouse_current_stocks','products.id','warehouse_current_stocks.product_id')
                    ->join('warehouses','warehouse_current_stocks.warehouse_id','warehouses.id')
                    //->where('products.name','like','%'.$request->name.'%')
                    ->where('products.name',$request->name)
                    ->select('warehouses.name as warehouse_name','products.name as product_name','products.selling_price as price','warehouse_current_stocks.current_stock')
                    ->get();
            }

            if($request->barcode){
                $store_stocks = DB::table('products')
                    ->join('warehouse_store_current_stocks','products.id','warehouse_store_current_stocks.product_id')
                    ->join('stores','warehouse_store_current_stocks.store_id','stores.id')
                    ->orWhere('products.barcode',$request->barcode)
                    ->select('stores.name as store_name','products.name as product_name','products.selling_price as price','warehouse_store_current_stocks.current_stock')
                    ->get();

                $warehouse_stocks = DB::table('products')
                    ->join('warehouse_current_stocks','products.id','warehouse_current_stocks.product_id')
                    ->join('warehouses','warehouse_current_stocks.warehouse_id','warehouses.id')
                    ->orWhere('products.barcode',$request->barcode)
                    ->select('warehouses.name as warehouse_name','products.name as product_name','products.selling_price as price','warehouse_current_stocks.current_stock')
                    ->get();
            }

            if($request->item_code){
                $store_stocks = DB::table('products')
                    ->join('warehouse_store_current_stocks','products.id','warehouse_store_current_stocks.product_id')
                    ->join('stores','warehouse_store_current_stocks.store_id','stores.id')
                    ->orWhere('products.item_code',$request->item_code)
                    ->select('stores.name as store_name','products.name as product_name','products.selling_price as price','warehouse_store_current_stocks.current_stock')
                    ->get();

                $warehouse_stocks = DB::table('products')
                    ->join('warehouse_current_stocks','products.id','warehouse_current_stocks.product_id')
                    ->join('warehouses','warehouse_current_stocks.warehouse_id','warehouses.id')
                    ->orWhere('products.item_code',$request->item_code)
                    ->select('warehouses.name as warehouse_name','products.name as product_name','products.selling_price as price','warehouse_current_stocks.current_stock')
                    ->get();
            }

            if($store_stocks){
                $success['store_stock_details'] =  $store_stocks;
                $success['warehouse_stock_details'] =  $warehouse_stocks;
//                return response()->json(['success'=>true,'response' => $success], $this->successStatus);

                $response = APIHelpers::createAPIResponse(false,200,'',$success);
                return response()->json($response,200);
            }
        } catch (\Exception $e) {
            //return $e->getMessage();
            $response = APIHelpers::createAPIResponse(false,500,'Internal Server Error.',null);
            return response()->json($response,500);
        }
    }

    function stock_sync(){
        $stock_data = Stock::whereIn('id', function($query) {
            $query->from('stocks')->groupBy('warehouse_id')->groupBy('store_id')->groupBy('product_id')->selectRaw('MIN(id)');
        })->get();

        $row_count = count($stock_data);
        if($row_count > 0){
            foreach ($stock_data as $key => $data){
                $warehouse_id = $data->warehouse_id;
                $store_id = $data->store_id;
                $product_id = $data->product_id;
                $this->product_store_stock_sync($warehouse_id,$store_id,$product_id);
            }
            return response()->json(['success'=>true,'response' => 'Data Successfully Updated.'], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Data Found!'], $this->failStatus);
        }
    }
}
