<?php

namespace App\Http\Controllers\API;

use App\Employee;
use App\EmployeeOfficeInformation;
use App\EmployeeSalaryInformation;
use App\Helpers\UserInfo;
use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\Facades\Image;

class EmployeeController extends Controller
{
    public $successStatus = 200;
    public $authStatus = 401;
    public $failStatus = 402;
    public $ExistsStatus = 403;
    public $validationStatus = 404;

    // Employee
    public function employeeList(){
        $employees = DB::table('employees')
            ->join('warehouses','employees.warehouse_id','warehouses.id')
            ->leftJoin('stores','employees.store_id','stores.id')
            ->select(
                'employees.id',
                'employees.name',
                'employees.email',
                'employees.phone',
                'employees.gender',
                'employees.date_of_birth',
                'employees.blood_group',
                'employees.national_id',
                'employees.marital_status',
                'employees.present_address',
                'employees.permanent_address',
                'employees.status',
                'employees.image',
                'warehouses.id as warehouse_id',
                'warehouses.name as warehouse_name',
                'stores.id as store_id',
                'stores.name as store_name'
            )
            ->orderBy('id','desc')
            ->get();

        if($employees)
        {
            $success['employees'] =  $employees;
            return response()->json(['success'=>true,'response' => $success], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Employees List Found!'], $this->failStatus);
        }
    }

    public function employeeListActive(){
        $employees = DB::table('employees')
            ->join('warehouses','employees.warehouse_id','warehouses.id')
            ->leftJoin('stores','employees.store_id','stores.id')
            ->where('employees.status',1)
            ->select(
                'employees.id',
                'employees.name',
                'employees.email',
                'employees.phone',
                'employees.gender',
                'employees.date_of_birth',
                'employees.blood_group',
                'employees.national_id',
                'employees.marital_status',
                'employees.present_address',
                'employees.permanent_address',
                'employees.status',
                'employees.image',
                'warehouses.id as warehouse_id',
                'warehouses.name as warehouse_name',
                'stores.id as store_id',
                'stores.name as store_name'
            )
            ->orderBy('id','desc')
            ->get();

        if($employees)
        {
            $success['employees'] =  $employees;
            return response()->json(['success'=>true,'response' => $success], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Employees List Found!'], $this->failStatus);
        }
    }

    public function employeeCreate(Request $request){

        $validator = Validator::make($request->all(), [
            //'name' => 'required|unique:employees,name',
            //'email'=> 'required',
            'phone'=> 'required',
            'status'=> 'required',
            'warehouse_id'=> 'required',
        ]);

        if ($validator->fails()) {
            $response = [
                'success' => false,
                'data' => 'Validation Error.',
                'message' => $validator->errors()
            ];

            return response()->json($response, $this-> validationStatus);
        }


        $employee = new Employee();
        $employee->name = $request->name;
        $employee->email = $request->email;
        $employee->phone = $request->phone;
        $employee->gender = $request->gender;
        $employee->date_of_birth = $request->date_of_birth;
        $employee->blood_group = $request->blood_group;
        $employee->national_id = $request->national_id;
        $employee->marital_status = $request->marital_status;
        $employee->present_address = $request->present_address;
        $employee->permanent_address = $request->permanent_address;
        $employee->status = $request->status;
        $employee->warehouse_id = $request->warehouse_id;
        $employee->store_id = $request->store_id;
        $employee->save();
        $insert_id = $employee->id;

        if($insert_id){
            $user_data['name'] = $request->name;
            $user_data['email'] = $request->email;
            $user_data['phone'] = $request->phone;
            $user_data['password'] = Hash::make(123456);
            //$user_data['employee_id'] = $insert_id;
            $user = User::create($user_data);
            $user->employee_id=$request->employee_id;
            $user->save();
            // first create employee role, then bellow assignRole code enable
            $user->assignRole('employee');

            $text = "Dear ".$request->name." Sir, Your Username is ".$request->phone." and password is: 123456";
            UserInfo::smsAPI("88".$request->phone,$text);

            return response()->json(['success'=>true,'response' => $employee], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'Leave Employee Not Created Successfully!'], $this->failStatus);
        }
    }

    public function employeeEdit(Request $request){

        $validator = Validator::make($request->all(), [
            'employee_id'=> 'required',
            //'name' => 'required|unique:employees,name,'.$request->employee_id,
            //'email'=> 'required',
            'phone'=> 'required',
            'status'=> 'required',
            'warehouse_id'=> 'required',
        ]);

        if ($validator->fails()) {
            $response = [
                'success' => false,
                'data' => 'Validation Error.',
                'message' => $validator->errors()
            ];

            return response()->json($response, $this->validationStatus);
        }

        $check_exists_employees = DB::table("employees")->where('id',$request->employee_id)->pluck('id')->first();
        if($check_exists_employees == null){
            return response()->json(['success'=>false,'response'=>'No Employee Found!'], $this->failStatus);
        }

        $employee = Employee::find($request->employee_id);
        $employee->name = $request->name;
        $employee->email = $request->email;
        $employee->phone = $request->phone;
        $employee->gender = $request->gender;
        $employee->date_of_birth = $request->date_of_birth;
        $employee->blood_group = $request->blood_group;
        $employee->national_id = $request->national_id;
        $employee->marital_status = $request->marital_status;
        $employee->present_address = $request->present_address;
        $employee->permanent_address = $request->permanent_address;
        $employee->status = $request->status;
        $employee->warehouse_id = $request->warehouse_id;
        $employee->store_id = $request->store_id;
        $update_leave_employee = $employee->save();

        if($update_leave_employee){

//            $user_data['name'] = $request->name;
//            $user_data['email'] = $request->email;
//            $user_data['phone'] = $request->phone;
//            $user_data['password'] = Hash::make(123456);
//            //$user_data['employee_id'] = $request->employee_id;
//            $user = User::create($user_data);
//            $user->employee_id=$request->employee_id;
//            $user->save();
//            // first create employee role, then bellow assignRole code enable
//            $user->assignRole('employee');

            return response()->json(['success'=>true,'response' => $employee], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'Employee Not Created Successfully!'], $this->failStatus);
        }
    }

