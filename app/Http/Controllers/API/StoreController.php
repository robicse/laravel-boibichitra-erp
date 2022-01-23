<?php

namespace App\Http\Controllers\API;

use App\Helpers\APIHelpers;
use App\Http\Controllers\Controller;
use App\Product;
use App\Stock;
use App\Store;
use App\StoreProductDamage;
use App\StoreProductDamageDetail;
use App\warehouseCurrentStock;
use App\WarehouseProductDamage;
use App\WarehouseProductDamageDetail;
use App\WarehouseStoreCurrentStock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class StoreController extends Controller
{
    public $successStatus = 200;
    public $authStatus = 401;
    public $failStatus = 402;
    public $ExistsStatus = 403;
    public $validationStatus = 404;

    public function storeList(){
        try {
            $stores = DB::table('stores')
                ->leftJoin('warehouses','stores.warehouse_id','warehouses.id')
                ->select('stores.id','stores.name as store_name','stores.phone','stores.email','stores.address','stores.status','warehouses.id as warehouse_id','warehouses.name as warehouse_name')
                ->get();
            if($stores === null){
                $response = APIHelpers::createAPIResponse(true,404,'No Store Found.',null);
                return response()->json($response,404);
            }else{
                $response = APIHelpers::createAPIResponse(false,200,'',$stores);
                return response()->json($response,200);
            }

        } catch (\Exception $e) {
            //return $e->getMessage();
            $response = APIHelpers::createAPIResponse(false,500,'Internal Server Error.',null);
            return response()->json($response,500);
        }
    }

    public function storeCreate(Request $request){

        try {
            // required and unique
            $validator = Validator::make($request->all(), [
                'warehouse_id' => 'required',
                'name' => 'required',
                'phone' => 'required|unique:stores,name',
                'status'=> 'required',
            ]);

            if ($validator->fails()) {
                $response = APIHelpers::createAPIResponse(true,409,$validator->errors(),null);
                return response()->json($response,409);
            }

            $store = new Store();
            $store->warehouse_id = $request->warehouse_id;
            $store->name = $request->name;
            $store->phone = $request->phone;
            $store->email = $request->email ? $request->email : NULL;
            $store->address = $request->address ? $request->address : NULL;
            $store->status = $request->status;
            $store->save();

            $response = APIHelpers::createAPIResponse(false,201,'Store Added Successfully.',null);
            return response()->json($response,201);

        } catch (\Exception $e) {
            //return $e->getMessage();
            $response = APIHelpers::createAPIResponse(false,500,'Internal Server Error.',null);
            return response()->json($response,500);
        }
    }

    public function storeEdit(Request $request){
        try {
            $check_exists_store = DB::table("stores")->where('id',$request->store_id)->pluck('id')->first();
            if($check_exists_store == null){
                $response = APIHelpers::createAPIResponse(true,404,'No Warehouse Found.',null);
                return response()->json($response,404);
            }

            // required and unique
            $validator = Validator::make($request->all(), [
                'warehouse_id' => 'required',
                'name' => 'unique:stores,name,'.$request->store_id,
                'phone' => 'required',
                'status'=> 'required',
            ]);

            if ($validator->fails()) {
                $response = APIHelpers::createAPIResponse(true,409,$validator->errors(),null);
                return response()->json($response,409);
            }

            $store = Store::find($request->store_id);
            $store->warehouse_id = $request->warehouse_id;
            $store->name = $request->name;
            $store->phone = $request->phone;
            $store->email = $request->email ? $request->email : NULL;
            $store->address = $request->address ? $request->address : NULL;
            $store->status = $request->status;
            $update_store = $store->save();
            if($update_store){
                $response = APIHelpers::createAPIResponse(false,200,'Store Updated Successfully.',null);
                return response()->json($response,200);
            }else{
                $response = APIHelpers::createAPIResponse(true,400,'Store Updated Failed.',null);
                return response()->json($response,400);
            }
        } catch (\Exception $e) {
            //return $e->getMessage();
            $response = APIHelpers::createAPIResponse(false,500,'Internal Server Error.',null);
            return response()->json($response,500);
        }
    }

    public function storeDelete(Request $request){
        try {
            $check_exists_store = DB::table("stores")->where('id',$request->store_id)->pluck('id')->first();
            if($check_exists_store == null){
                $response = APIHelpers::createAPIResponse(true,404,'No Store Found.',null);
                return response()->json($response,404);
            }

            //$delete_store = DB::table("stores")->where('id',$request->store_id)->delete();
            $store_soft_delete = Store::find($request->store_id);
            $store_soft_delete->status=0;
            $affected_row = $store_soft_delete->update();
            if($affected_row)
            {
                $response = APIHelpers::createAPIResponse(false,200,'Store Successfully Soft Deleted.',null);
                return response()->json($response,200);
            }else{
                $response = APIHelpers::createAPIResponse(true,400,'Store Updated Failed.',null);
                return response()->json($response,400);
            }
        } catch (\Exception $e) {
            //return $e->getMessage();
            $response = APIHelpers::createAPIResponse(false,500,'Internal Server Error.',null);
            return response()->json($response,500);
        }
    }

    // store product damage
    public function storeProductDamageList(){
        try {
            $store_product_damage_lists = DB::table('store_product_damages')
                ->leftJoin('users','store_product_damages.user_id','users.id')
                ->leftJoin('stores','store_product_damages.store_id','stores.id')
                ->select(
                    'store_product_damages.id',
                    'store_product_damages.invoice_no',
                    'users.name as user_name',
                    'stores.id as store_id',
                    'stores.name as store_name',
                    'store_product_damages.damage_date'
                )
                ->paginate(12);

            if($store_product_damage_lists === null){
                $response = APIHelpers::createAPIResponse(true,404,'No Store Product Damage Found.',null);
                return response()->json($response,404);
            }else{
                $response = APIHelpers::createAPIResponse(false,200,'',$store_product_damage_lists);
                return response()->json($response,200);
            }
        } catch (\Exception $e) {
            //return $e->getMessage();
            $response = APIHelpers::createAPIResponse(false,500,'Internal Server Error.',null);
            return response()->json($response,500);
        }
    }

    public function storeProductDamageDetails(Request $request){
        $store_product_damage_details = DB::table('store_product_damages')
            ->join('store_product_damage_details','store_product_damages.id','store_product_damage_details.store_product_damage_id')
            ->leftJoin('products','store_product_damage_details.product_id','products.id')
            ->leftJoin('product_units','store_product_damage_details.product_unit_id','product_units.id')
            ->leftJoin('product_brands','store_product_damage_details.product_brand_id','product_brands.id')
            ->where('store_product_damages.id',$request->store_product_damage_id)
            ->select(
                'products.id as product_id',
                'products.name as product_name',
                'product_units.id as product_unit_id',
                'product_units.name as product_unit_name',
                'product_brands.id as product_brand_id',
                'product_brands.name as product_brand_name',
                'store_product_damage_details.qty',
                'store_product_damage_details.id as store_product_damage_detail_id',
                'store_product_damage_details.price',
                'store_product_damage_details.sub_total',
                'store_product_damage_details.vat_amount'
            )
            ->get();

        $total_damage_amount = 0;
        if(count($store_product_damage_details) > 0){
            foreach ($store_product_damage_details as $store_product_damage_detail){
                $total_damage_amount += $store_product_damage_detail->sub_total;
            }
        }

        $store_info = DB::table('stores')
            ->join('store_product_damages','stores.id','store_product_damages.store_id')
            ->where('store_product_damages.id',$request->store_product_damage_id)
            ->select('name','phone','email','address')
            ->first();

        if($store_product_damage_details)
        {
            $success['store_product_damage_details'] =  $store_product_damage_details;
            $success['total_damage_amount'] =  $total_damage_amount;
            $success['store_info'] =  $store_info;
            return response()->json(['success'=>true,'response' => $success], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Store Product Damage Details Found!'], $this->failStatus);
        }
    }

    public function storeProductDamageCreate(Request $request){
        //dd($request->all());
        $this->validate($request, [
            'store_id'=> 'required',
        ]);

        $user_id = Auth::user()->id;
        $store_id = $request->store_id;
        $date = date('Y-m-d');
        $date_time = date('Y-m-d H:i:s');

        $get_invoice_no = StoreProductDamage::latest()->pluck('invoice_no')->first();
        if(!empty($get_invoice_no)){
            $get_invoice = str_replace("SPDN-","",$get_invoice_no);
            $invoice_no = $get_invoice+1;
        }else{
            $invoice_no = 8000;
        }

        $final_invoice = 'SPDN-'.$invoice_no;

        $store_product_damage = new StoreProductDamage();
        $store_product_damage->invoice_no = $final_invoice;
        $store_product_damage->user_id = $user_id;
        $store_product_damage->store_id = $store_id;
        $store_product_damage->damage_date = $date;
        $store_product_damage->damage_date_time = $date_time;
        $store_product_damage->save();
        $insert_id = $store_product_damage->id;

        if($insert_id){
            foreach ($request->products as $data) {
                $product_id = $data['product_id'];
                $barcode = Product::where('id',$product_id)->pluck('barcode')->first();

                // warehouse damage product
                $store_product_damage_detail = new StoreProductDamageDetail();
                $store_product_damage_detail->store_product_damage_id  = $insert_id;
                $store_product_damage_detail->product_unit_id = $data['product_unit_id'];
                $store_product_damage_detail->product_brand_id = $data['product_brand_id'] ? $data['product_brand_id'] : NULL;
                $store_product_damage_detail->product_id = $product_id;
                $store_product_damage_detail->barcode = $barcode;
                $store_product_damage_detail->qty = $data['qty'];
                $store_product_damage_detail->price = $data['price'];
                $store_product_damage_detail->vat_amount = 0;
                $store_product_damage_detail->sub_total = $data['qty']*$data['price'];
                $store_product_damage_detail->save();


                // product stock
                $stock_row = Stock::where('stock_where','store')->where('store_id',$store_id)->where('product_id',$product_id)->latest('id')->first();

                $stock = new Stock();
                $stock->ref_id=$insert_id;
                $stock->user_id=$user_id;
                $stock->product_unit_id= $data['product_unit_id'];
                $stock->product_brand_id=$data['product_brand_id'] ? $data['product_brand_id'] : NULL;
                $stock->product_id=$product_id;
                $stock->stock_type='store_product_damage';
                $stock->warehouse_id=6;
                $stock->store_id=$store_id;
                $stock->stock_where='store';
                $stock->stock_in_out='stock_out';
                $stock->previous_stock=$stock_row->current_stock;
                $stock->stock_in=0;
                $stock->stock_out=$data['qty'];
                $stock->current_stock=$stock_row->current_stock - $data['qty'];
                $stock->stock_date=$date;
                $stock->stock_date_time=$date_time;
                $stock->save();


                $warehouse_store_current_stock = WarehouseStoreCurrentStock::where('store_id',$store_id)->where('product_id',$product_id)->first();
                $exists_current_stock = $warehouse_store_current_stock->current_stock;
                $warehouse_store_current_stock->current_stock=$exists_current_stock - $data['qty'];
                $warehouse_store_current_stock->update();
            }
        }

        if($insert_id){
            return response()->json(['success'=>true,'response' => 'Updated Successfully.'], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Updated Successfully!'], $this->failStatus);
        }
    }

    public function storeProductDamageEdit(Request $request){
        //dd($request->all());
        $this->validate($request, [
            'store_product_damage_id'=> 'required',
            'store_id'=> 'required',
        ]);

        $user_id = Auth::user()->id;
        $store_id = $request->store_id;
        $date = date('Y-m-d');
        $date_time = date('Y-m-d H:i:s');

        $store_product_damage = StoreProductDamage::find($request->store_product_damage_id);
        $store_product_damage->user_id = $user_id;
        $store_product_damage->store_id = $store_id;
        $affectedRow = $store_product_damage->save();


        if($affectedRow){
            foreach ($request->products as $data) {
                $product_id = $data['product_id'];
                $barcode = Product::where('id',$product_id)->pluck('barcode')->first();

                $store_product_damage_detail_id = $data['store_product_damage_detail_id'];
                // warehouse damage product
                $store_product_damage = StoreProductDamageDetail::find($store_product_damage_detail_id);
                $previous_store_product_damage_qty = $store_product_damage->qty;
                $store_product_damage->product_unit_id = $data['product_unit_id'];
                $store_product_damage->product_brand_id = $data['product_brand_id'] ? $data['product_brand_id'] : NULL;
                $store_product_damage->product_id = $product_id;
                $store_product_damage->barcode = $barcode;
                $store_product_damage->qty = $data['qty'];
                $store_product_damage->price = $data['price'];
                $store_product_damage->vat_amount = 0;
                $store_product_damage->sub_total = $data['qty']*$data['price'];
                $affectedRow = $store_product_damage->update();

                if($affectedRow){
                    // product stock
                    $stock_row = Stock::where('stock_where','store')->where('store_id',$store_id)->where('product_id',$product_id)->latest('id')->first();
                    $current_stock = $stock_row->current_stock;

                    $warehouse_store_current_stock = WarehouseStoreCurrentStock::where('store_id',$store_id)->where('product_id',$product_id)->first();
                    $exists_current_stock = $warehouse_store_current_stock->current_stock;

                    if($stock_row->stock_in != $data['qty']){

                        if($data['qty'] > $stock_row->stock_in){
                            $new_stock_out = $data['qty'] - $previous_store_product_damage_qty;

                            $stock = new Stock();
                            $stock->ref_id=$request->store_product_damage_id;
                            $stock->user_id=$user_id;
                            $stock->product_unit_id= $data['product_unit_id'];
                            $stock->product_brand_id= $data['product_brand_id'] ? $data['product_brand_id'] : NULL;
                            $stock->product_id= $product_id;
                            $stock->stock_type='store_product_damage_increase';
                            $stock->warehouse_id= 6;
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
                            $warehouse_store_current_stock->current_stock=$exists_current_stock - $new_stock_out;
                            $warehouse_store_current_stock->save();
                        }else{
                            $new_stock_in =  $previous_store_product_damage_qty - $data['qty'];

                            $stock = new Stock();
                            $stock->ref_id=$request->store_product_damage_id;
                            $stock->user_id=$user_id;
                            $stock->product_unit_id= $data['product_unit_id'];
                            $stock->product_brand_id= $data['product_brand_id'] ? $data['product_brand_id'] : NULL;
                            $stock->product_id= $product_id;
                            $stock->stock_type='store_product_damage_decrease';
                            $stock->warehouse_id= 6;
                            $stock->store_id=$store_id;
                            $stock->stock_where='store';
                            $stock->stock_in_out='stock_out';
                            $stock->previous_stock=$current_stock;
                            $stock->stock_in=$new_stock_in;
                            $stock->stock_out=0;
                            $stock->current_stock=$current_stock + $new_stock_in;
                            $stock->stock_date=$date;
                            $stock->stock_date_time=$date_time;
                            $stock->save();

                            // warehouse current stock
                            $warehouse_store_current_stock->current_stock=$exists_current_stock + $new_stock_in;
                            $warehouse_store_current_stock->save();
                        }
                    }


                }
            }
        }

        if($affectedRow){
            return response()->json(['success'=>true,'response' => 'Updated Successfully.'], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Updated Successfully!'], $this->failStatus);
        }
    }

    public function storeProductDamageDelete(Request $request){
        $check_exists_store_product_damage = DB::table("store_product_damages")->where('id',$request->store_product_damage_id)->pluck('id')->first();
        if($check_exists_store_product_damage == null){
            return response()->json(['success'=>false,'response'=>'No Store Product Damage List Found!'], $this->failStatus);
        }

        $storeProductDamage = StoreProductDamage::find($request->store_product_damage_id);

        $storeProductDamageDetails = StoreProductDamageDetail::where('store_product_damage_id',$request->store_product_damage_id)->get();
        if(count($storeProductDamageDetails) > 0){
            foreach ($storeProductDamageDetails as $storeProductDamageDetail){
                $user_id = Auth::user()->id;
                $date = date('Y-m-d');
                $date_time = date('Y-m-d H:i:s');

                // damage stock
                $store_product_damage_id = $check_exists_store_product_damage->id;
                $qty = $storeProductDamageDetail->qty;
                $warehouse_id = $check_exists_store_product_damage->warehouse_id;
                $product_unit_id = $storeProductDamageDetail->product_unit_id;
                $product_brand_id = $storeProductDamageDetail->product_brand_id;
                $product_id = $storeProductDamageDetail->product_id;

                // current stock
                $stock_row = Stock::where('stock_where','store')->where('store_id',$store_id)->where('product_id',$product_id)->latest('id')->first();
                $current_stock = $stock_row->current_stock;

                $stock = new Stock();
                $stock->ref_id=$store_product_damage_id;
                $stock->user_id=$user_id;
                $stock->product_unit_id= $product_unit_id;
                $stock->product_brand_id= $product_brand_id;
                $stock->product_id= $product_id;
                $stock->stock_type='store_product_damage_delete';
                $stock->warehouse_id= 6;
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


                $store_current_stock = WarehouseStoreCurrentStock::where('store_id',$store_id)->where('product_id',$product_id)->first();
                $exists_current_stock = $store_current_stock->current_stock;
                $store_current_stock->current_stock=$exists_current_stock - $qty;
                $store_current_stock->update();
            }
        }


        $delete_store_product_damage = $storeProductDamage->delete();

        if($delete_store_product_damage)
        {
            return response()->json(['success'=>true,'response' =>'Store Successfully Deleted!'], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'Store Not Deleted!'], $this->failStatus);
        }
    }
}
