<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\User;
use App\VerificationCode;
use App\Helpers\UserInfo;
use Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public $successStatus = 200;
    public $failStatus = 401;
    public $validationStatus = 404;

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'phone' => 'required',
            'password' => 'required',
            'role_id' => 'required',
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
        $slug = Str::slug($request->name,'-');
        $drSlugCheck = User::where('slug', $slug)->first();
        if(!empty($drSlugCheck)) {
            $slug = $slug.'-'.Str::random(6);
        }

        // user data
        $user = new User();
        $user->name = $request->name;
        $user->slug = $slug;
        $user->country_code = $request->countyCodePrefix;
        $user->phone = $phn;
        $user->password = Hash::make($request->password);
        $user->role_id = $request->role_id;
        $user->sign_up_type = 2; // 1=web, 2=phone
        $user->status = 0;
        $user->save();
        $user_id = $user->id;
        if($user_id){
            // create token
            $success['token'] = $user->createToken('PreventCare')->accessToken;
            $success['user'] =  $user;


            // same api
            // verification table
            $verification = VerificationCode::where('phone',$user->phone)->first();
            if (!empty($verification)){
                $verification->delete();
            }
            $verCode = new VerificationCode();
            $verCode->phone = $user->phone;
            $verCode->code = mt_rand(1111,9999);
            $verCode->status = 0;
            $verCode->save();
            $insert_id = $verCode->id;
            if($insert_id){
                $text = "Dear ".$user->name.", Your Prevent Care OTP is ".$verCode->code;
                UserInfo::smsAPI("88".$verCode->phone,$text);

                $success['code'] = $verCode->code;

                return response()->json(['success' => $success], $this-> successStatus);
            }else{
                return response()->json(['error' => 'Unauthorised'], $this-> failStatus);
            }



            //return response()->json(['success' => $success], $this-> successStatus);
        }else{
            return response()->json(['error' => 'Unauthorised'], $this-> failStatus);
        }
    }

    // another api
    public function getVerificationCode(Request $request){
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
        ]);

        if ($validator->fails()) {
            $response = [
                'success' => false,
                'data' => 'Validation Error.',
                'message' => $validator->errors()
            ];
            return response()->json($response, $this-> validationStatus);
        }

        $user = User::find($request->user_id);

        // verification table
        $verification = VerificationCode::where('phone',$user->phone)->first();
        if (!empty($verification)){
            $verification->delete();
        }
        $verCode = new VerificationCode();
        $verCode->phone = $user->phone;
        $verCode->code = mt_rand(1111,9999);
        $verCode->status = 0;
        $verCode->save();
        $insert_id = $verCode->id;
        if($insert_id){
            $text = "Dear ".$user->name.", Your Prevent Care OTP is ".$verCode->code;
            UserInfo::smsAPI("88".$verCode->phone,$text);

            $success['code'] = $verCode->code;
            $success['user'] = $user;

            return response()->json(['success' => $success], $this-> successStatus);
        }else{
            return response()->json(['error' => 'Unauthorised'], $this-> failStatus);
        }
    }

    public function verification(Request $request){
        $validator = Validator::make($request->all(), [
            'code' => 'required',
            'phone' => 'required',
        ]);

        if ($validator->fails()) {
            $response = [
                'success' => false,
                'data' => 'Validation Error.',
                'message' => $validator->errors()
            ];

            return response()->json($response, $this-> validationStatus);
        }

        $check = VerificationCode::where('code',$request->code)->where('phone',$request->phone)->where('status',0)->first();
        if (!empty($check)) {
            // verification update
            $check->status = 1;
            $check->update();

            // user update
            $user = User::where('phone',$request->phone)->first();
            $user->status = 1;
            $user->save();

            // get token
            $token = DB::table('oauth_access_tokens')
                ->where('user_id',$user->id)
                ->latest()
                ->pluck('id')
                ->first();

            $success['token'] = $token;
            $success['user'] = $user;

            return response()->json(['success' => $success], $this-> successStatus);
        }else{
            return response()->json(['error' => 'Unauthorised'], $this-> failStatus);
        }

    }

    public function login()
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
            $user = Auth::user();

            $success['success'] = true;
            // create token
            $success['token'] = $user->createToken('PreventCare')->accessToken;
            $success['user'] = $user;

            return response()->json(['success' => $success], $this-> successStatus);
        } else {
            return response()->json(['error' => 'Unauthorised'], $this-> failStatus);
        }
    }
}
