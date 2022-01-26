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
use \Firebase\JWT\JWT;

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

        //$privateKey = "-----BEGIN PRIVATE KEY-----\nMIIEvwIBADANBgkqhkiG9w0BAQEFAASCBKkwggSlAgEAAoIBAQDWnOBfWljPRqyy\ne5bqj3FL9gW5kBe5lniyfD/mvFnv/DKncYLitMitI7YsIvx0H4VGW1Rllxo5UnXR\nzmLEfZAUanDpmlHS9bUAsi/lR75ClGW2kl/A23bB2b4ICHf0aV6gD+EIB9YLQexg\nc4YXJdmttbuFCv4/CXHzRHmLdF5wWeAeWsaG1+LVLmDdgko4JzGSiIsfe7jg3Asc\nAs7vG5c0TS4dOMD4r7tT30C4TAZWshBEYWUR58eyZaKXbc4ab31f4lKLcbngHCIc\nbawgRJadqbhKBL2FWm35/KL+XWy4FgHhY6pjgWFzTNtMw+j306B+0IpaQNf/mks8\nyTxskX3nAgMBAAECggEABElb3euzDGjP+DyptgOpcqf2U0+Cec18mawLprMqZLW3\n2UpWH+sWewbUk6sbOcKLae1XETRkbLKt8cPaiywq3Y2GtdPEQJ9xvxLQDBdTwIaw\nRWZFDVgU1ihgOE7a/oHARxgqGXv2lYD6lK6aBgpWf7a6iRzAGUg6A27hspxfaoUH\nqGzFCzAJeqdcDIsSXHEZW+k1+x9us5aC2J9lB4wyuaqIqktFjPJCdswPx9xsLr2X\nZn6SPYiTGRTgZnew1Yk/zsvjCaT8lsZja6auq7IclkCj//n5EnLmhtaD20V37eaV\n1JEAnNo1F1VE66JLf6nMPY+WauRESAjVHZFPqAY7EQKBgQD5KRoihM/aZXEx0x6P\n6Y9Wv7gk8cGL23Pb1snrDKFtmXdeXfbH+sz8mOgq9Y1Q95eHPpNpG4XvARC7FQoU\nBU0fkVk3mXmY2TwxLMObJJzq4zOQCAGIZ0GrKfYh94PgqZ5FFiaPVQ2edVCEOE3Y\nK54zzkdC6a0sTWp1Eex6EPlv2QKBgQDcgQA07tmNe+VVZCHW9IqYU/lLUgWzLXUB\n30iajrqln0hZqDFhL/0RDcifQs2QLnN928NHoJBEttAExwQ66xIRIn6Ts/gVVt3y\nfjbQLPsfoTBKKoyu3+k+EoLACrKW76mpAwI0UDLYAgWtiljmXguXcvcxcTmsz3/9\nmBofxjODvwKBgQDM/LHRwG65AUh1c3nrcH5LIoQ/cN6JT80sCrQou0V8RAxfCPNl\nZ8OJ9crcvRS8jlaOID9q9AfmsHuxTwfxnMLsu8oo4g2WYPMSif+L/j1TSgU79Do+\nnKT8SxOCsn4/MY1SzXx/47vGqEHL5f61YH1Rpd4fAN1GW5LAKjTh4GE3UQKBgQCI\nNF8OU2Oq05crkfidMNzTjzt0XSwMK84U4/mTDwsX9zXXu98Uq3HksOD2D2uu3iKU\n4cTUX8f9yfbgnJZuVnoIf4g0cHyTod7jRTdSjBZqyURs66+O7dzDbOe6/GCof04L\nikI4Ujm12DntooGbewgp+ufacJgxuNLUsLmiWunDPQKBgQDIOJQDES/WstOGmTSl\nRxiOxMmkcoxyNrbnTVgRnJJsMTvjo9HzU71ZnGDn5RP2GCK01JaVd/WBZH7HDOLU\nhTSqPnSMX/e2dJkT4IIpUZdhcXaLJDVkf3GDqpEGE2rUv0RTLBnb3MTkDwA/n7rf\nnuvn61fUK/FXONx/h52JZOZgig==\n-----END PRIVATE KEY-----\n";

        if(Auth::guard('web')->attempt(['phone' => request('phone'), 'password' => request('password')])){
            //return response()->json(['success' => 'true'], $this-> successStatus);

            $success['success'] = true;

            //$user = Auth::user();
            $user = Auth::guard('web')->user();

            $user['warehouse_id'] = (int) $user['warehouse_id'];
            $user['store_id'] = (int) $user['store_id'];
            $user['party_id'] = (int) $user['party_id'];
            $user['employee_id'] = (int) $user['employee_id'];
            $user['status'] = (int) $user['status'];

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
//            $user['permissions'] = Permission::join("role_has_permissions","role_has_permissions.permission_id","=","permissions.id")
//                ->where("role_has_permissions.role_id",$role_id)
//                ->get();
            $permissions = Permission::join("role_has_permissions","role_has_permissions.permission_id","=","permissions.id")
                ->select(
                    'permissions.id',
                    'permissions.name',
                    'permissions.guard_name',
                    //'permissions.status',
                    'permissions.created_at',
                    'permissions.updated_at',
                    'role_has_permissions.permission_id',
                    'role_has_permissions.role_id'
                )
                ->where("role_has_permissions.role_id",$role_id)
                ->get();

            $permission_data = [];
            foreach($permissions as $per){
                $nested_data['id'] = $per->id;
                $nested_data['name'] = $per->name;
                $nested_data['guard_name'] = $per->guard_name;
                //$nested_data['status'] = (int) $per->status;
                $nested_data['created_at'] = $per->created_at;
                $nested_data['updated_at'] = $per->updated_at;
                $nested_data['permission_id'] = (int) $per->permission_id;
                $nested_data['role_id'] = (int) $per->role_id;

                array_push($permission_data, $nested_data);
            }


            $user['permissions'] = $permission_data;
            unset($user['roles']);



            $success['user'] = $user;
            //$success['permissions'] = $permissions;



//            $payload = array(
//                "aud" => "https://identitytoolkit.googleapis.com/google.identity.identitytoolkit.v1.IdentityToolkit",
//                "iat" => time(),
//                "exp" =>  strtotime(date("Y/m/d H:i:s", strtotime("+30 minutes"))),
//                "iss" => "firebase-adminsdk-b71mj@dschat-b633c.iam.gserviceaccount.com",
//                "sub" => "firebase-adminsdk-b71mj@dschat-b633c.iam.gserviceaccount.com",
//                "uid" => strval(Auth::id()),
//            );
//            $firebase_token = JWT::encode($payload, $privateKey, 'RS256');
//            $success['firebase_token'] =$firebase_token;

            return response()->json(['success' => $success], $this-> successStatus);
        }else{
            return response()->json(['error' => 'Unauthorised'], $this-> failStatus);
        }
    }

    public function getFirebaseToken(){
        $privateKey = "-----BEGIN PRIVATE KEY-----\nMIIEvQIBADANBgkqhkiG9w0BAQEFAASCBKcwggSjAgEAAoIBAQCyY3NDAfUW707V\nLoI566S77Q4iIaQAeWU1gBvzXZ46XV/GnpLqOdq37pM94K6YrN03uDLooknBKe+m\ny/6mM7GMWH46IY2i6Ivgn6FSS7yNgXKzO/EA6JNwDbd5KN9Ir2enoluZw7kJKlUy\nXVqrQOWbQHlhVz7Boa93lzE3npN+unPjADZoN/43alSD7qWwtXSwWbwIEhiXYVZA\nQfP4IHrRGSiWrrliqjwqgKUvAdp/I6lruwSjjmPJyYlrVTICgoHpSfoHwcJiOSg6\nd21fr13lDiRtp8dfyNR2G66JtlUy/7eEZhula1YgktPrSmvBf/1aDt+qAkvuhGQV\nXJmjD4xbAgMBAAECggEABjZ9fK90RES4Z6dThMXmul7St7CZQCT6N+dK0eqISLsY\ny1eZgUvbiL1n5mdNXWbj231wVHbWf8JApPwE1fAF9n/cgZTKlIpp2Lxmh3G40urX\nvlNPnPr8q5HmunMId8UYJJf8fbn5105gojzrG/zKImKX96JsvpytekpLPgcpVugD\nn1btW/+ygGe+zmjhgJJoTA2qIX7XsRV+OpilmOq6ukHjHPp9w1JkkRk/NVXK2Pi5\nq853DQfQ2e+S+sKQQS926GQvwH6SIa53qkIRFZcZqUyI3LdbthAXytyipFoYp7je\nM/tLirWkgJFY12B2IHH2aLBBxV/GAZRlOQLoKPLxJQKBgQDX5RF/jsfFMNmXmSPN\n8cgf7BSYq77ppOEmT4bLjytApF84KuOgAp+U2bpovv9rF5IeTU3tNTURyUHo125E\nrA6e+lWLit417kUSTpDQHqYXXa7JepWge4bNTR0dTIAeLyx+zzzoVTmP6Sf4BT8Q\nnxLfHgp391boGE00SSePHii6NwKBgQDThsJjv7EbH8Xu3bR6J83/a0b301PHfXGD\n/hd/fkmNu9OjRYkt68XNHRct9cHsiwCQRkhuk/4mO3ofKpTFGetElTfSgyIssrHR\nT86vr7cXfWyvGIHBylC8our+0ZHY0qQuA3J+LZK1/HB6IMfy5PQsIAPnp7qoKEUd\nzoxH1Fqc/QKBgAH4Tx6Q2PXNqk6d0RvR9veDmfSCrK1JGdzVoO8+kTB3z36dtZIC\nNB6Rlgfapk392xF3txxQ/fj5kyZiwNjTkRaWRi2NyNVJJUwzd1LO1Mkly7B+89qR\nBfvdlkRsLEcaOxe1IGMmU+4iPIEg2yB1syBiD8pkcWCQyDOP71J4/folAoGBAI/G\nr2ahqz4mkKFV7RCC8dBFGM3mxLF6IKh5E3vDWTePjcESyLV/5skOluzUeOXRbaaj\nPyR6T2upTc7VYJ3AilvpmTHrlFUReTAusOxV4XuD81XosHoKjGpLyrM9u4bHVnU4\nPqBpVamBVuqMUZxV52n+sOjK52LZTHGALXW9b4H5AoGAXCMzKgUa1mENR3Tj2KJM\nhLgujAESEQNV+WU/SB+gJhAfVO6gDTuKBgOYHv+z3E4rYqZRgNBsAG/+/y7qCE4p\n1uuIur+4pq/IKGgzrCcWJRQd/QPIrkPIvc8sfWkk0zSXWKPkxWXUuOYxGEdxbHbd\nl4xBC29xXx0KAtV12weDopM=\n-----END PRIVATE KEY-----\n";
        $payload = array(
            "aud" => "https://identitytoolkit.googleapis.com/google.identity.identitytoolkit.v1.IdentityToolkit",
            "iat" => time(),
            "exp" =>  strtotime(date("Y/m/d H:i:s", strtotime("+30 minutes"))),
            "iss" => "firebase-adminsdk-oryz3@boibichitra-chat.iam.gserviceaccount.com",
            "sub" => "firebase-adminsdk-oryz3@boibichitra-chat.iam.gserviceaccount.com",
            "uid" => strval(Auth::id()),
        );
        $firebase_token = JWT::encode($payload, $privateKey, 'RS256');
        $success['firebase_token'] =$firebase_token;

        return response()->json(['success' => $success], $this-> successStatus);
    }

    public function getChatUserList(){
        $users = DB::table("users")
            ->join('model_has_roles','model_has_roles.model_id','users.id')
            ->join('roles','model_has_roles.role_id','roles.id')
            ->leftJoin('warehouses','users.warehouse_id','warehouses.id')
            ->leftJoin('stores','users.store_id','stores.id')
            ->where('users.id','!=',Auth::id())
            ->where('roles.id','!=',8)
            ->select('users.id','users.name','users.phone','users.email','users.status','roles.name as role','warehouses.id as warehouse_id','warehouses.name as warehouse_name','stores.id as store_id','stores.name as store_name')
            ->get();


        if($users)
        {
            return response()->json(['success'=>true,'response' => $users], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No User Found!'], $this->failStatus);
        }
    }

    public function getChatUserIdentityList(Request $request){
        $users = DB::table("users")
            ->join('model_has_roles','model_has_roles.model_id','users.id')
            ->join('roles','model_has_roles.role_id','roles.id')
            ->leftJoin('warehouses','users.warehouse_id','warehouses.id')
            ->leftJoin('stores','users.store_id','stores.id')
            //->whereIn('users.id',[1,3,5,6])
            ->whereIn('users.id',json_decode($request->user_ids))
            ->select('users.id','users.name','users.phone','users.email','users.status','roles.name as role','warehouses.id as warehouse_id','warehouses.name as warehouse_name','stores.id as store_id','stores.name as store_name')
            ->get();


        if($users)
        {
            return response()->json(['success'=>true,'response' => $users], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No User Found!'], $this->failStatus);
        }
    }
}