    public function employeeDelete(Request $request){
        $check_exists_employees = DB::table("employees")->where('id',$request->employee_id)->pluck('id')->first();
        if($check_exists_employees == null){
            return response()->json(['success'=>false,'response'=>'No Employee Found!'], $this->failStatus);
        }

        $soft_delete_leave_employee = Employee::find($request->employee_id);
        $soft_delete_leave_employee->status=0;
        $affected_row = $soft_delete_leave_employee->update();
        if($affected_row)
        {
            return response()->json(['success'=>true,'response' => 'Employee Successfully Soft Deleted!'], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Employee Deleted!'], $this->failStatus);
        }
    }

//    public function employeeImage(Request $request)
//    {
//        $employee=Employee::find($request->employee_id);
//        $base64_image_propic = $request->employee_img;
//        //return response()->json(['response' => $base64_image_propic], $this-> successStatus);
//
//        $data = $request->employee_img;
//        $pos = strpos($data, ';');
//        $type = explode(':', substr($data, 0, $pos))[1];
//        $type1 = explode('/', $type)[1];
//
//        if (preg_match('/^data:image\/(\w+);base64,/', $base64_image_propic)) {
//            $data = substr($base64_image_propic, strpos($base64_image_propic, ',') + 1);
//            $data = base64_decode($data);
//
//            $currentDate = Carbon::now()->toDateString();
//            $imagename = $currentDate . '-' . uniqid() . 'employee_pic.'.$type1 ;
//
//            // delete old image.....
//            if(Storage::disk('public')->exists('uploads/employees/'.$employee->image))
//            {
//                Storage::disk('public')->delete('uploads/employees/'.$employee->image);
//
//            }
//
//            // resize image for service category and upload
//            //$data = Image::make($data)->resize(100, 100)->save($data->getClientOriginalExtension());
//
//            // store image
//            Storage::disk('public')->put("uploads/employees/". $imagename, $data);
//
//
//            // update image db
//            $employee->image = $imagename;
//            $employee->update();
//
//            $success['employee'] = $employee;
//            return response()->json(['response' => $success], $this-> successStatus);
//
//        }else{
//            return response()->json(['response'=>'failed'], $this-> failStatus);
//        }
//
//    }

    public function employeeImage(Request $request)
    {
        $employee=Employee::find($request->employee_id);
        $image = $request->file('employee_img');
        if (isset($image)) {
            //make unique name for image
            $currentDate = Carbon::now()->toDateString();
            $imagename = $currentDate.'-'.uniqid().'.'.$image->getClientOriginalExtension();

            // delete old image.....
            if(Storage::disk('public')->exists('uploads/employees/'.$employee->image))
            {
                Storage::disk('public')->delete('uploads/employees/'.$employee->image);

            }

//            resize image for hospital and upload
            $proImage = Image::make($image)->resize(100, 100)->save($image->getClientOriginalExtension());
            Storage::disk('public')->put('uploads/employees/'. $imagename, $proImage);

            // update image db
            $employee->image = $imagename;
            $employee->update();

            $success['employee'] = $employee;
            return response()->json(['response' => $success], $this-> successStatus);

        }else{
            return response()->json(['response'=>'failed'], $this-> failStatus);
        }

    }

    // employee office information
    public function employeeOfficeInformationList(){
        $employee_office_informations = DB::table('employee_office_informations')
            ->join('employees','employee_office_informations.employee_id','=','employees.id')
            ->join('departments','employee_office_informations.department_id','=','departments.id')
            ->join('designations','employee_office_informations.designation_id','=','designations.id')
            ->select('employee_office_informations.id','employee_office_informations.employee_type','employee_office_informations.card_no','employee_office_informations.joining_date','employee_office_informations.resignation_date','employee_office_informations.last_office_date','employee_office_informations.status','employee_office_informations.employee_id','employees.name as employee_name','employee_office_informations.department_id','departments.name as department_name','employee_office_informations.designation_id','designations.name as designation_name')
            ->orderBy('id','desc')
            ->get();

        if($employee_office_informations)
        {
            $success['employee_office_informations'] =  $employee_office_informations;
            return response()->json(['success'=>true,'response' => $success], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Employee Office Informations List Found!'], $this->failStatus);
        }
    }

    public function employeeOfficeInformationCreate(Request $request){

        $validator = Validator::make($request->all(), [
            'employee_id'=> 'required',
            'employee_type'=> 'required',
            'card_no'=> 'required',
            'department_id'=> 'required',
            'designation_id'=> 'required',
        ]);

        if ($validator->fails()) {
            $response = [
                'success' => false,
                'data' => 'Validation Error.',
                'message' => $validator->errors()
            ];

            return response()->json($response, $this-> validationStatus);
        }


        $employee_office_information = new EmployeeOfficeInformation();
        $employee_office_information->employee_id = $request->employee_id;
        $employee_office_information->employee_type = $request->employee_type;
        $employee_office_information->card_no = $request->card_no;
        $employee_office_information->department_id = $request->department_id;
        $employee_office_information->designation_id = $request->designation_id;
        $employee_office_information->joining_date = $request->joining_date;
        $employee_office_information->resignation_date = $request->resignation_date;
        $employee_office_information->last_office_date = $request->last_office_date;
        $employee_office_information->status = $request->status;
        $employee_office_information->save();
        $insert_id = $employee_office_information->id;

        if($insert_id){
            return response()->json(['success'=>true,'response' => $employee_office_information], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'Leave Employee Office Information Not Created Successfully!'], $this->failStatus);
        }
    }

    public function employeeOfficeInformationEdit(Request $request){

        $validator = Validator::make($request->all(), [
            'employee_office_information_id'=> 'required',
            'employee_id'=> 'required',
            'employee_type'=> 'required',
            'card_no'=> 'required',
            'department_id'=> 'required',
            'designation_id'=> 'required',
        ]);

        if ($validator->fails()) {
            $response = [
                'success' => false,
                'data' => 'Validation Error.',
                'message' => $validator->errors()
            ];

            return response()->json($response, $this->validationStatus);
        }

        $check_exists_employee_office_informations = DB::table("employee_office_informations")->where('id',$request->employee_office_information_id)->pluck('id')->first();
        if($check_exists_employee_office_informations == null){
            return response()->json(['success'=>false,'response'=>'No Employee Office Information Found!'], $this->failStatus);
        }

        $employee_office_information = EmployeeOfficeInformation::find($request->employee_office_information_id);
        $employee_office_information->employee_id = $request->employee_id;
        $employee_office_information->employee_type = $request->employee_type;
        $employee_office_information->card_no = $request->card_no;
        $employee_office_information->department_id = $request->department_id;
        $employee_office_information->designation_id = $request->designation_id;
        $employee_office_information->joining_date = $request->joining_date;
        $employee_office_information->resignation_date = $request->resignation_date;
        $employee_office_information->last_office_date = $request->last_office_date;
        $employee_office_information->status = $request->status;
        $update_employee_office_information = $employee_office_information->save();

        if($update_employee_office_information){
            return response()->json(['success'=>true,'response' => $employee_office_information], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'Employee Office Information Not Created Successfully!'], $this->failStatus);
        }
    }

