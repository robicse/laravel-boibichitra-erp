<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Weekend;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class WeekendController extends Controller
{
    public $successStatus = 200;
    public $authStatus = 401;
    public $failStatus = 402;
    public $ExistsStatus = 403;
    public $validationStatus = 404;

    // weekend
    public function weekendList(){
        $weekends = DB::table('weekends')->select('id','date','note','status')->orderBy('id','desc')->get();

        if($weekends)
        {
            $success['weekends'] =  $weekends;
            return response()->json(['success'=>true,'response' => $success], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Weekends List Found!'], $this->failStatus);
        }
    }

    public function weekendCreate(Request $request){

        $validator = Validator::make($request->all(), [
            'date' => 'required|unique:weekends,date',
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

        $weekend = new Weekend();
        $weekend->date = $request->date;
        $weekend->year = $year;
        $weekend->month = $month;
        $weekend->day = $day;
        $weekend->note = isset($request->note) ? $request->note : NULL;
        $weekend->status = $request->status;
        $weekend->save();
        $insert_id = $weekend->id;

        if($insert_id){
            return response()->json(['success'=>true,'response' => $weekend], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'weekends Not Created Successfully!'], $this->failStatus);
        }
    }

    public function weekendEdit(Request $request){

        $validator = Validator::make($request->all(), [
            'weekend_id'=> 'required',
            'date' => 'required|unique:weekends,date,'.$request->weekend_id,
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

        $check_exists_weekend = DB::table("weekends")->where('id',$request->weekend_id)->pluck('id')->first();
        if($check_exists_weekend == null){
            return response()->json(['success'=>false,'response'=>'No weekend Found!'], $this->failStatus);
        }

        $year = date('Y', strtotime($request->date));
        $month = date('m', strtotime($request->date));
        $day = date('d', strtotime($request->date));

        $weekend = Weekend::find($request->weekend_id);
        $weekend->date = $request->date;
        $weekend->year = $year;
        $weekend->month = $month;
        $weekend->day = $day;
        $weekend->note = $request->note;
        $weekend->status = $request->status;
        $update_weekend = $weekend->save();

        if($update_weekend){
            return response()->json(['success'=>true,'response' => $weekend], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'Weekend Not Created Successfully!'], $this->failStatus);
        }
    }

    public function weekendDelete(Request $request){
        $check_exists_weekend = DB::table("weekends")->where('id',$request->weekend_id)->pluck('id')->first();
        if($check_exists_weekend == null){
            return response()->json(['success'=>false,'response'=>'No Weekend Found!'], $this->failStatus);
        }

        $soft_delete_weekend = Weekend::find($request->weekend_id);
        $soft_delete_weekend->status=0;
        $affected_row = $soft_delete_weekend->update();
        if($affected_row)
        {
            return response()->json(['success'=>true,'response' => 'Weekend Successfully Soft Deleted!'], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Weekend Deleted!'], $this->failStatus);
        }
    }
}
