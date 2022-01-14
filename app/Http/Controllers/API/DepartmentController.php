<?php

namespace App\Http\Controllers\API;

use App\Department;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class DepartmentController extends Controller
{
    public $successStatus = 200;
    public $authStatus = 401;
    public $failStatus = 402;
    public $ExistsStatus = 403;
    public $validationStatus = 404;

    public function departmentList(){
        $departments = DB::table('departments')->select('id','name','status')->orderBy('id','desc')->get();

        if($departments)
        {
            $success['departments'] =  $departments;
            return response()->json(['success'=>true,'response' => $success], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Departments List Found!'], $this->failStatus);
        }
    }
    public function departmentListActive(){
        $departments = DB::table('departments')->select('id','name','status')->where('status',1)->orderBy('id','desc')->get();

        if($departments)
        {
            $success['departments'] =  $departments;
            return response()->json(['success'=>true,'response' => $success], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Departments List Found!'], $this->failStatus);
        }
    }

    public function departmentCreate(Request $request){

        $validator = Validator::make($request->all(), [
            'name' => 'required|unique:departments,name',
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


        $departments = new Department();
        $departments->name = $request->name;
        $departments->status = $request->status;
        $departments->save();
        $insert_id = $departments->id;

        if($insert_id){
            return response()->json(['success'=>true,'response' => $departments], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'Departments Not Created Successfully!'], $this->failStatus);
        }
    }

    public function departmentEdit(Request $request){

        $validator = Validator::make($request->all(), [
            'department_id'=> 'required',
            'name' => 'required|unique:departments,name,'.$request->department_id,
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

        $check_exists_department = DB::table("departments")->where('id',$request->department_id)->pluck('id')->first();
        if($check_exists_department == null){
            return response()->json(['success'=>false,'response'=>'No Department Found!'], $this->failStatus);
        }

        $department = Department::find($request->department_id);
        $department->name = $request->name;
        $department->status = $request->status;
        $update_department = $department->save();

        if($update_department){
            return response()->json(['success'=>true,'response' => $department], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'Department Not Created Successfully!'], $this->failStatus);
        }
    }

    public function departmentDelete(Request $request){
        $check_exists_department = DB::table("departments")->where('id',$request->department_id)->pluck('id')->first();
        if($check_exists_department == null){
            return response()->json(['success'=>false,'response'=>'No Department Found!'], $this->failStatus);
        }

        //$delete_party = DB::table("product_brands")->where('id',$request->product_brand_id)->delete();
        $soft_delete_department = Department::find($request->department_id);
        $soft_delete_department->status=0;
        $affected_row = $soft_delete_department->update();
        if($affected_row)
        {
            return response()->json(['success'=>true,'response' => 'Department Successfully Soft Deleted!'], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Department Deleted!'], $this->failStatus);
        }
    }
}
