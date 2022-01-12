<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\LeaveApplication;
use App\LeaveCategory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class LeaveController extends Controller
{
    public $successStatus = 200;
    public $authStatus = 401;
    public $failStatus = 402;
    public $ExistsStatus = 403;
    public $validationStatus = 404;

    // leave category
    public function leaveCategoryList(){
        $leave_categories = DB::table('leave_categories')->select('id','name','limit','duration','status')->orderBy('id','desc')->get();

        if($leave_categories)
        {
            $success['leave_categories'] =  $leave_categories;
            return response()->json(['success'=>true,'response' => $success], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Leave Category List Found!'], $this->failStatus);
        }
    }

    public function leaveCategoryCreate(Request $request){

        $validator = Validator::make($request->all(), [
            'name' => 'required|unique:leave_categories,name',
            'limit'=> 'required',
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


        $leave_category = new LeaveCategory();
        $leave_category->name = $request->name;
        $leave_category->limit = $request->limit;
        $leave_category->duration = $request->duration;
        $leave_category->status = $request->status;
        $leave_category->save();
        $insert_id = $leave_category->id;

        if($insert_id){
            return response()->json(['success'=>true,'response' => $leave_category], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'Leave Category Not Created Successfully!'], $this->failStatus);
        }
    }

    public function leaveCategoryEdit(Request $request){

        $validator = Validator::make($request->all(), [
            'leave_category_id'=> 'required',
            'name' => 'required|unique:leave_categories,name,'.$request->leave_category_id,
            'limit'=> 'required',
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

        $check_exists_leave_categories = DB::table("leave_categories")->where('id',$request->leave_category_id)->pluck('id')->first();
        if($check_exists_leave_categories == null){
            return response()->json(['success'=>false,'response'=>'No Leave Category Found!'], $this->failStatus);
        }

        $leave_category = LeaveCategory::find($request->leave_category_id);
        $leave_category->name = $request->name;
        $leave_category->limit = $request->limit;
        $leave_category->duration = $request->duration;
        $leave_category->status = $request->status;
        $update_leave_category = $leave_category->save();

        if($update_leave_category){
            return response()->json(['success'=>true,'response' => $leave_category], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'Leave Category Not Created Successfully!'], $this->failStatus);
        }
    }

    public function leaveCategoryDelete(Request $request){
        $check_exists_leave_category = DB::table("holidays")->where('id',$request->leave_category_id)->pluck('id')->first();
        if($check_exists_leave_category == null){
            return response()->json(['success'=>false,'response'=>'No Holiday Found!'], $this->failStatus);
        }

        $soft_delete_leave_category = LeaveCategory::find($request->leave_category_id);
        $soft_delete_leave_category->status=0;
        $affected_row = $soft_delete_leave_category->update();
        if($affected_row)
        {
            return response()->json(['success'=>true,'response' => 'Leave Category Successfully Soft Deleted!'], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Leave Category Deleted!'], $this->failStatus);
        }
    }

    // Leave Application
    public function leaveApplicationList(){
        $leave_applications = DB::table('leave_applications')
            ->join('employees','leave_applications.employee_id','=','employees.id')
            ->join('leave_categories','leave_applications.leave_category_id','=','leave_categories.id')
            ->select('leave_applications.id','leave_applications.start_date','leave_applications.end_date','leave_applications.duration','leave_applications.reason','leave_applications.approval_status','leave_applications.status','employees.id as employee_id','employees.name as employee_name','leave_categories.id as leave_category_id','leave_categories.name as leave_category_name')
            ->orderBy('id','desc')
            ->get();

        if($leave_applications)
        {
            $success['leave_applications'] =  $leave_applications;
            return response()->json(['success'=>true,'response' => $success], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Leave Application List Found!'], $this->failStatus);
        }
    }

    public function leaveApplicationCreate(Request $request){

        $validator = Validator::make($request->all(), [
            'employee_id'=> 'required',
            'leave_category_id'=> 'required',
            'start_date'=> 'required',
            'end_date'=> 'required',
            'duration'=> 'required',
        ]);

        if ($validator->fails()) {
            $response = [
                'success' => false,
                'data' => 'Validation Error.',
                'message' => $validator->errors()
            ];

            return response()->json($response, $this-> validationStatus);
        }


        $date1 = $request->start_date;
        $date2 = $request->end_date;

        $ts1 = strtotime($date1);
        $ts2 = strtotime($date2);

        $year1 = date('Y', $ts1);
        $year2 = date('Y', $ts2);

        $month1 = date('m', $ts1);
        $month2 = date('m', $ts2);
        if(($year1 !== $year2) && ($month1 !== $month2)){
            return response()->json(['success' => false,'data' => 'Please select same month on year'], $this-> validationStatus);
        }


        $leave_application = new LeaveApplication();
        $leave_application->employee_id = $request->employee_id;
        $leave_application->leave_category_id = $request->leave_category_id;
        $leave_application->year = $year1;
        $leave_application->month = $month1;
        $leave_application->start_date = $request->start_date;
        $leave_application->end_date = $request->end_date;
        $leave_application->duration = $request->duration;
        $leave_application->reason = $request->reason;
        $leave_application->approval_status = $request->approval_status;
        $leave_application->status = $request->status;
        $leave_application->save();
        $insert_id = $leave_application->id;

        if($insert_id){
            return response()->json(['success'=>true,'response' => $leave_application], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'Leave Application Not Created Successfully!'], $this->failStatus);
        }
    }

    public function leaveApplicationEdit(Request $request){

        $validator = Validator::make($request->all(), [
            'leave_application_id'=> 'required',
            'employee_id'=> 'required',
            'leave_category_id'=> 'required',
            'start_date'=> 'required',
            'end_date'=> 'required',
            'duration'=> 'required',
        ]);

        if ($validator->fails()) {
            $response = [
                'success' => false,
                'data' => 'Validation Error.',
                'message' => $validator->errors()
            ];

            return response()->json($response, $this->validationStatus);
        }

        $check_exists_leave_applications = DB::table("leave_applications")->where('id',$request->leave_application_id)->pluck('id')->first();
        if($check_exists_leave_applications == null){
            return response()->json(['success'=>false,'response'=>'No Leave Application Found!'], $this->failStatus);
        }

        $leave_application = LeaveApplication::find($request->leave_application_id);
        $leave_application->employee_id = $request->employee_id;
        $leave_application->leave_category_id = $request->leave_category_id;
        $leave_application->start_date = $request->start_date;
        $leave_application->end_date = $request->end_date;
        $leave_application->duration = $request->duration;
        $leave_application->reason = $request->reason;
        $leave_application->approval_status = $request->approval_status;
        $leave_application->status = $request->status;
        $update_leave_application = $leave_application->save();

        if($update_leave_application){
            return response()->json(['success'=>true,'response' => $leave_application], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'Leave Application Not Created Successfully!'], $this->failStatus);
        }
    }

    public function leaveApplicationDelete(Request $request){
        $check_exists_leave_applications = DB::table("leave_applications")->where('id',$request->leave_application_id)->pluck('id')->first();
        if($check_exists_leave_applications == null){
            return response()->json(['success'=>false,'response'=>'No Leave Application Found!'], $this->failStatus);
        }

        $soft_delete_leave_application = LeaveApplication::find($request->leave_application_id);
        $soft_delete_leave_application->status=0;
        $affected_row = $soft_delete_leave_application->update();
        if($affected_row)
        {
            return response()->json(['success'=>true,'response' => 'Leave Application Successfully Soft Deleted!'], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Leave Application Deleted!'], $this->failStatus);
        }
    }
}
