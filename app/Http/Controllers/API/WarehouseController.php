<?php

namespace App\Http\Controllers\API;

use App\Helpers\APIHelpers;
use App\Http\Controllers\Controller;
use App\Product;
use App\Stock;
use App\Warehouse;
use App\warehouseCurrentStock;
use App\WarehouseProductDamage;
use App\WarehouseProductDamageDetail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class WarehouseController extends Controller
{
    public $successStatus = 200;
    public $authStatus = 401;
    public $failStatus = 402;
    public $ExistsStatus = 403;
    public $validationStatus = 404;


    // product warehouse
    public function warehouseList(){
        try {
            $warehouses = DB::table('warehouses')->select('id','name','phone','email','address','status')->get();
            if($warehouses === null){
                $response = APIHelpers::createAPIResponse(true,404,'No Warehouse Found.',null);
                return response()->json($response,404);
            }else{
                $response = APIHelpers::createAPIResponse(false,200,'',$warehouses);
                return response()->json($response,200);
            }
        } catch (\Exception $e) {
            //return $e->getMessage();
            $response = APIHelpers::createAPIResponse(false,500,'Internal Server Error.',null);
            return response()->json($response,500);
        }
    }

    public function warehouseCreate(Request $request){

        try {
            // required and unique
            $validator = Validator::make($request->all(), [
                'name' => 'unique:warehouses,name',
                'phone' => 'required',
                'status'=> 'required',
            ]);

            if ($validator->fails()) {
                $response = APIHelpers::createAPIResponse(true,400,$validator->errors(),null);
                return response()->json($response,400);
            }

            $warehouse = new Warehouse();
            $warehouse->name = $request->name;
            $warehouse->phone = $request->phone;
            $warehouse->email = $request->email;
            $warehouse->address = $request->address;
            $warehouse->status = $request->status;
            $warehouse->save();

            $response = APIHelpers::createAPIResponse(false,201,'Warehouse Added Successfully.',null);
            return response()->json($response,201);
        } catch (\Exception $e) {
            //return $e->getMessage();
            $response = APIHelpers::createAPIResponse(false,500,'Internal Server Error.',null);
            return response()->json($response,500);
        }
    }

    public function warehouseEdit(Request $request){
        try {
            $check_exists_warehouse = DB::table("warehouses")->where('id',$request->warehouse_id)->pluck('id')->first();
            if($check_exists_warehouse == null){
                $response = APIHelpers::createAPIResponse(true,404,'No Warehouse Found.',null);
                return response()->json($response,404);
            }

            // required and unique
            $validator = Validator::make($request->all(), [
                'warehouse_id' => 'required',
                'name' => 'unique:warehouses,name,'.$request->warehouse_id,
                'phone' => 'required',
                'status'=> 'required',
            ]);

            if ($validator->fails()) {
                $response = APIHelpers::createAPIResponse(true,400,$validator->errors(),null);
                return response()->json($response,400);
            }

            $warehouse = Warehouse::find($request->warehouse_id);
            $warehouse->name = $request->name;
            $warehouse->phone = $request->phone;
            $warehouse->email = $request->email;
            $warehouse->address = $request->address;
            $warehouse->status = $request->status;
            $update_warehouse = $warehouse->save();

            if($update_warehouse){
                $response = APIHelpers::createAPIResponse(false,200,'Warehouse Updated Successfully.',null);
                return response()->json($response,200);
            }else{
                $response = APIHelpers::createAPIResponse(true,400,'Warehouse Updated Failed.',null);
                return response()->json($response,400);
            }
        } catch (\Exception $e) {
            //return $e->getMessage();
            $response = APIHelpers::createAPIResponse(false,500,'Internal Server Error.',null);
            return response()->json($response,500);
        }
    }

    public function warehouseDelete(Request $request){
        try {
            $check_exists_warehouse = DB::table("warehouses")->where('id',$request->warehouse_id)->pluck('id')->first();
            if($check_exists_warehouse == null){
                $response = APIHelpers::createAPIResponse(true,404,'No Warehouse Found.',null);
                return response()->json($response,404);
            }

            //$delete_warehouse = DB::table("warehouses")->where('id',$request->warehouse_id)->delete();
            $soft_delete_warehouse = Warehouse::find($request->warehouse_id);
            $soft_delete_warehouse->status=0;
            $affected_row = $soft_delete_warehouse->update();
            if($affected_row)
            {
                $response = APIHelpers::createAPIResponse(false,200,'Warehouse Successfully Soft Deleted.',null);
                return response()->json($response,200);
            }else{
                $response = APIHelpers::createAPIResponse(true,400,'Warehouse Updated Failed.',null);
                return response()->json($response,400);
            }
        } catch (\Exception $e) {
            //return $e->getMessage();
            $response = APIHelpers::createAPIResponse(false,500,'Internal Server Error.',null);
            return response()->json($response,500);
        }
    }

    // warehouse product damage
    public function warehouseProductDamageList(){
        try {
            $warehouse_product_damage_lists = DB::table('warehouse_product_damages')
                ->leftJoin('users','warehouse_product_damages.user_id','users.id')
                ->leftJoin('warehouses','warehouse_product_damages.warehouse_id','warehouses.id')
                ->select(
                    'warehouse_product_damages.id',
                    'warehouse_product_damages.invoice_no',
                    'users.name as user_name',
                    'warehouses.id as warehouse_id',
                    'warehouses.name as warehouse_name',
                    'warehouse_product_damages.damage_date'
                )
                ->paginate(12);

            if($warehouse_product_damage_lists === null){
                $response = APIHelpers::createAPIResponse(true,404,'No Warehouse Product Damage Found.',null);
                return response()->json($response,404);
            }else{
                $response = APIHelpers::createAPIResponse(false,200,'',$warehouse_product_damage_lists);
                return response()->json($response,200);
            }
        } catch (\Exception $e) {
            //return $e->getMessage();
            $response = APIHelpers::createAPIResponse(false,500,'Internal Server Error.',null);
            return response()->json($response,500);
        }
    }

    public function warehouseProductDamageDetails(Request $request){
        try {
            $warehouse_product_damage_details = DB::table('warehouse_product_damages')
                ->join('warehouse_product_damage_details','warehouse_product_damages.id','warehouse_product_damage_details.warehouse_product_damage_id')
                ->leftJoin('products','warehouse_product_damage_details.product_id','products.id')
                ->leftJoin('product_units','warehouse_product_damage_details.product_unit_id','product_units.id')
                ->leftJoin('product_brands','warehouse_product_damage_details.product_brand_id','product_brands.id')
                ->where('warehouse_product_damages.id',$request->warehouse_product_damage_id)
                ->select(
                    'products.id as product_id',
                    'products.name as product_name',
                    'product_units.id as product_unit_id',
                    'product_units.name as product_unit_name',
                    'product_brands.id as product_brand_id',
                    'product_brands.name as product_brand_name',
                    'warehouse_product_damages.warehouse_id',
                    'warehouse_product_damage_details.qty',
                    'warehouse_product_damage_details.id as warehouse_product_damage_detail_id',
                    'warehouse_product_damage_details.price',
                    'warehouse_product_damage_details.sub_total',
                    'warehouse_product_damage_details.vat_amount'
                )
                ->get();

            $total_damage_amount = 0;
            if(count($warehouse_product_damage_details) > 0){
                foreach ($warehouse_product_damage_details as $warehouse_product_damage_detail){
                    $total_damage_amount += $warehouse_product_damage_detail->sub_total;
                }
            }

            if($warehouse_product_damage_details)
            {
                $store_stock_return_arr = [];
                foreach ($warehouse_product_damage_details as $warehouse_product_damage_detail){
                    $current_stock = warehouseProductCurrentStock($warehouse_product_damage_detail->warehouse_id,$warehouse_product_damage_detail->product_id);

                    $nested_data['product_id']=$warehouse_product_damage_detail->product_id;
                    $nested_data['product_name']=$warehouse_product_damage_detail->product_name;
                    $nested_data['product_unit_id']=$warehouse_product_damage_detail->product_unit_id;
                    $nested_data['product_unit_name']=$warehouse_product_damage_detail->product_unit_name;
                    $nested_data['product_brand_id']=$warehouse_product_damage_detail->product_brand_id;
                    $nested_data['product_brand_name']=$warehouse_product_damage_detail->product_brand_name;
                    $nested_data['qty']=$warehouse_product_damage_detail->qty;
                    $nested_data['warehouse_product_damage_detail_id']=$warehouse_product_damage_detail->warehouse_product_damage_detail_id;
                    $nested_data['price']=$warehouse_product_damage_detail->price;
                    $nested_data['sub_total']=$warehouse_product_damage_detail->sub_total;
                    $nested_data['vat_amount']=$warehouse_product_damage_detail->vat_amount;
                    $nested_data['current_stock']=$current_stock;

                    array_push($store_stock_return_arr,$nested_data);
                }

                $warehouse_info = DB::table('warehouses')
                    ->join('warehouse_product_damages','warehouses.id','warehouse_product_damages.warehouse_id')
                    ->where('warehouse_product_damages.id',$request->warehouse_product_damage_id)
                    ->select('name','phone','email','address')
                    ->first();

                $success['warehouse_product_damage_details'] =  $store_stock_return_arr;
                $success['total_damage_amount'] =  $total_damage_amount;
                $success['warehouse_info'] =  $warehouse_info;
                //return response()->json(['success'=>true,'response' => $success], $this->successStatus);

                $response = APIHelpers::createAPIResponse(false,200,'',$success);
                return response()->json($response,200);
            }else{
                $response = APIHelpers::createAPIResponse(true,400,'No Warehouse Product Damage Details Found.',null);
                return response()->json($response,400);
            }
        } catch (\Exception $e) {
            //return $e->getMessage();
            $response = APIHelpers::createAPIResponse(false,500,'Internal Server Error.',null);
            return response()->json($response,500);
        }
    }

    public function warehouseProductDamageCreate(Request $request){
        //dd($request->all());
        $this->validate($request, [
            'warehouse_id'=> 'required',
        ]);

        $user_id = Auth::user()->id;
        $warehouse_id = $request->warehouse_id;
        $date = date('Y-m-d');
        $date_time = date('Y-m-d H:i:s');

        $get_invoice_no = WarehouseProductDamage::latest()->pluck('invoice_no')->first();
        if(!empty($get_invoice_no)){
            $get_invoice = str_replace("WPDN-","",$get_invoice_no);
            $invoice_no = $get_invoice+1;
        }else{
            $invoice_no = 6000;
        }

        $final_invoice = 'WPDN-'.$invoice_no;

        $warehouse_product_damage = new WarehouseProductDamage();
        $warehouse_product_damage->invoice_no = $final_invoice;
        $warehouse_product_damage->user_id = $user_id;
        $warehouse_product_damage->warehouse_id = $warehouse_id;
        $warehouse_product_damage->damage_date = $date;
        $warehouse_product_damage->damage_date_time = $date_time;
        $warehouse_product_damage->save();
        $insert_id = $warehouse_product_damage->id;

        if($insert_id){
            foreach ($request->products as $data) {
                $product_id = $data['product_id'];
                $barcode = Product::where('id',$product_id)->pluck('barcode')->first();

                // warehouse damage product
                $warehouse_product_damage_detail = new WarehouseProductDamageDetail();
                $warehouse_product_damage_detail->warehouse_product_damage_id  = $insert_id;
                $warehouse_product_damage_detail->product_unit_id = $data['product_unit_id'];
                $warehouse_product_damage_detail->product_brand_id = $data['product_brand_id'] ? $data['product_brand_id'] : NULL;
                $warehouse_product_damage_detail->product_id = $product_id;
                $warehouse_product_damage_detail->barcode = $barcode;
                $warehouse_product_damage_detail->qty = $data['qty'];
                $warehouse_product_damage_detail->price = $data['price'];
                $warehouse_product_damage_detail->vat_amount = 0;
                $warehouse_product_damage_detail->sub_total = $data['qty']*$data['price'];
                $warehouse_product_damage_detail->save();


                // product stock
                $stock_row = Stock::where('stock_where','warehouse')->where('warehouse_id',$warehouse_id)->where('product_id',$product_id)->latest('id')->first();

                $stock = new Stock();
                $stock->ref_id=$insert_id;
                $stock->user_id=$user_id;
                $stock->product_unit_id= $data['product_unit_id'];
                $stock->product_brand_id=$data['product_brand_id'] ? $data['product_brand_id'] : NULL;
                $stock->product_id=$product_id;
                $stock->stock_type='warehouse_product_damage';
                $stock->warehouse_id=$warehouse_id;
                $stock->store_id=NULL;
                $stock->stock_where='warehouse';
                $stock->stock_in_out='stock_out';
                $stock->previous_stock=$stock_row->current_stock;
                $stock->stock_in=0;
                $stock->stock_out=$data['qty'];
                $stock->current_stock=$stock_row->current_stock - $data['qty'];
                $stock->stock_date=$date;
                $stock->stock_date_time=$date_time;
                $stock->save();


                $warehouse_current_stock = WarehouseCurrentStock::where('warehouse_id',$warehouse_id)->where('product_id',$product_id)->first();
                $exists_current_stock = $warehouse_current_stock->current_stock;
                $warehouse_current_stock->current_stock=$exists_current_stock - $data['qty'];
                $warehouse_current_stock->update();
            }
        }

        if($insert_id){
            return response()->json(['success'=>true,'response' => 'Updated Successfully.'], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Updated Successfully!'], $this->failStatus);
        }
    }

    public function warehouseProductDamageEdit(Request $request){
        //dd($request->all());
        $this->validate($request, [
            'warehouse_product_damage_id'=> 'required',
            'warehouse_id'=> 'required',
        ]);

        $user_id = Auth::user()->id;
        $warehouse_id = $request->warehouse_id;
        $date = date('Y-m-d');
        $date_time = date('Y-m-d H:i:s');

        $warehouse_product_damage = WarehouseProductDamage::find($request->warehouse_product_damage_id);
        $warehouse_product_damage->user_id = $user_id;
        $warehouse_product_damage->warehouse_id = $warehouse_id;
        $affectedRow = $warehouse_product_damage->save();


        if($affectedRow){
            foreach ($request->products as $data) {
                $product_id = $data['product_id'];
                $barcode = Product::where('id',$product_id)->pluck('barcode')->first();

                $warehouse_product_damage_detail_id = $data['warehouse_product_damage_detail_id'];
                // warehouse damage product
                $warehouse_product_damage = WarehouseProductDamageDetail::find($warehouse_product_damage_detail_id);
                $previous_warehouse_product_damage_qty = $warehouse_product_damage->qty;
                $warehouse_product_damage->product_unit_id = $data['product_unit_id'];
                $warehouse_product_damage->product_brand_id = $data['product_brand_id'] ? $data['product_brand_id'] : NULL;
                $warehouse_product_damage->product_id = $product_id;
                $warehouse_product_damage->barcode = $barcode;
                $warehouse_product_damage->qty = $data['qty'];
                $warehouse_product_damage->price = $data['price'];
                $warehouse_product_damage->vat_amount = 0;
                $warehouse_product_damage->sub_total = $data['qty']*$data['price'];
                $affectedRow = $warehouse_product_damage->update();

                if($affectedRow){
                    // product stock
                    $stock_row = Stock::where('stock_where','warehouse')->where('warehouse_id',$warehouse_id)->where('product_id',$product_id)->latest('id')->first();
                    $current_stock = $stock_row->current_stock;

                    $warehouse_current_stock = WarehouseCurrentStock::where('warehouse_id',$warehouse_id)->where('product_id',$product_id)->first();
                    $exists_current_stock = $warehouse_current_stock->current_stock;

                    if($stock_row->stock_in != $data['qty']){

                        if($data['qty'] > $stock_row->stock_in){
                            $new_stock_out = $data['qty'] - $previous_warehouse_product_damage_qty;

                            $stock = new Stock();
                            $stock->ref_id=$request->warehouse_product_damage_id;
                            $stock->user_id=$user_id;
                            $stock->product_unit_id= $data['product_unit_id'];
                            $stock->product_brand_id= $data['product_brand_id'] ? $data['product_brand_id'] : NULL;
                            $stock->product_id= $product_id;
                            $stock->stock_type='warehouse_product_damage_increase';
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
                            $warehouse_current_stock->current_stock=$exists_current_stock - $new_stock_out;
                            $warehouse_current_stock->save();
                        }else{
                            $new_stock_in =  $previous_warehouse_product_damage_qty - $data['qty'];

                            $stock = new Stock();
                            $stock->ref_id=$request->warehouse_product_damage_id;
                            $stock->user_id=$user_id;
                            $stock->product_unit_id= $data['product_unit_id'];
                            $stock->product_brand_id= $data['product_brand_id'] ? $data['product_brand_id'] : NULL;
                            $stock->product_id= $product_id;
                            $stock->stock_type='warehouse_product_damage_decrease';
                            $stock->warehouse_id= $warehouse_id;
                            $stock->store_id=NULL;
                            $stock->stock_where='warehouse';
                            $stock->stock_in_out='stock_out';
                            $stock->previous_stock=$current_stock;
                            $stock->stock_in=$new_stock_in;
                            $stock->stock_out=0;
                            $stock->current_stock=$current_stock + $new_stock_in;
                            $stock->stock_date=$date;
                            $stock->stock_date_time=$date_time;
                            $stock->save();

                            // warehouse current stock
                            $warehouse_current_stock->current_stock=$exists_current_stock + $new_stock_in;
                            $warehouse_current_stock->save();
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

    public function warehouseProductDamageDelete(Request $request){
        $check_exists_warehouse_product_damage = DB::table("warehouse_product_damages")->where('id',$request->warehouse_product_damage_id)->pluck('id')->first();
        if($check_exists_warehouse_product_damage == null){
            return response()->json(['success'=>false,'response'=>'No Warehouse Product Damage List Found!'], $this->failStatus);
        }

        $warehouseProductDamage = WarehouseProductDamage::find($request->warehouse_product_damage_id);

        $warehouseProductDamageDetails = WarehouseProductDamageDetail::where('warehouse_product_damage_id',$request->warehouse_product_damage_id)->get();
        if(count($warehouseProductDamageDetails) > 0){
            foreach ($warehouseProductDamageDetails as $warehouseProductDamageDetail){
                $user_id = Auth::user()->id;
                $date = date('Y-m-d');
                $date_time = date('Y-m-d H:i:s');

                // damage stock
                $warehouse_product_damage_id = $check_exists_warehouse_product_damage->id;
                $qty = $warehouseProductDamageDetail->qty;
                $warehouse_id = $check_exists_warehouse_product_damage->warehouse_id;
                $product_unit_id = $warehouseProductDamageDetail->product_unit_id;
                $product_brand_id = $warehouseProductDamageDetail->product_brand_id;
                $product_id = $warehouseProductDamageDetail->product_id;

                // current stock
                $stock_row = Stock::where('stock_where','warehouse')->where('warehouse_id',$warehouse_id)->where('product_id',$product_id)->latest('id')->first();
                $current_stock = $stock_row->current_stock;

                $stock = new Stock();
                $stock->ref_id=$warehouse_product_damage_id;
                $stock->user_id=$user_id;
                $stock->product_unit_id= $product_unit_id;
                $stock->product_brand_id= $product_brand_id;
                $stock->product_id= $product_id;
                $stock->stock_type='warehouse_product_damage_delete';
                $stock->warehouse_id= $warehouse_id;
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


                $warehouse_current_stock = WarehouseCurrentStock::where('warehouse_id',$warehouse_id)->where('product_id',$product_id)->first();
                $exists_current_stock = $warehouse_current_stock->current_stock;
                $warehouse_current_stock->current_stock=$exists_current_stock - $qty;
                $warehouse_current_stock->update();
            }
        }


        $delete_warehouse_product_damage = $warehouseProductDamage->delete();

        if($delete_warehouse_product_damage)
        {
            return response()->json(['success'=>true,'response' =>'Sale Successfully Deleted!'], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'Sale Not Deleted!'], $this->failStatus);
        }
    }

}