    public function employeeOfficeInformationDelete(Request $request){
        $check_exists_employee_office_informations = DB::table("employee_office_informations")->where('id',$request->employee_office_information_id)->pluck('id')->first();
        if($check_exists_employee_office_informations == null){
            return response()->json(['success'=>false,'response'=>'No Employee Office Information Found!'], $this->failStatus);
        }

        $soft_delete_employee_office_information = EmployeeOfficeInformation::find($request->employee_office_information_id);
        $soft_delete_employee_office_information->status=0;
        $affected_row = $soft_delete_employee_office_information->update();
        if($affected_row)
        {
            return response()->json(['success'=>true,'response' => 'Employee Office Information Successfully Soft Deleted!'], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Employee Office Information Deleted!'], $this->failStatus);
        }
    }

    // employee salary information
    public function employeeSalaryInformationList(){
        $employee_salary_informations = DB::table('employee_salary_informations')
            ->join('employees','employee_salary_informations.employee_id','=','employees.id')
            ->select('employee_salary_informations.id','employee_salary_informations.gross_salary','employee_salary_informations.basic','employee_salary_informations.house_rent','employee_salary_informations.medical','employee_salary_informations.conveyance','employee_salary_informations.special','employee_salary_informations.status','employee_salary_informations.id as employee_id','employees.name as employee_name')
            ->orderBy('id','desc')
            ->get();

        if($employee_salary_informations)
        {
            $success['employee_salary_informations'] =  $employee_salary_informations;
            return response()->json(['success'=>true,'response' => $success], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Employee Salary Informations List Found!'], $this->failStatus);
        }
    }

    public function employeeSalaryInformationCreate(Request $request){

        $validator = Validator::make($request->all(), [
            'employee_id'=> 'required',
            'gross_salary'=> 'required',
        ]);

        if ($validator->fails()) {
            $response = [
                'success' => false,
                'data' => 'Validation Error.',
                'message' => $validator->errors()
            ];

            return response()->json($response, $this-> validationStatus);
        }


        $employee_salary_information = new EmployeeSalaryInformation();
        $employee_salary_information->employee_id = $request->employee_id;
        $employee_salary_information->gross_salary = $request->gross_salary;
        $employee_salary_information->basic = $request->basic;
        $employee_salary_information->house_rent = $request->house_rent;
        $employee_salary_information->medical = $request->medical;
        $employee_salary_information->conveyance = $request->conveyance;
        $employee_salary_information->special = $request->special;
        $employee_salary_information->status = $request->status;
        $employee_salary_information->save();
        $insert_id = $employee_salary_information->id;

        if($insert_id){
            return response()->json(['success'=>true,'response' => $employee_salary_information], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'Leave Employee Salary Information Not Created Successfully!'], $this->failStatus);
        }
    }

    public function employeesalaryInformationEdit(Request $request){

        $validator = Validator::make($request->all(), [
            'employee_salary_information_id'=> 'required',
            'employee_id'=> 'required',
            'gross_salary'=> 'required',
        ]);

        if ($validator->fails()) {
            $response = [
                'success' => false,
                'data' => 'Validation Error.',
                'message' => $validator->errors()
            ];

            return response()->json($response, $this->validationStatus);
        }

        $check_exists_employee_salary_informations = DB::table("employee_salary_informations")->where('id',$request->employee_salary_information_id)->pluck('id')->first();
        if($check_exists_employee_salary_informations == null){
            return response()->json(['success'=>false,'response'=>'No Employee salary Information Found!'], $this->failStatus);
        }

        $employee_salary_information = EmployeesalaryInformation::find($request->employee_salary_information_id);
        $employee_salary_information->employee_id = $request->employee_id;
        $employee_salary_information->gross_salary = $request->gross_salary;
        $employee_salary_information->basic = $request->basic;
        $employee_salary_information->house_rent = $request->house_rent;
        $employee_salary_information->medical = $request->medical;
        $employee_salary_information->conveyance = $request->conveyance;
        $employee_salary_information->special = $request->special;
        $employee_salary_information->status = $request->status;
        $update_employee_salary_information = $employee_salary_information->save();

        if($update_employee_salary_information){
            return response()->json(['success'=>true,'response' => $employee_salary_information], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'Employee Salary Information Not Created Successfully!'], $this->failStatus);
        }
    }

    public function employeesalaryInformationDelete(Request $request){
        $check_exists_employee_salary_informations = DB::table("employee_salary_informations")->where('id',$request->employee_salary_information_id)->pluck('id')->first();
        if($check_exists_employee_salary_informations == null){
            return response()->json(['success'=>false,'response'=>'No Employee Salary Information Found!'], $this->failStatus);
        }

        $soft_delete_employee_salary_information = EmployeesalaryInformation::find($request->employee_salary_information_id);
        $soft_delete_employee_salary_information->status=0;
        $affected_row = $soft_delete_employee_salary_information->update();
        if($affected_row)
        {
            return response()->json(['success'=>true,'response' => 'Employee Salary Information Successfully Soft Deleted!'], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Employee Salary Information Deleted!'], $this->failStatus);
        }
    }
}
