<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\LeaveApplication;
use App\Payroll;
use App\Payslip;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PayrollController extends Controller
{
    public $successStatus = 200;
    public $authStatus = 401;
    public $failStatus = 402;
    public $ExistsStatus = 403;
    public $validationStatus = 404;

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

        $check_attendance_count = DB::table('attendances')
            ->where('employee_id', $employee_id)
            ->where('year', $year)
            ->where('month', $month)
            ->pluck('id')
            ->first();

        if(empty($check_attendance_count)){
            return response()->json(['success'=>true,'response' => 'No Attendance Found Yet!'], $this->validationStatus);
        }



//        $day_count_of_month = cal_days_in_month(CAL_GREGORIAN, $month, $year);
//
//        $total_present_string_count = countPresentThisMonth($request->year, $request->month, $request->employee_id);
//        $total_weekend = countWeekendThisMonth($request->year, $request->month);
//        $total_holiday = countHolidayThisMonth($request->year, $request->month);
//        $total_leave_approved = countLeaveApprovedThisMonth($request->year, $request->month, $request->employee_id);
//        $total_leave_pending = countLeavePendingThisMonth($request->year, $request->month, $request->employee_id);
//
//        $total_late_absent_count = countLateInfoThisMonth($request->year, $request->month, $request->employee_id);
//        $total_late = $total_late_absent_count['total_late_count'];
//        $total_late_absent_quotient = $total_late_absent_count['total_late_absent_quotient'];
//        $total_late_absent_remainder = $total_late_absent_count['total_late_absent_remainder'];
//
//        $calculation_late = $total_late - $total_late_absent_quotient;
//        $absent_day_minus = $total_present_string_count + $total_leave_approved + $calculation_late;
//
//        $total_working_day = $day_count_of_month - ($total_weekend + $total_holiday); // 31-4 = 27
//        $final_absent = ($total_working_day - $absent_day_minus); // 27 - 27 = 0
//
//        return response()->json(['success'=>true,'total_absent' => $final_absent], $this->successStatus);



        $total_absent = 0;
        $total_present_string_count = 0;
        $total_late = 0;
        $final_absent = 0;
        $day_count_of_month = cal_days_in_month(CAL_GREGORIAN, $month, $year);

        $total_present_string_count = countPresentThisMonth($request->year, $request->month, $request->employee_id);
        $total_weekend = countWeekendThisMonth($request->year, $request->month, $request->employee_id);
        $total_holiday = countHolidayThisMonth($request->year, $request->month, $request->employee_id);
        $total_leave_approved = countLeaveApprovedThisMonth($request->year, $request->month, $request->employee_id);
        //$total_leave_pending = countLeavePendingThisMonth($request->year, $request->month, $request->employee_id);

        $total_late_absent_count = countLateInfoThisMonth($request->year, $request->month, $request->employee_id);
        $total_late = $total_late_absent_count['total_late_count'];
        $total_late_absent_quotient = $total_late_absent_count['total_late_absent_quotient'];
        //$total_late_absent_remainder = $total_late_absent_count['total_late_absent_remainder'];

        $calculation_late = $total_late - $total_late_absent_quotient;
        $absent_day_minus = $total_present_string_count + $total_leave_approved + $calculation_late;

        $total_working_day = $day_count_of_month - ($total_weekend + $total_holiday); // 31-4 = 27
        $final_absent = ($total_working_day - $absent_day_minus); // 27 - 27 = 0

        return response()->json(['success'=>true,'total_absent' => $final_absent], $this->successStatus);

    }

    public function totalLateByEmployee(Request $request){
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

        // late
        $total_late = 0;

        $late = DB::table('attendances')
            ->select(DB::raw('COUNT(late) as total_late'))
            ->where('year',$request->year)
            ->where('month',$request->month)
            ->where('employee_id',$request->employee_id)
            ->where('late','!=',NULL)
            ->first();

        if($late){
            $total_late = $late->total_late;
        }

        return response()->json(['success'=>true,'total_late' => $total_late], $this->successStatus);
    }

    public function totalWeekend(Request $request){
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
        $total_weekend = countWeekendThisMonth($year, $month, $employee_id);

        return response()->json(['success'=>true,'total_weekend' => $total_weekend], $this->successStatus);
    }

    public function totalHoliday(Request $request){
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
        $total_holiday = countHolidayThisMonth($year, $month, $employee_id);

        return response()->json(['success'=>true,'total_holiday' => $total_holiday], $this->successStatus);
    }

    public function totalWorkingDay(Request $request){
        $validator = Validator::make($request->all(), [
            'year'=> 'required',
            'month'=> 'required',
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
        $total_weekend = 0;
        $total_holiday = 0;
        $deduction_day = 0;
        $day_count_of_month = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        $day = 1;

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

            $weekend = DB::table('weekends')
                ->where('year', $year)
                ->where('month', $month)
                ->where('day', $custom_today)
                ->pluck('id')
                ->first();

            if($weekend){
                $total_weekend += 1;
            }

            $holiday = DB::table('holidays')
                ->where('year', $year)
                ->where('month', $month)
                ->where('day', $custom_today)
                ->pluck('id')
                ->first();

            if($holiday){
                $total_holiday += 1;
            }

        }

        $total_working_day = $day_count_of_month - $total_weekend;
        if($total_weekend > 0){
            $deduction_day += $total_weekend;
        }
        if($total_holiday > 0){
            $deduction_day += $total_holiday;
        }
        $total_working_day = $day_count_of_month - $deduction_day;



        return response()->json(['success'=>true,'total_weekend' => $total_weekend,'total_holiday' => $total_holiday,'total_working_day' => $total_working_day], $this->successStatus);
    }


    public function employeeDetailsDepartmentWise(Request $request){
        $validator = Validator::make($request->all(), [
            'year'=> 'required',
            'month'=> 'required',
            'department_id'=> 'required',
        ]);

        if ($validator->fails()) {
            $response = [
                'success' => false,
                'data' => 'Validation Error.',
                'message' => $validator->errors()
            ];

            return response()->json($response, $this->validationStatus);
        }

        $employee_details = DB::table('employees')
            ->leftJoin('employee_office_informations','employees.id','=','employee_office_informations.employee_id')
            ->leftJoin('employee_salary_informations','employees.id','=','employee_salary_informations.employee_id')
            ->where('employee_office_informations.department_id', $request->department_id)
            ->select(
                'employees.id as employee_id',
                'employees.name as employee_name',
                'employee_office_informations.department_id',
                'employee_office_informations.designation_id',
                'employee_office_informations.card_no',
                'employee_office_informations.joining_date',
                'employee_salary_informations.gross_salary',
                'employee_salary_informations.basic',
                'employee_salary_informations.house_rent',
                'employee_salary_informations.medical',
                'employee_salary_informations.conveyance',
                'employee_salary_informations.special'
            )
            ->get();



        if(count($employee_details) > 0)
        {
            $year = $request->year;
            $month = $request->month;


            $employee_details_arr = [];
            foreach ($employee_details as $employee_detail){

                $nested_data['employee_id'] = $employee_detail->employee_id;
                $nested_data['department_id'] = $employee_detail->department_id;
                $nested_data['designation_id'] = $employee_detail->designation_id;
                $nested_data['card_no'] = $employee_detail->card_no;
                $nested_data['employee_name'] = $employee_detail->employee_name;
                $nested_data['joining_date'] = $employee_detail->joining_date;
                $nested_data['gross_salary'] = $employee_detail->gross_salary;
                $nested_data['basic'] = $employee_detail->basic;
                $nested_data['house_rent'] = $employee_detail->house_rent;
                $nested_data['medical'] = $employee_detail->medical;
                $nested_data['conveyance'] = $employee_detail->conveyance;
                $nested_data['special'] = $employee_detail->special;
                array_push($employee_details_arr,$nested_data);
            }

            return response()->json(['success'=>true,'response' => $employee_details_arr], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Employee Details Found!'], $this->failStatus);
        }
    }

    public function employeeDetailsEmployeeWise(Request $request){
        $validator = Validator::make($request->all(), [
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

        $employee_detail = DB::table('employees')
            ->leftJoin('employee_office_informations','employees.id','=','employee_office_informations.employee_id')
            ->leftJoin('employee_salary_informations','employees.id','=','employee_salary_informations.employee_id')
            ->where('employees.id', $request->employee_id)
            ->select(
                'employees.id as employee_id',
                'employees.name as employee_name',
                'employee_office_informations.department_id',
                'employee_office_informations.designation_id',
                'employee_office_informations.card_no',
                'employee_office_informations.joining_date',
                'employee_salary_informations.gross_salary',
                'employee_salary_informations.basic',
                'employee_salary_informations.house_rent',
                'employee_salary_informations.medical',
                'employee_salary_informations.conveyance',
                'employee_salary_informations.special'
            )
            ->first();



        if($employee_detail)
        {
//            $nested_data['employee_id'] = $employee_detail->employee_id;
//            $nested_data['department_id'] = $employee_detail->department_id;
//            $nested_data['designation_id'] = $employee_detail->designation_id;
//            $nested_data['designation_id'] = $employee_detail->designation_id;
//            $nested_data['card_no'] = $employee_detail->card_no;
//            $nested_data['employee_name'] = $employee_detail->employee_name;
//            $nested_data['joining_date'] = $employee_detail->joining_date;
//            $nested_data['gross_salary'] = $employee_detail->gross_salary;
//            $nested_data['basic'] = $employee_detail->basic;
//            $nested_data['house_rent'] = $employee_detail->house_rent;
//            $nested_data['medical'] = $employee_detail->medical;
//            $nested_data['conveyance'] = $employee_detail->conveyance;
//            $nested_data['special'] = $employee_detail->special;


            return response()->json(['success'=>true,'response' => $employee_detail], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Employee Details Found!'], $this->failStatus);
        }
    }

    public function payrollCreate(Request $request){
        $validator = Validator::make($request->all(), [
            'employee_id'=> 'required',
            'year'=> 'required',
            'month'=> 'required',
        ]);

        if ($validator->fails()) {
            $response = [
                'success' => false,
                'data' => 'Validation Error.',
                'message' => $validator->errors()
            ];

            return response()->json($response, $this->validationStatus);
        }

        $check_payroll_exists = Payroll::where('year',$request->year)
            ->where('month',$request->month)
            ->where('employee_id',$request->employee_id)
            ->first();
        if($check_payroll_exists){
            return response()->json(['success'=>true,'response' => 'You have already created payroll for this Employee'], $this->failStatus);
        }


        $payroll = new Payroll();
        $payroll->year=$request->year;
        $payroll->month=$request->month;
        $payroll->department_id=$request->department_id;
        $payroll->designation_id=$request->designation_id;
        $payroll->employee_id=$request->employee_id;
        $payroll->card_no=$request->card_no;
        $payroll->employee_name=$request->employee_name;
        $payroll->joining_date=$request->joining_date;
        $payroll->gross_salary=$request->gross_salary;
        $payroll->basic=$request->basic;
        $payroll->house_rent=$request->house_rent;
        $payroll->medical=$request->medical;
        $payroll->conveyance=$request->conveyance;
        $payroll->special=$request->special;
        $payroll->other_allowance=$request->other_allowance;
        $payroll->payable_gross_salary=$request->payable_gross_salary;
        $payroll->mobile_bill_deduction=$request->mobile_bill_deduction;
        $payroll->other_deduction=$request->other_deduction;
        $payroll->total_deduction_amount=$request->total_deduction_amount;
        $payroll->total_working_day=$request->total_working_day;
        $payroll->late=$request->late;
        $payroll->absent=$request->absent;
        $payroll->absent_deduction=$request->absent_deduction;
        $payroll->net_salary=$request->net_salary;
        $payroll->save();
        $insert_id = $payroll->id;


        if($insert_id)
        {
            return response()->json(['success'=>true,'response' => $payroll], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Payroll Successfully Inserted!'], $this->failStatus);
        }
    }

    public function payrollEdit(Request $request){
        $validator = Validator::make($request->all(), [
            'payroll_id'=> 'required',
            'employee_id'=> 'required',
            'year'=> 'required',
            'month'=> 'required',
        ]);

        if ($validator->fails()) {
            $response = [
                'success' => false,
                'data' => 'Validation Error.',
                'message' => $validator->errors()
            ];

            return response()->json($response, $this->failStatus);
        }

        $payroll = Payroll::find($request->payroll_id);
        $payroll->year=$request->year;
        $payroll->month=$request->month;
        $payroll->department_id=$request->department_id;
        $payroll->designation_id=$request->designation_id;
        $payroll->employee_id=$request->employee_id;
        $payroll->card_no=$request->card_no;
        $payroll->employee_name=$request->employee_name;
        $payroll->joining_date=$request->joining_date;
        $payroll->gross_salary=$request->gross_salary;
        $payroll->basic=$request->basic;
        $payroll->house_rent=$request->house_rent;
        $payroll->medical=$request->medical;
        $payroll->conveyance=$request->conveyance;
        $payroll->special=$request->special;
        $payroll->other_allowance=$request->other_allowance;
        $payroll->payable_gross_salary=$request->payable_gross_salary;
        $payroll->mobile_bill_deduction=$request->mobile_bill_deduction;
        $payroll->other_deduction=$request->other_deduction;
        $payroll->total_deduction_amount=$request->total_deduction_amount;
        $payroll->total_working_day=$request->total_working_day;
        $payroll->late=$request->late;
        $payroll->absent=$request->absent;
        $payroll->absent_deduction=$request->absent_deduction;
        $payroll->net_salary=$request->net_salary;
        $affectedRow = $payroll->save();


        if($affectedRow)
        {
            return response()->json(['success'=>true,'response' => $payroll], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Payroll Successfully Updated!'], $this->failStatus);
        }
    }

    public function payrollList(){
        $payrolls = DB::table('payrolls')
            ->join('employees','payrolls.employee_id','=','employees.id')
            ->join('departments','payrolls.department_id','=','departments.id')
            ->join('designations','payrolls.designation_id','=','designations.id')
            ->select(
                'payrolls.id',
                'payrolls.year',
                'payrolls.month',
                'payrolls.department_id',
                'departments.name as department_name',
                'payrolls.designation_id',
                'designations.name as designation_name',
                'payrolls.employee_id',
                'payrolls.card_no',
                'payrolls.employee_name',
                'payrolls.joining_date',
                'payrolls.gross_salary',
                'payrolls.basic',
                'payrolls.house_rent',
                'payrolls.medical',
                'payrolls.conveyance',
                'payrolls.special',
                'payrolls.other_allowance',
                'payrolls.payable_gross_salary',
                'payrolls.mobile_bill_deduction',
                'payrolls.other_deduction',
                'payrolls.total_deduction_amount',
                'payrolls.total_working_day',
                'payrolls.late',
                'payrolls.absent',
                'payrolls.absent_deduction',
                'payrolls.net_salary'
            )
            ->orderBy('id','desc')
            ->get();

        if($payrolls)
        {
            $success['payroll'] =  $payrolls;
            return response()->json(['success'=>true,'response' => $success], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Payrolls List Found!'], $this->failStatus);
        }
    }

    public function payslipCreate(Request $request){
        $validator = Validator::make($request->all(), [
            'employee_id'=> 'required',
            'year'=> 'required',
            'month'=> 'required',
        ]);

        if ($validator->fails()) {
            $response = [
                'success' => false,
                'data' => 'Validation Error.',
                'message' => $validator->errors()
            ];

            return response()->json($response, $this->validationStatus);
        }

        $check_payslip_exists = Payslip::where('year',$request->year)
            ->where('month',$request->month)
            ->where('employee_id',$request->employee_id)
            ->first();
        if($check_payslip_exists){
            return response()->json(['success'=>true,'response' => 'You have already created payslip for this Employee'], $this->failStatus);
        }

        $employee_detail = DB::table('employees')
            ->leftJoin('employee_office_informations','employees.id','=','employee_office_informations.employee_id')
            ->leftJoin('employee_salary_informations','employees.id','=','employee_salary_informations.employee_id')
            ->where('employees.id', $request->employee_id)
            ->select(
                'employees.id as employee_id',
                'employees.name as employee_name',
                'employee_office_informations.department_id',
                'employee_office_informations.designation_id',
                'employee_office_informations.card_no',
                'employee_office_informations.joining_date',
                'employee_salary_informations.gross_salary',
                'employee_salary_informations.basic',
                'employee_salary_informations.house_rent',
                'employee_salary_informations.medical',
                'employee_salary_informations.conveyance',
                'employee_salary_informations.special'
            )
            ->first();


        $payslip = new Payslip();
        $payslip->year=$request->year;
        $payslip->month=$request->month;
        $payslip->department_id=$employee_detail->department_id;
        $payslip->designation_id=$employee_detail->designation_id;
        $payslip->employee_id=$request->employee_id;
        $payslip->card_no=$employee_detail->card_no;
        $payslip->employee_name=$employee_detail->employee_name;
        $payslip->payment_by_user_id=$request->payment_by_user_id;
        $payslip->payment_date=date('Y-m-d');
        $payslip->payment_date_time=date('Y-m-d H:i:s');
        $payslip->payment_type=$request->payment_type;
        $payslip->account_no=isset($request->account_no) ? $request->account_no : '';
        $payslip->payment_amount=$request->payment_amount;
        $payslip->note=isset($request->note) ? $request->note : '';
        $payslip->save();
        $insert_id = $payslip->id;


        if($insert_id)
        {
            return response()->json(['success'=>true,'response' => $payslip], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Payslip Successfully Inserted!'], $this->failStatus);
        }
    }

    public function payslipList(){
        $payrolls = DB::table('payslips')
            ->join('employees','payslips.employee_id','=','employees.id')
            ->join('departments','payslips.department_id','=','departments.id')
            ->join('designations','payslips.designation_id','=','designations.id')
            ->leftJoin('users','payslips.payment_by_user_id','=','users.id')
            ->select(
                'payslips.id',
                'payslips.year',
                'payslips.month',
                'payslips.department_id',
                'departments.name as department_name',
                'payslips.designation_id',
                'designations.name as designation_name',
                'payslips.employee_id',
                'payslips.card_no',
                'payslips.employee_name',
                'payslips.payment_date',
                'payslips.payment_date_time',
                'payslips.payment_type',
                'users.name as payment_by_user_name',
                'payslips.account_no',
                'payslips.payment_amount',
                'payslips.note'
            )
            ->orderBy('id','desc')
            ->get();

        if($payrolls)
        {
            $success['payroll'] =  $payrolls;
            return response()->json(['success'=>true,'response' => $success], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Payrolls List Found!'], $this->failStatus);
        }
    }
}
