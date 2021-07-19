<?php

namespace App\Http\Controllers\API;

use App\Holiday;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class HolidayController extends Controller
{
    public $successStatus = 200;
    public $authStatus = 401;
    public $failStatus = 402;
    public $ExistsStatus = 403;
    public $validationStatus = 404;

    public function holidayList(){
        $holidays = DB::table('holidays')->select('id','name','date','details','status')->orderBy('id','desc')->get();

        if($holidays)
        {
            $success['holidays'] =  $holidays;
            return response()->json(['success'=>true,'response' => $success], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Holidays List Found!'], $this->failStatus);
        }
    }

    public function holidayCreate(Request $request){

        $validator = Validator::make($request->all(), [
            'name' => 'required|unique:holidays,name',
            'date'=> 'required',
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


        $year = date('Y', strtotime($request->date));
        $month = date('m', strtotime($request->date));
        $day = date('d', strtotime($request->date));

        $holiday = new Holiday();
        $holiday->name = $request->name;
        $holiday->date = $request->date;
        $holiday->year = $year;
        $holiday->month = $month;
        $holiday->day = $day;
        $holiday->details = $request->details;
        $holiday->status = $request->status;
        $holiday->save();
        $insert_id = $holiday->id;

        if($insert_id){
            return response()->json(['success'=>true,'response' => $holiday], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'Holidays Not Created Successfully!'], $this->failStatus);
        }
    }

    public function holidayEdit(Request $request){

        $validator = Validator::make($request->all(), [
            'holiday_id'=> 'required',
            'name' => 'required|unique:holidays,name,'.$request->holiday_id,
            'date'=> 'required',
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

        $check_exists_holiday = DB::table("holidays")->where('id',$request->holiday_id)->pluck('id')->first();
        if($check_exists_holiday == null){
            return response()->json(['success'=>false,'response'=>'No holiday Found!'], $this->failStatus);
        }

        $year = date('Y', strtotime($request->date));
        $month = date('m', strtotime($request->date));
        $day = date('d', strtotime($request->date));

        $holiday = Holiday::find($request->holiday_id);
        $holiday->name = $request->name;
        $holiday->date = $request->date;
        $holiday->year = $year;
        $holiday->month = $month;
        $holiday->day = $day;
        $holiday->details = $request->details;
        $holiday->status = $request->status;
        $update_holiday = $holiday->save();

        if($update_holiday){
            return response()->json(['success'=>true,'response' => $holiday], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'holiday Not Created Successfully!'], $this->failStatus);
        }
    }

    public function holidayDelete(Request $request){
        $check_exists_holiday = DB::table("holidays")->where('id',$request->holiday_id)->pluck('id')->first();
        if($check_exists_holiday == null){
            return response()->json(['success'=>false,'response'=>'No Holiday Found!'], $this->failStatus);
        }

        $soft_delete_holiday = Holiday::find($request->holiday_id);
        $soft_delete_holiday->status=0;
        $affected_row = $soft_delete_holiday->update();
        if($affected_row)
        {
            return response()->json(['success'=>true,'response' => 'Holiday Successfully Soft Deleted!'], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Holiday Deleted!'], $this->failStatus);
        }
    }
}
