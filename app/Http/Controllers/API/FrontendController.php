<?php

namespace App\Http\Controllers\API;


use App\Http\Controllers\Controller;
use App\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use App\User;

class FrontendController extends Controller
{
    public $successStatus = 200;
    public $authStatus = 401;
    public $failStatus = 402;
    public $ExistsStatus = 403;
    public $validationStatus = 404;

    public function test1()
    {
        //return 'test';
        return response()->json(['success'=>true,'response' => 'Test Action Api!'], $this-> successStatus);
    }

    // only production user er jonno 1 bar e registration hobe
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'phone' => 'required|unique:users,phone',
            'password' => 'required|same:confirm_password',
        ]);

        if ($validator->fails()) {
            $response = [
                'success' => false,
                'data' => 'Validation Error.',
                'message' => $validator->errors()
            ];
            return response()->json($response, $this-> validationStatus);
        }

        $phn1 = (int)$request->phone;
        $check = User::where('phone',$phn1)->first();
        if (!empty($check)){
            $response = [
                'success' => false,
                'data' => 'Check Exists OR Not.',
                'message' => 'phone number already exist'
            ];
            return response()->json($response, $this-> validationStatus);
        }

        if($request->countyCodePrefix == +880){
            $phn = (int)$request->phone;
        }else{
            $phn = $request->phone;
        }
//        $slug = Str::slug($request->name,'-');
//        $drSlugCheck = User::where('slug', $slug)->first();
//        if(!empty($drSlugCheck)) {
//            $slug = $slug.'-'.Str::random(6);
//        }

        // user data
        $user = new User();
        $user->name = $request->name;
        //$user->slug = $slug;
        $user->phone = $phn;
        $user->password = Hash::make($request->password);
        $user->email = $request->email;
        $user->save();
        $user_id = $user->id;
        if($user_id){
            // create token
            $success['token'] = $user->createToken('BoiBichitra')->accessToken;
            $success['user'] =  $user;

            return response()->json(['success' => $success], $this-> successStatus);
        }else{
            return response()->json(['error' => 'No User Inserted!'], $this-> failStatus);
        }
    }

    // web good
    public function login_web(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            $response = [
                'success' => false,
                'data' => 'Validation Error.',
                'message' => $validator->errors()
            ];

            return response()->json($response, $this-> validationStatus);
        }

        //if (Auth::attempt(['email' => request('email'), 'password' => request('password')])) {
        if (Auth::attempt(['phone' => request('phone'), 'password' => request('password')])) {

            $success['success'] = true;

            $user = Auth::user();

            // create token
            $user['token'] = $user->createToken('BoiBichitra')->accessToken;

            //get roles
            $user['role'] = $user->getRoleNames()[0];
            //$user['role_id'] = $user['roles'][0]->id;
            $role_id = $user['roles'][0]->id;
            $user['permissions'] = Permission::join("role_has_permissions","role_has_permissions.permission_id","=","permissions.id")
                ->where("role_has_permissions.role_id",$role_id)
                ->get();
            unset($user['roles']);



            $success['user'] = $user;
            //$success['permissions'] = $permissions;

            return response()->json(['success' => $success], $this-> successStatus);
        } else {
            return response()->json(['error' => 'Unauthorised'], $this-> failStatus);
        }
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            $response = [
                'success' => false,
                'data' => 'Validation Error.',
                'message' => $validator->errors()
            ];

            return response()->json($response, $this-> validationStatus);
        }

        if(Auth::guard('web')->attempt(['phone' => request('phone'), 'password' => request('password')])){
            //return response()->json(['success' => 'true'], $this-> successStatus);

            $success['success'] = true;

            //$user = Auth::user();
            $user = Auth::guard('web')->user();

            if($user['store_id'] != NULL){
                $user['store_name'] = Store::where('id',$user['store_id'])->pluck('name')->first();
            }else{
                $user['store_name'] = NULL;
            }

            // create token
            $user['token'] = $user->createToken('BoiBichitra')->accessToken;

            //get roles
            $user['role'] = $user->getRoleNames()[0];
            //$user['role_id'] = $user['roles'][0]->id;
            $role_id = $user['roles'][0]->id;
            $user['permissions'] = Permission::join("role_has_permissions","role_has_permissions.permission_id","=","permissions.id")
                ->where("role_has_permissions.role_id",$role_id)
                ->get();
            unset($user['roles']);



            $success['user'] = $user;
            //$success['permissions'] = $permissions;

            return response()->json(['success' => $success], $this-> successStatus);
        }else{
            return response()->json(['error' => 'Unauthorised'], $this-> failStatus);
        }
    }
}
