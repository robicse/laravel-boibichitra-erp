<?php

namespace App\Http\Controllers\API;

use App\Designation;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class DesignationController extends Controller
{
    public $successStatus = 200;
    public $authStatus = 401;
    public $failStatus = 402;
    public $ExistsStatus = 403;
    public $validationStatus = 404;

    public function designationList(){
        $designations = DB::table('designations')->select('id','name','status')->orderBy('id','desc')->get();

        if($designations)
        {
            $success['designations'] =  $designations;
            return response()->json(['success'=>true,'response' => $success], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Designations List Found!'], $this->failStatus);
        }
    }

    public function designationListActive(){
        $designations = DB::table('designations')->select('id','name','status')->where('status',1)->orderBy('id','desc')->get();

        if($designations)
        {
            $success['designations'] =  $designations;
            return response()->json(['success'=>true,'response' => $success], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Designations List Found!'], $this->failStatus);
        }
    }

    public function designationCreate(Request $request){

        $validator = Validator::make($request->all(), [
            'name' => 'required|unique:designations,name',
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


        $designations = new Designation();
        $designations->name = $request->name;
        $designations->status = $request->status;
        $designations->save();
        $insert_id = $designations->id;

        if($insert_id){
            return response()->json(['success'=>true,'response' => $designations], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'designations Not Created Successfully!'], $this->failStatus);
        }
    }

    public function designationEdit(Request $request){

        $validator = Validator::make($request->all(), [
            'designation_id'=> 'required',
            'name' => 'required|unique:designations,name,'.$request->designation_id,
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

        $check_exists_designation = DB::table("designations")->where('id',$request->designation_id)->pluck('id')->first();
        if($check_exists_designation == null){
            return response()->json(['success'=>false,'response'=>'No Designation Found!'], $this->failStatus);
        }

        $designation = Designation::find($request->designation_id);
        $designation->name = $request->name;
        $designation->status = $request->status;
        $update_designation = $designation->save();

        if($update_designation){
            return response()->json(['success'=>true,'response' => $designation], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'Designation Not Created Successfully!'], $this->failStatus);
        }
    }

    public function designationDelete(Request $request){
        $check_exists_designation = DB::table("designations")->where('id',$request->designation_id)->pluck('id')->first();
        if($check_exists_designation == null){
            return response()->json(['success'=>false,'response'=>'No Designation Found!'], $this->failStatus);
        }

        $soft_delete_designation = Designation::find($request->designation_id);
        $soft_delete_designation->status=0;
        $affected_row = $soft_delete_designation->update();
        if($affected_row)
        {
            return response()->json(['success'=>true,'response' => 'Designation Successfully Soft Deleted!'], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Designation Deleted!'], $this->failStatus);
        }
    }
}
