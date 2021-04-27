<?php

namespace App\Http\Controllers\API;

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
        $product_vats = DB::table('product_vats')->select('id','name','vat_percentage','status')->orderBy('id','desc')->get();

        if($product_vats)
        {
            $success['product_vats'] =  $product_vats;
            return response()->json(['success'=>true,'response' => $success], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Product Vat List Found!'], $this->failStatus);
        }
    }

    public function productVatCreate(Request $request){

        $validator = Validator::make($request->all(), [
            'name' => 'required|unique:product_vats,name',
            'vat_percentage'=> 'required',
            'status'=> 'required',
        ]);

        if ($validator->fails()) {
            $response = [
                'success' => false,
                'data' => 'Validation Error.',
                'message' => $validator->errors()
            ];

            return response()->json($response, $this-> validationStatus);
        }


        $productVat = new ProductVat();
        $productVat->name = $request->name;
        $productVat->vat_percentage = $request->vat_percentage;
        $productVat->status = $request->status;
        $productVat->save();
        $insert_id = $productVat->id;

        if($insert_id){
            return response()->json(['success'=>true,'response' => $productVat], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'Product Vat Not Created Successfully!'], $this->failStatus);
        }
    }

    public function productVatEdit(Request $request){

        $validator = Validator::make($request->all(), [
            'product_vat_id'=> 'required',
            'name' => 'required|unique:product_vats,name,'.$request->product_vat_id,
            'vat_percentage'=> 'required',
            'status'=> 'required',
        ]);

        if ($validator->fails()) {
            $response = [
                'success' => false,
                'data' => 'Validation Error.',
                'message' => $validator->errors()
            ];

            return response()->json($response, $this->validationStatus);
        }

        $check_exists_product_vat = DB::table("product_vats")->where('id',$request->product_vat_id)->pluck('id')->first();
        if($check_exists_product_vat == null){
            return response()->json(['success'=>false,'response'=>'No Product Vat Found!'], $this->failStatus);
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
    }

    public function productVatDelete(Request $request){
        $check_exists_product_vat = DB::table("product_vats")->where('id',$request->product_vat_id)->pluck('id')->first();
        if($check_exists_product_vat == null){
            return response()->json(['success'=>false,'response'=>'No Product Vat Found!'], $this->failStatus);
        }

        $soft_delete_product_vat = ProductVat::find($request->product_vat_id);
        $soft_delete_product_vat->status=0;
        $affected_row = $soft_delete_product_vat->update();
        if($affected_row)
        {
            return response()->json(['success'=>true,'response' => 'Product Vat Successfully Soft Deleted!'], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Product Vat Deleted!'], $this->failStatus);
        }
    }
}
