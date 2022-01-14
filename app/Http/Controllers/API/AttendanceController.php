<?php

namespace App\Http\Controllers\API;

use App\Attendance;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class AttendanceController extends Controller
{
    public $successStatus = 200;
    public $authStatus = 401;
    public $failStatus = 402;
    public $ExistsStatus = 403;
    public $validationStatus = 404;

    // Attendance
    public function attendanceList(Request $request){
        if($request->from_date && $request->to_date && $request->store_id){
            $attendances = DB::table('attendances')
                ->join('employees','attendances.employee_id','=','employees.id')
                ->where('attendances.date','>=',$request->from_date)
                ->where('attendances.date','<=',$request->to_date)
                ->where('attendances.store_id',$request->store_id)
                ->select('attendances.id','attendances.card_no','attendances.employee_name','attendances.date','attendances.year','attendances.month','attendances.on_duty','attendances.off_duty','attendances.clock_in','attendances.clock_out','attendances.late','attendances.early','attendances.absent','attendances.work_time','attendances.att_time','attendances.status','attendances.id as employee_id','employees.name as employee_name')
                ->orderBy('id','desc')
                ->get();
        }elseif($request->from_date && $request->to_date){

            $attendances = DB::table('attendances')
                ->join('employees','attendances.employee_id','=','employees.id')
                ->where('attendances.date','>=',$request->from_date)
                ->where('attendances.date2','<=',$request->to_date)
                ->where('attendances.store_id',NULL)
                ->select('attendances.id','attendances.card_no','attendances.employee_name','attendances.date','attendances.year','attendances.month','attendances.on_duty','attendances.off_duty','attendances.clock_in','attendances.clock_out','attendances.late','attendances.early','attendances.absent','attendances.work_time','attendances.att_time','attendances.status','attendances.id as employee_id','employees.name as employee_name')
                ->orderBy('id','desc')
                ->get();
        }elseif($request->store_id){
            $attendances = DB::table('attendances')
                ->join('employees','attendances.employee_id','=','employees.id')
                ->where('employees.store_id',$request->store_id)
                ->select('attendances.id','attendances.card_no','attendances.employee_name','attendances.date','attendances.year','attendances.month','attendances.on_duty','attendances.off_duty','attendances.clock_in','attendances.clock_out','attendances.late','attendances.early','attendances.absent','attendances.work_time','attendances.att_time','attendances.status','attendances.id as employee_id','employees.name as employee_name')
                ->orderBy('id','desc')
                ->get();
        }else{
            $attendances = DB::table('attendances')
                ->join('employees','attendances.employee_id','=','employees.id')
                ->select('attendances.id','attendances.card_no','attendances.employee_name','attendances.date','attendances.year','attendances.month','attendances.on_duty','attendances.off_duty','attendances.clock_in','attendances.clock_out','attendances.late','attendances.early','attendances.absent','attendances.work_time','attendances.att_time','attendances.status','attendances.id as employee_id','employees.name as employee_name')
                ->orderBy('id','desc')
                ->get();
        }

        if($attendances)
        {
            $success['attendances'] =  $attendances;
            return response()->json(['success'=>true,'response' => $success], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Attendance List Found!'], $this->failStatus);
        }
    }

//    public function attendanceCreate(Request $request){
//
//        $validator = Validator::make($request->all(), [
//            'employee_id'=> 'required',
//            'card_no'=> 'required',
//            'employee_name'=> 'required',
//            'date'=> 'required',
//            'year'=> 'required',
//            'month'=> 'required',
//            'on_duty'=> 'required',
//            'off_duty'=> 'required',
//            'clock_in'=> 'required',
//            'clock_out'=> 'required',
//            'late'=> 'required',
//        ]);
//
//        if ($validator->fails()) {
//            $response = [
//                'success' => false,
//                'data' => 'Validation Error.',
//                'message' => $validator->errors()
//            ];
//
//            return response()->json($response, $this-> validationStatus);
//        }
//
//
//        $attendance = new Attendance();
//        $attendance->employee_id = $request->employee_id;
//        $attendance->card_no = $request->card_no;
//        $attendance->employee_name = $request->employee_name;
//        $attendance->date = $request->date;
//        $attendance->year = $request->year;
//        $attendance->month = $request->month;
//        $attendance->on_duty = $request->on_duty;
//        $attendance->off_duty = $request->off_duty;
//        $attendance->clock_in = $request->clock_in;
//        $attendance->clock_out = $request->clock_out;
//        $attendance->late = $request->late;
//        $attendance->early = $request->early;
//        $attendance->absent = $request->absent;
//        $attendance->work_time = $request->work_time;
//        $attendance->att_time = $request->att_time;
//        $attendance->status = $request->note;
//        $attendance->save();
//        $insert_id = $attendance->id;
//
//        if($insert_id){
//            return response()->json(['success'=>true,'response' => $attendance], $this->successStatus);
//        }else{
//            return response()->json(['success'=>false,'response'=>'Attendance Not Created Successfully!'], $this->failStatus);
//        }
//    }






//    public function attendanceCreate(Request $request){
//        $validator = Validator::make($request->all(), [
//            'attendances'=> 'required',
//        ]);
//
//        if ($validator->fails()) {
//            $response = [
//                'success' => false,
//                'data' => 'Validation Error.',
//                'message' => $validator->errors()
//            ];
//
//            return response()->json($response, $this-> validationStatus);
//        }
//
//        foreach ($request->attendances as $data) {
//            $card_no = DB::table('employee_office_informations')
//                ->where('card_no',$data['card_no'])
//                ->pluck('card_no')
//                ->first();
//
//            if(empty($card_no)){
//                $response = [
//                    'success' => false,
//                    'data' => 'Validation Error.',
//                    'message' => ['This ['.$data['card_no'].'] Not Found, For Any Employee.]'],
//                    'exist'=>1
//                ];
//                return response()->json($response, $this-> failStatus);
//            }
//
//            $date =  $data['date'];
//
//            $year = date('Y', strtotime($date));
//            $month = date('m', strtotime($date));
//
//            // check employee already attendance
//            $check_exists = Attendance::where('card_no',$data['card_no'])
//                ->where('year',$year)
//                ->where('month',$month)
//                ->first();
//            if(!empty($check_exists)){
//                $response = [
//                    'success' => false,
//                    'data' => 'Validation Error.',
//                    'message' => ['This Employee ['.$data['card_no'].'] Attendance of '.$month. ' ' .$year.' Already Exists, Please Try Another.]'],
//                    'exist'=>1
//                ];
//                return response()->json($response, $this-> failStatus);
//            }
//
//        }
//
//        $success_insert_flag = true;
//
//
//        foreach ($request->attendances as $data) {
//
//            $date =  $data['date'];
//
//            $year = date('Y', strtotime($date));
//            //$month = date('F', strtotime($date));
//            $month = date('m', strtotime($date));
//            $day = date('d', strtotime($date));
//            $update_date = $year.'-'.$month.'-'.$day;
//
//
//            $employee_info = DB::table('employees')
//                ->join('employee_office_informations','employees.id','=','employee_office_informations.employee_id')
//                ->where('employee_office_informations.card_no',$data['card_no'])
//                ->select('employees.id','employees.name','employee_office_informations.card_no','employees.warehouse_id','employees.store_id')
//                ->first();
//
//            $attendance = new Attendance();
//            $attendance->warehouse_id = $employee_info->warehouse_id;
//            $attendance->store_id = $employee_info->store_id;
//            $attendance->employee_id = $employee_info->id;
//            $attendance->card_no = $data['card_no'];
//            $attendance->employee_name = $employee_info->name;
//            $attendance->date = $update_date;
//            $attendance->year = $year;
//            $attendance->month = $month;
//            $attendance->day = $day;
//
//            $attendance->on_duty = isset($data['on_duty']) ? $data['on_duty'] : NULL;
//            $attendance->off_duty = isset($data['off_duty']) ? $data['off_duty'] : NULL;
//            $attendance->clock_in = isset($data['clock_in']) ? $data['clock_in'] : NULL;
//            $attendance->clock_out = isset($data['clock_out']) ? $data['clock_out'] : NULL;
//            $attendance->late = isset($data['late']) ? $data['late'] : NULL;
//            $attendance->early = isset($data['early']) ? $data['early'] : NULL;
//            $attendance->absent = isset($data['absent']) ? $data['absent'] : NULL;
//            $attendance->work_time = isset($data['work_time']) ? $data['work_time'] : NULL;
//            $attendance->att_time = isset($data['att_time']) ? $data['att_time'] : NULL;
//            $attendance->status = isset($data['late']) ? 'Late' : 'Present';
//            $attendance->save();
//            $insert_id = $attendance->id;
//            if($insert_id == ''){
//                $success_insert_flag = false;
//            }
//        }
//        if($success_insert_flag == true){
//            return response()->json(['success'=>true,'response' => 'Inserted Successfully.'], $this->successStatus);
//        }else{
//            return response()->json(['success'=>false,'response'=>'No Inserted Successfully!'], $this->failStatus);
//        }
//    }




//    public function attendanceCreate(Request $request){
//        $validator = Validator::make($request->all(), [
//            'attendances'=> 'required',
//        ]);
//
//        if ($validator->fails()) {
//            $response = [
//                'success' => false,
//                'data' => 'Validation Error.',
//                'message' => $validator->errors()
//            ];
//
//            return response()->json($response, $this-> validationStatus);
//        }
//
//        foreach ($request->attendances as $data) {
//            $card_no = DB::table('employee_office_informations')
//                ->where('card_no',$data['card_no'])
//                ->pluck('card_no')
//                ->first();
//
//            if(empty($card_no)){
//                $response = [
//                    'success' => false,
//                    'data' => 'Validation Error.',
//                    'message' => ['This ['.$data['card_no'].'] Not Found, For Any Employee.]'],
//                    'exist'=>1
//                ];
//                return response()->json($response, $this-> failStatus);
//            }
//
//        }
//
//        $attendance_datas = $request->attendances;
//        $date = $request->attendances[0]['date'];
//        $year = date('Y', strtotime($date));
//        $month = date('m', strtotime($date));
//
//        $day_count_of_month = cal_days_in_month(CAL_GREGORIAN, $month, $year);
//
//        $day = 1;
//        $success_insert_flag = true;
//
//        for($i=0;$i<$day_count_of_month;$i++){
//            // check attendance
//            if($day == 1){
//                $custom_today = '01';
//            }elseif($day == 2){
//                $custom_today = '02';
//            }elseif($day == 3){
//                $custom_today = '03';
//            }elseif($day == 4){
//                $custom_today = '04';
//            }elseif($day == 5){
//                $custom_today = '05';
//            }elseif($day == 6){
//                $custom_today = '06';
//            }elseif($day == 7){
//                $custom_today = '07';
//            }elseif($day == 8){
//                $custom_today = '08';
//            }elseif($day == 9){
//                $custom_today = '09';
//            }else{
//                $custom_today = $day;
//            }
//
//            $current_date = $year.'-'.$month.'-'.$custom_today;
//
//            $attendance_data_check = getExcelAttendanceData($attendance_datas,$current_date);
//            $date_match_or_not = $attendance_data_check['date_match_or_not'];
//            $employee_data = $attendance_data_check['employee_data'];
//            $attendance_data = $attendance_data_check['attendance_data'];
//
//            if($date_match_or_not !== ''){
//                //echo ' => found ----';
//                if($attendance_data['clock_in'] > $attendance_data['on_duty']){
//                    //$late = $attendance_data['clock_in'] - $attendance_data['on_duty'];
//                    $late = $attendance_data['late'];
//                    $status = 'Late';
//                }else{
//                    $late = NULL;
//                    $status = 'Present';
//                }
//
//                $on_duty = $attendance_data['on_duty'];
//                $off_duty = $attendance_data['off_duty'];
//                $clock_in = $attendance_data['clock_in'];
//                $clock_out = $attendance_data['clock_out'];
//            }else{
//                //echo ' => not found ----';
//                $on_duty = NULL;
//                $off_duty = NULL;
//                $clock_in = NULL;
//                $clock_out = NULL;
//                $late = NULL;
//
//                $weekend_check = getWeekendThisDate($current_date);
//                $holiday_check = getHolidayThisDate($current_date);
//                $leave_check = getLeaveThisDate($current_date);
//                if(!empty($weekend_check)){
//                    $status = 'Weekend';
//                }elseif(!empty($holiday_check)){
//                    $status = 'Holiday';
//                }elseif(!empty($leave_check)){
//                    $status = 'Leave';
////                    if($leave_check == 'Pending'){
////
////                    }
//                }else{
//                    $status = 'Absent';
//                }
//            }
//
//            $attendance = new Attendance();
//            $attendance->warehouse_id = $employee_data->warehouse_id;
//            $attendance->store_id = $employee_data->store_id;
//            $attendance->employee_id = $employee_data->id;
//            $attendance->card_no = $data['card_no'];
//            $attendance->employee_name = $employee_data->name;
//            $attendance->date = $current_date;
//            $attendance->year = $year;
//            $attendance->month = $month;
//            $attendance->day = $custom_today;
//            $attendance->on_duty = $on_duty;
//            $attendance->off_duty = $off_duty;
//            $attendance->clock_in = $clock_in;
//            $attendance->clock_out = $clock_out;
//            $attendance->late = $late;
//            $attendance->early = NULL;
//            $attendance->absent = NULL;
//            $attendance->work_time = NULL;
//            $attendance->att_time = NULL;
//            $attendance->status = $status;
//            $attendance->save();
//
//            $day ++;
//        }
//
//        if($success_insert_flag == true){
//            return response()->json(['success'=>true,'response' => 'Inserted Successfully.'], $this->successStatus);
//        }else{
//            return response()->json(['success'=>false,'response'=>'No Inserted Successfully!'], $this->failStatus);
//        }
//    }

    public function attendanceCreate(Request $request){
        $validator = Validator::make($request->all(), [
            'attendances'=> 'required',
        ]);

        if ($validator->fails()) {
            $response = [
                'success' => false,
                'data' => 'Validation Error.',
                'message' => $validator->errors()
            ];

            return response()->json($response, $this-> validationStatus);
        }

        $count_card_nos = [];
        foreach ($request->attendances as $data) {
            if(!in_array($data['card_no'], $count_card_nos, true)){
                array_push($count_card_nos, $data['card_no']);
            }

            $card_no = DB::table('employee_office_informations')
                ->where('card_no',$data['card_no'])
                ->pluck('card_no')
                ->first();

            if(empty($card_no)){
                $response = [
                    'success' => false,
                    'data' => 'Validation Error.',
                    'message' => ['This ['.$data['card_no'].'] Not Found, For Any Employee.]'],
                    'exist'=>1
                ];
                return response()->json($response, $this-> failStatus);
            }

        }

        $employee_count = count($count_card_nos);
        if($employee_count > 0){
            foreach($count_card_nos as $employee_card_no){

                $employee_data = DB::table('employees')
                    ->join('employee_office_informations','employees.id','=','employee_office_informations.employee_id')
                    ->where('employee_office_informations.card_no',$employee_card_no)
                    ->select('employees.id','employees.name','employee_office_informations.card_no','employees.warehouse_id','employees.store_id')
                    ->first();

                $attendance_datas = $request->attendances;
                $date = $request->attendances[0]['date'];
                $year = date('Y', strtotime($date));
                $month = date('m', strtotime($date));

                $day_count_of_month = cal_days_in_month(CAL_GREGORIAN, $month, $year);

                $day = 1;
                $success_insert_flag = true;

                for($i=0;$i<$day_count_of_month;$i++){
                    // check attendance
                    if($day == 1){
                        $custom_today = '01';
                    }elseif($day == 2){
                        $custom_today = '02';
                    }elseif($day == 3){
                        $custom_today = '03';
                    }elseif($day == 4){
                        $custom_today = '04';
                    }elseif($day == 5){
                        $custom_today = '05';
                    }elseif($day == 6){
                        $custom_today = '06';
                    }elseif($day == 7){
                        $custom_today = '07';
                    }elseif($day == 8){
                        $custom_today = '08';
                    }elseif($day == 9){
                        $custom_today = '09';
                    }else{
                        $custom_today = $day;
                    }

                    $current_date = $year.'-'.$month.'-'.$custom_today;

                    $attendance_data_check = getExcelAttendanceData($attendance_datas,$current_date,$employee_card_no);
                    $date_match_or_not = $attendance_data_check['date_match_or_not'];
                    $attendance_data = $attendance_data_check['attendance_data'];

                    if($date_match_or_not !== ''){
                        //echo ' => found ----';
                        if($attendance_data['clock_in'] > $attendance_data['on_duty']){
                            //$late = $attendance_data['clock_in'] - $attendance_data['on_duty'];
                            $late = $attendance_data['late'];
                            $status = 'Late';
                        }else{
                            $late = NULL;
                            $status = 'Present';
                        }

                        $on_duty = $attendance_data['on_duty'];
                        $off_duty = $attendance_data['off_duty'];
                        $clock_in = $attendance_data['clock_in'];
                        $clock_out = $attendance_data['clock_out'];
                    }else{
                        //echo ' => not found ----';
                        $on_duty = NULL;
                        $off_duty = NULL;
                        $clock_in = NULL;
                        $clock_out = NULL;
                        $late = NULL;

                        $weekend_check = getWeekendThisDate($current_date);
                        $holiday_check = getHolidayThisDate($current_date);
                        $leave_check = getLeaveThisDate($current_date);
                        if(!empty($weekend_check)){
                            $status = 'Weekend';
                        }elseif(!empty($holiday_check)){
                            $status = 'Holiday';
                        }elseif(!empty($leave_check)){
                            $status = 'Leave';
                        }else{
                            $status = 'Absent';
                        }
                    }

                    $attendance = new Attendance();
                    $attendance->warehouse_id = $employee_data->warehouse_id;
                    $attendance->store_id = $employee_data->store_id;
                    $attendance->employee_id = $employee_data->id;
                    $attendance->card_no = $employee_card_no;
                    $attendance->employee_name = $employee_data->name;
                    $attendance->date = $current_date;
                    $attendance->year = $year;
                    $attendance->month = $month;
                    $attendance->day = $custom_today;
                    $attendance->on_duty = $on_duty;
                    $attendance->off_duty = $off_duty;
                    $attendance->clock_in = $clock_in;
                    $attendance->clock_out = $clock_out;
                    $attendance->late = $late;
                    $attendance->early = NULL;
                    $attendance->absent = NULL;
                    $attendance->work_time = NULL;
                    $attendance->att_time = NULL;
                    $attendance->status = $status;
                    $attendance->note = $date_match_or_not;
                    $attendance->save();

                    $day ++;
                }
            }
        }

        if($success_insert_flag == true){
            return response()->json(['success'=>true,'response' => 'Inserted Successfully.'], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Inserted Successfully!'], $this->failStatus);
        }
    }












//    public function attendanceEdit(Request $request){
//
//        $validator = Validator::make($request->all(), [
//            'attendance_id'=> 'required',
//            'employee_id'=> 'required',
//            'card_no'=> 'required',
//            'employee_name'=> 'required',
//            'date'=> 'required',
//            'year'=> 'required',
//            'month'=> 'required',
//            'on_duty'=> 'required',
//            'off_duty'=> 'required',
//            'clock_in'=> 'required',
//            'clock_out'=> 'required',
//            'late'=> 'required',
//        ]);
//
//        if ($validator->fails()) {
//            $response = [
//                'success' => false,
//                'data' => 'Validation Error.',
//                'message' => $validator->errors()
//            ];
//
//            return response()->json($response, $this->validationStatus);
//        }
//
//        $check_exists_attendances = DB::table("attendances")->where('id',$request->attendance_id)->pluck('id')->first();
//        if($check_exists_attendances == null){
//            return response()->json(['success'=>false,'response'=>'No Attendance Found!'], $this->failStatus);
//        }
//
//        $attendance = Attendance::find($request->attendance_id);
//        $attendance->employee_id = $request->employee_id;
//        $attendance->card_no = $request->card_no;
//        $attendance->employee_name = $request->employee_name;
//        $attendance->date = $request->date;
//        $attendance->year = $request->year;
//        $attendance->month = $request->month;
//        $attendance->on_duty = $request->on_duty;
//        $attendance->off_duty = $request->off_duty;
//        $attendance->clock_in = $request->clock_in;
//        $attendance->clock_out = $request->clock_out;
//        $attendance->late = $request->late;
//        $attendance->early = $request->early;
//        $attendance->absent = $request->absent;
//        $attendance->work_time = $request->work_time;
//        $attendance->att_time = $request->att_time;
//        $update_attendance = $attendance->save();
//
//        if($update_attendance){
//            return response()->json(['success'=>true,'response' => $attendance], $this->successStatus);
//        }else{
//            return response()->json(['success'=>false,'response'=>'Attendance Not Updated Successfully!'], $this->failStatus);
//        }
//    }

//    public function attendanceDelete(Request $request){
//        $check_exists_attendances = DB::table("attendances")->where('id',$request->attendance_id)->pluck('id')->first();
//        if($check_exists_attendances == null){
//            return response()->json(['success'=>false,'response'=>'No Attendance Found!'], $this->failStatus);
//        }
//
//        $soft_delete_attendance = Attendance::find($request->attendance_id);
//        $soft_delete_attendance->status=0;
//        $affected_row = $soft_delete_attendance->update();
//        if($affected_row)
//        {
//            return response()->json(['success'=>true,'response' => 'Attendance Successfully Soft Deleted!'], $this->successStatus);
//        }else{
//            return response()->json(['success'=>false,'response'=>'No Attendance Deleted!'], $this->failStatus);
//        }
//    }

    public function attendanceReport(Request $request){
        $validator = Validator::make($request->all(), [
            'employee_id'=> 'required',
            'year'=>'required',
            'month'=> 'required',
        ]);

        if ($validator->fails()) {
            $response = [
                'success' => false,
                'data' => 'Validation Error.',
                'message' => $validator->errors()
            ];

            return response()->json($response, $this-> validationStatus);
        }

        $year = $request->year;
        $month = $request->month;
        $employee_id = $request->employee_id;

        $day_count_of_month = cal_days_in_month(CAL_GREGORIAN, $month, $year);

        $attendance_data = [];
        $day = 1;
        $custom_today = '';
        $absent = '';
        for($i=0;$i<$day_count_of_month;$i++){

            $get_employee = DB::table('employees')
                ->leftJoin('employee_office_informations','employees.id','employee_office_informations.employee_id')
                ->where('employees.id',$employee_id)
                ->select('employees.name as employee_name','employee_office_informations.card_no')
                ->first();


            // check attendance
            if($day == 1){
                $custom_today = '01';
            }elseif($day == 2){
                $custom_today = '02';
            }elseif($day == 3){
                $custom_today = '03';
            }elseif($day == 4){
                $custom_today = '04';
            }elseif($day == 5){
                $custom_today = '05';
            }elseif($day == 6){
                $custom_today = '06';
            }elseif($day == 7){
                $custom_today = '07';
            }elseif($day == 8){
                $custom_today = '08';
            }elseif($day == 9){
                $custom_today = '09';
            }else{
                $custom_today = $day;
            }

            $current_date = $year.'-'.$month.'-'.$custom_today;
            $check_attendance = DB::table('attendances')
                ->where('employee_id',$employee_id)
                ->where('date',$current_date)
                ->first();

            if($check_attendance == null){
                // weekend
                $check_weekend = DB::table('weekends')
                    ->where('date',$current_date)
                    ->first();

                // holiday
                $check_holiday = DB::table('holidays')
                    ->where('date',$current_date)
                    ->first();
                if($check_weekend){
                    $absent = 'Weekend';
                }elseif($check_holiday){
                    $absent = 'Holiday';
                }else{
                    $absent = 'Absent';
                }
            }else{
                if($check_attendance->clock_in){
                    $absent = 'Present';
                }if($check_attendance->clock_in == ''){
                    $absent = 'Absent';
                }
            }


            $nested_data['day']= $day;
            $nested_data['month']= $month;
            $nested_data['year']= $year;
            $nested_data['current_date']= $current_date;
            $nested_data['card_no']= $get_employee ? $get_employee->card_no : '';
            $nested_data['employee_name']= $get_employee ? $get_employee->employee_name : '';
            $nested_data['clock_in']= $check_attendance ? $check_attendance->clock_in : '';
            $nested_data['clock_out']= $check_attendance ? $check_attendance->clock_out : '';
            $nested_data['late']= $check_attendance ? $check_attendance->late : '';
            $nested_data['work_time']= $check_attendance ? $check_attendance->work_time : '';
            $nested_data['early']= $check_attendance ? $check_attendance->early : '';
            $nested_data['absent']= $absent;

            $day++;

            array_push($attendance_data,$nested_data);
        }

        if($attendance_data){
            return response()->json(['success'=>true,'response' => $attendance_data], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Inserted Successfully!'], $this->failStatus);
        }
    }


    public function totalAbsentByEmployee(Request $request){
        $validator = Validator::make($request->all(), [
            'year'=> 'required',
            'month'=> 'required',
            'employee_id'=> 'required',
        ]);

        if ($validator->fails()) {
            $response = [
                'success' => false,
                'data' => 'Validation Error.',
                'message' => $validator->errors()
            ];

            return response()->json($response, $this->validationStatus);
        }

        $month = $request->month;
        $year = $request->year;
        $employee_id = $request->employee_id;

        $total_absent = 0;
        $day_count_of_month = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        $day = 1;

        //$check_data_arr = [];
        for($i=0;$i<$day_count_of_month;$i++) {

            // check attendance
            if ($day == 1) {
                $custom_today = '01';
            } elseif ($day == 2) {
                $custom_today = '02';
            } elseif ($day == 3) {
                $custom_today = '03';
            } elseif ($day == 4) {
                $custom_today = '04';
            } elseif ($day == 5) {
                $custom_today = '05';
            } elseif ($day == 6) {
                $custom_today = '06';
            } elseif ($day == 7) {
                $custom_today = '07';
            } elseif ($day == 8) {
                $custom_today = '08';
            } elseif ($day == 9) {
                $custom_today = '09';
            } else {
                $custom_today = $day;
            }

            $day++;

            //$current_date = $year . '-' . $month . '-' . $custom_today;
            $clock_in = DB::table('attendances')
                ->where('employee_id', $employee_id)
                //->where('date', $current_date)
                ->where('year', $year)
                ->where('month', $month)
                ->where('day', $custom_today)
                ->pluck('clock_in')
                ->first();

            if($clock_in == null){
                $total_absent += 1;
            }

            //$nested_data['clock_in'] = $clock_in;
            //array_push($check_data_arr, $nested_data);

        }

        return response()->json(['success'=>true,'total_absent' => $total_absent], $this->successStatus);
    }
}
