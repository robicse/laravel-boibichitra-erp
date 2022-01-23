<?php

namespace App\Http\Controllers\API;

use App\Helpers\APIHelpers;
use App\Http\Controllers\Controller;
use App\ProductVat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ProductVatController extends Controller
{
    public $successStatus = 200;
    public $authStatus = 401;
    public $failStatus = 402;
    public $ExistsStatus = 403;
    public $validationStatus = 404;

    // product vat
    public function productVatList(){
        try {
            $product_vats = DB::table('product_vats')->select('id','name','vat_percentage','status')->orderBy('id','desc')->get();

            if($product_vats === null){
                $response = APIHelpers::createAPIResponse(true,404,'No Product Vat Found.',null);
                return response()->json($response,404);
            }else{
                $response = APIHelpers::createAPIResponse(false,200,'',$product_vats);
                return response()->json($response,200);
            }
        } catch (\Exception $e) {
            //return $e->getMessage();
            $response = APIHelpers::createAPIResponse(false,500,'Internal Server Error.',null);
            return response()->json($response,500);
        }
    }

    public function productVatCreate(Request $request){
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|unique:product_vats,name',
                'vat_percentage'=> 'required',
                'status'=> 'required',
            ]);

            if ($validator->fails()) {
                $response = APIHelpers::createAPIResponse(true,400,$validator->errors(),null);
                return response()->json($response,400);
            }


            $productVat = new ProductVat();
            $productVat->name = $request->name;
            $productVat->vat_percentage = $request->vat_percentage;
            $productVat->status = $request->status;
            $productVat->save();

            $response = APIHelpers::createAPIResponse(false,201,'Product Vat Added Successfully.',null);
            return response()->json($response,201);
        } catch (\Exception $e) {
            //return $e->getMessage();
            $response = APIHelpers::createAPIResponse(false,500,'Internal Server Error.',null);
            return response()->json($response,500);
        }
    }

    public function productVatEdit(Request $request){
        try {
            $validator = Validator::make($request->all(), [
                'product_vat_id'=> 'required',
                'name' => 'required|unique:product_vats,name,'.$request->product_vat_id,
                'vat_percentage'=> 'required',
                'status'=> 'required',
            ]);

            if ($validator->fails()) {
                $response = APIHelpers::createAPIResponse(true,400,$validator->errors(),null);
                return response()->json($response,400);
            }

            $check_exists_product_vat = DB::table("product_vats")->where('id',$request->product_vat_id)->pluck('id')->first();
            if($check_exists_product_vat == null){
                $response = APIHelpers::createAPIResponse(true,404,'No Product Vat Found.',null);
                return response()->json($response,404);
            }

            $product_vats = ProductVat::find($request->product_vat_id);
            $product_vats->name = $request->name;
            $product_vats->vat_percentage = $request->vat_percentage;
            $product_vats->status = $request->status;
            $update_product_vat = $product_vats->save();

            if($update_product_vat){
                return response()->json(['success'=>true,'response' => $product_vats], $this->successStatus);
            }else{
                return response()->json(['success'=>false,'response'=>'Product Vat Not Updated Successfully!'], $this->failStatus);
            }
        } catch (\Exception $e) {
            //return $e->getMessage();
            $response = APIHelpers::createAPIResponse(false,500,'Internal Server Error.',null);
            return response()->json($response,500);
        }
    }

    public function productVatDelete(Request $request){
        try {
            $check_exists_product_vat = DB::table("product_vats")->where('id',$request->product_vat_id)->pluck('id')->first();
            if($check_exists_product_vat == null){
                $response = APIHelpers::createAPIResponse(true,404,'No Product Vat Found.',null);
                return response()->json($response,404);
            }

            $soft_delete_product_vat = ProductVat::find($request->product_vat_id);
            $soft_delete_product_vat->status=0;
            $affected_row = $soft_delete_product_vat->update();
            if($affected_row)
            {
                $response = APIHelpers::createAPIResponse(false,200,'Product Vat Successfully Soft Deleted.',null);
                return response()->json($response,200);
            }else{
                $response = APIHelpers::createAPIResponse(true,400,'Product Vat Updated Failed.',null);
                return response()->json($response,400);
            }
        } catch (\Exception $e) {
            //return $e->getMessage();
            $response = APIHelpers::createAPIResponse(false,500,'Internal Server Error.',null);
            return response()->json($response,500);
        }
    }
}
