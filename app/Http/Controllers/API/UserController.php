<?php

namespace App\Http\Controllers\API;

use App\Helpers\APIHelpers;
use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public $successStatus = 200;
    public $authStatus = 401;
    public $failStatus = 402;
    public $ExistsStatus = 403;
    public $validationStatus = 404;

    public function userList(){
        try {
            $users = DB::table("users")
                ->join('model_has_roles','model_has_roles.model_id','users.id')
                ->join('roles','model_has_roles.role_id','roles.id')
                ->leftJoin('warehouses','users.warehouse_id','warehouses.id')
                ->leftJoin('stores','users.store_id','stores.id')
                ->where('roles.id','!=',8)
                ->select('users.id','users.name','users.phone','users.email','users.status','roles.name as role','warehouses.id as warehouse_id','warehouses.name as warehouse_name','stores.id as store_id','stores.name as store_name')
                ->get();
            if($users === null){
                $response = APIHelpers::createAPIResponse(true,404,'No User Found.',null);
                return response()->json($response,404);
            }else{
                $response = APIHelpers::createAPIResponse(false,200,'',$users);
                return response()->json($response,200);
            }
        } catch (\Exception $e) {
            //return $e->getMessage();
            $response = APIHelpers::createAPIResponse(false,500,'Internal Server Error.',null);
            return response()->json($response,500);
        }
    }

    public function userCreate(Request $request){
        try {
//            $validator = Validator::make($request->all(), [
//                'name' => 'required',
//                'phone' => 'required|unique:users,phone',
//                'password' => 'required|same:confirm_password',
//                'roles' => 'required',
//                'status' => 'required',
//                'warehouse_id' => 'required',
//            ]);
//
//            if ($validator->fails()) {
//                $response = [
//                    'success' => false,
//                    'data' => 'Validation Error.',
//                    'message' => $validator->errors()
//                ];
//
//                return response()->json($response, $this->validationStatus);
//            }

            // required and unique
            $validator = Validator::make($request->all(), [
                'name' => 'required',
                'phone' => 'unique:users,phone',
                'password' => 'required|same:confirm_password',
                'roles' => 'required',
                'status'=> 'required',
                'warehouse_id'=> 'required',
            ]);

            if ($validator->fails()) {
                $response = APIHelpers::createAPIResponse(true,400,$validator->errors(),null);
                return response()->json($response,400);
            }

            $input = $request->all();
            $input['password'] = Hash::make($input['password']);

            $user = User::create($input);
            $user->assignRole($request->input('roles'));

            $response = APIHelpers::createAPIResponse(false,201,'User Added Successfully.',null);
            return response()->json($response,201);
        } catch (\Exception $e) {
            //return $e->getMessage();
            $response = APIHelpers::createAPIResponse(false,500,'Internal Server Error.',null);
            return response()->json($response,500);
        }
    }

    public function userDetails(Request $request){
        try {
            $check_exists_party = DB::table("users")->where('id',$request->user_id)->pluck('id')->first();
            if($check_exists_party == null){
                $response = APIHelpers::createAPIResponse(true,404,'No User Found.',null);
                return response()->json($response,404);
            }

            //$users = DB::table("users")->where('id',$request->user_id)->select('id','name','phone','email','status')->first();
            $users = DB::table("users")
                ->join('model_has_roles','model_has_roles.model_id','users.id')
                ->join('roles','model_has_roles.role_id','roles.id')
                ->leftJoin('warehouses','users.warehouse_id','warehouses.id')
                ->leftJoin('stores','users.store_id','stores.id')
                ->where('users.id',$request->user_id)
                ->select('users.id','users.name','users.phone','users.email','users.status','roles.name as role','warehouses.id as warehouse_id','warehouses.name as warehouse_name','stores.id as store_id','stores.name as store_name')
                ->first();


            if($users === null){
                $response = APIHelpers::createAPIResponse(true,404,'No User Found.',null);
                return response()->json($response,404);
            }else{
                $response = APIHelpers::createAPIResponse(false,200,'',$users);
                return response()->json($response,200);
            }
        } catch (\Exception $e) {
            //return $e->getMessage();
            $response = APIHelpers::createAPIResponse(false,500,'Internal Server Error.',null);
            return response()->json($response,500);
        }
    }

    public function userEdit(Request $request){
        try {
            $input = $request->all();

            if(!empty($input['password'])){
                $validator = Validator::make($request->all(), [
                    'user_id' => 'required',
                    'name' => 'required',
                    'phone' => 'required|unique:users,phone,'.$request->user_id,
                    'password' => 'same:confirm_password',
                    'roles' => 'required',
                    'status' => 'required',
                    'warehouse_id' => 'required',
                ]);
            }else{
                $validator = Validator::make($request->all(), [
                    'user_id' => 'required',
                    'name' => 'required',
                    'phone' => 'required|unique:users,phone,'.$request->user_id,
                    'roles' => 'required',
                    'status' => 'required',
                    'warehouse_id' => 'required',
                ]);
            }



            if ($validator->fails()) {
                $response = APIHelpers::createAPIResponse(true,400,$validator->errors(),null);
                return response()->json($response,400);
            }

            //$check_exists_party = DB::table("users")->where('id',$request->user_id)->pluck('id')->first();
            $check_exists_user = DB::table("users")->where('id',$request->user_id)->first();

            if($check_exists_user){
                if(!empty($input['password'])){
                    $input['password'] = Hash::make($input['password']);
                }else{
                    //$input = array_except($input,array('password'));
                    //$input = Arr::get($input,array('password'));
                    $input['password'] = $check_exists_user->password;
                }

                $user = User::find($request->user_id);
                $update_user = $user->update($input);
                DB::table('model_has_roles')->where('model_id',$request->user_id)->delete();

                $user->assignRole($request->input('roles'));

                if($update_user){
                    $response = APIHelpers::createAPIResponse(false,200,'User Updated Successfully.',null);
                    return response()->json($response,200);
                }else{
                    $response = APIHelpers::createAPIResponse(true,400,'User Updated Failed.',null);
                    return response()->json($response,400);
                }
            }else{
                $response = APIHelpers::createAPIResponse(true,404,'No User Found.',null);
                return response()->json($response,404);
            }
        } catch (\Exception $e) {
            //return $e->getMessage();
            $response = APIHelpers::createAPIResponse(false,500,'Internal Server Error.',null);
            return response()->json($response,500);
        }
    }

    public function userDelete(Request $request){
        try {
            $check_exists_party = DB::table("users")->where('id',$request->user_id)->pluck('id')->first();
            if($check_exists_party == null){
                $response = APIHelpers::createAPIResponse(true,404,'No User Found.',null);
                return response()->json($response,404);
            }

            //$delete_user = DB::table("users")->where('id',$request->user_id)->delete();
            $soft_delete_user = User::find($request->user_id);
            $soft_delete_user->status=0;
            $affected_row = $soft_delete_user->update();
            if($affected_row)
            {
                $response = APIHelpers::createAPIResponse(false,200,'User Successfully Soft Deleted.',null);
                return response()->json($response,200);
            }else{
                $response = APIHelpers::createAPIResponse(true,400,'User Updated Failed.',null);
                return response()->json($response,400);
            }
        } catch (\Exception $e) {
            //return $e->getMessage();
            $response = APIHelpers::createAPIResponse(false,500,'Internal Server Error.',null);
            return response()->json($response,500);
        }
    }

    public function changedPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'old_password' => 'required',
            'password' => 'required|confirmed',
        ]);

        if ($validator->fails()) {
            $response = [
                'success' => false,
                'data' => 'Validation Error.',
                'message' => $validator->errors()
            ];
            return response()->json($response, $this->validationStatus);
        }

        $hashedPassword = Auth::user()->password;

        if (Hash::check($request->old_password, $hashedPassword)) {
            if (!Hash::check($request->password, $hashedPassword)) {
                $user = \App\User::find(Auth::id());
                $user->password = Hash::make($request->password);
                $user->save();
                return response()->json(['success'=>true,'response' => 'Password Updated Successfully'], $this-> successStatus);
            } else {
                return response()->json(['success'=>false,'response'=>'New password cannot be the same as old password.'], $this->failStatus);
            }
        } else {
            return response()->json(['success'=>false,'response'=>'Current password not match.'], $this->failStatus);
        }

    }
}
