<?php

namespace App\Http\Controllers\API;

use App\Helpers\APIHelpers;
use App\Http\Controllers\Controller;
use App\ProductUnit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ProductUnitController extends Controller
{
    // product unit
    public function productUnitList(){
        try {
            $product_units = DB::table('product_units')->select('id','name','status')->get();
            if($product_units === null){
                $response = APIHelpers::createAPIResponse(true,404,'No Product Unit Found.',null);
                return response()->json($response,404);
            }else{
                $response = APIHelpers::createAPIResponse(false,200,'',$product_units);
                return response()->json($response,200);
            }
        } catch (\Exception $e) {
            //return $e->getMessage();
            $response = APIHelpers::createAPIResponse(false,500,'Internal Server Error.',null);
            return response()->json($response,500);
        }
    }

    public function productUnitCreate(Request $request){
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|unique:product_units,name',
                'status'=> 'required',
            ]);

            if ($validator->fails()) {
                $response = APIHelpers::createAPIResponse(true,400,$validator->errors(),null);
                return response()->json($response,400);
            }


            $productUnit = new ProductUnit();
            $productUnit->name = $request->name;
            $productUnit->status = $request->status;
            $productUnit->save();

            $response = APIHelpers::createAPIResponse(false,201,'Warehouse Added Successfully.',null);
            return response()->json($response,201);
        } catch (\Exception $e) {
            //return $e->getMessage();
            $response = APIHelpers::createAPIResponse(false,500,'Internal Server Error.',null);
            return response()->json($response,500);
        }
    }

    public function productUnitEdit(Request $request){
        try {
            $validator = Validator::make($request->all(), [
                'product_unit_id'=> 'required',
                'name' => 'required|unique:product_units,name,'.$request->product_unit_id,
                'status'=> 'required',
            ]);

            if ($validator->fails()) {
                $response = APIHelpers::createAPIResponse(true,400,$validator->errors(),null);
                return response()->json($response,400);
            }

            $check_exists_product_unit = DB::table("product_units")->where('id',$request->product_unit_id)->pluck('id')->first();
            if($check_exists_product_unit == null){
                $response = APIHelpers::createAPIResponse(true,404,'No Product Unit Found.',null);
                return response()->json($response,404);
            }

            $product_units = ProductUnit::find($request->product_unit_id);
            $product_units->name = $request->name;
            $product_units->status = $request->status;
            $update_product_unit = $product_units->save();

            if($update_product_unit){
                $response = APIHelpers::createAPIResponse(false,200,'Product Unit Updated Successfully.',null);
                return response()->json($response,200);
            }else{
                $response = APIHelpers::createAPIResponse(true,400,'Product Unit Updated Failed.',null);
                return response()->json($response,400);
            }
        } catch (\Exception $e) {
            //return $e->getMessage();
            $response = APIHelpers::createAPIResponse(false,500,'Internal Server Error.',null);
            return response()->json($response,500);
        }
    }

    public function productUnitDelete(Request $request){
        try {
            $check_exists_product_unit = DB::table("product_units")->where('id',$request->product_unit_id)->pluck('id')->first();
            if($check_exists_product_unit == null){
                $response = APIHelpers::createAPIResponse(true,404,'No Product Unit Found.',null);
                return response()->json($response,404);
            }

            //$delete_product_unit = DB::table("product_units")->where('id',$request->product_unit_id)->delete();
            $soft_delete_product_unit = ProductUnit::find($request->product_unit_id);
            $soft_delete_product_unit->status=0;
            $affected_row = $soft_delete_product_unit->update();
            if($affected_row)
            {
                $response = APIHelpers::createAPIResponse(false,200,'Product Unit Successfully Soft Deleted.',null);
                return response()->json($response,200);
            }else{
                $response = APIHelpers::createAPIResponse(true,400,'Product Unit Updated Failed.',null);
                return response()->json($response,400);
            }
        } catch (\Exception $e) {
            //return $e->getMessage();
            $response = APIHelpers::createAPIResponse(false,500,'Internal Server Error.',null);
            return response()->json($response,500);
        }
    }
}
