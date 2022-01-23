<?php

namespace App\Http\Controllers\API;

use App\Helpers\APIHelpers;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    public $successStatus = 200;
    public $authStatus = 401;
    public $failStatus = 402;
    public $ExistsStatus = 403;
    public $validationStatus = 404;

    public function roleList(){
        try {
            $roles = DB::table('roles')
                ->select('id','name')
                //->where('name','!=','admin')
                ->get();

            if($roles)
            {
                $data = [];

                foreach($roles as $role){
                    $nested_data['id'] = $role->id;
                    $nested_data['name'] = $role->name;

                    $nested_data['permissions'] = Permission::join("role_has_permissions","role_has_permissions.permission_id","=","permissions.id")
                        ->where("role_has_permissions.role_id",$role->id)
                        ->get();

                    $data[] = $nested_data;
                }

                //$success['role'] =  $data;
                //return response()->json(['success'=>true,'response' => $success], $this->successStatus);
                $response = APIHelpers::createAPIResponse(false,200,'',$data);
                return response()->json($response,200);
            }else{
                //return response()->json(['success'=>false,'response'=>'No Role List Found!'], $this->failStatus);
                $response = APIHelpers::createAPIResponse(true,404,'No Role Found.',null);
                return response()->json($response,404);
            }
        } catch (\Exception $e) {
            //return $e->getMessage();
            $response = APIHelpers::createAPIResponse(false,500,'Internal Server Error.',null);
            return response()->json($response,500);
        }
    }

    public function rolePermissionCreate(Request $request){
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|unique:roles,name',
                'permission' => 'required',
            ]);

            if ($validator->fails()) {
                $response = APIHelpers::createAPIResponse(true,400,$validator->errors(),null);
                return response()->json($response,400);
            }

            $role = Role::create(['name' => $request->input('name')]);
            $role->syncPermissions($request->input('permission'));

            if($role)
            {
                $response = APIHelpers::createAPIResponse(false,201,'Role Added Successfully.',$role);
                return response()->json($response,201);
            }else{
                $response = APIHelpers::createAPIResponse(true,404,'No Role Found.',null);
                return response()->json($response,404);
            }
        } catch (\Exception $e) {
            //return $e->getMessage();
            $response = APIHelpers::createAPIResponse(false,500,'Internal Server Error.',null);
            return response()->json($response,500);
        }
    }

    public function rolePermissionUpdate(Request $request){
        try {
            $validator = Validator::make($request->all(), [
                'role_id' => 'required',
                'name' => 'required',
                'permission' => 'required',
            ]);

            if ($validator->fails()) {
                $response = APIHelpers::createAPIResponse(true,400,$validator->errors(),null);
                return response()->json($response,400);
            }

            //$role = Role::create(['name' => $request->input('name')]);
            //$role->syncPermissions($request->input('permission'));


            $role = Role::find($request->role_id);
            $role->name = $request->input('name');
            $role->save();

            $role->syncPermissions($request->input('permission'));

            if($role)
            {
                $response = APIHelpers::createAPIResponse(false,200,'Role Updated Successfully.',null);
                return response()->json($response,200);
            }else{
                $response = APIHelpers::createAPIResponse(true,400,'Role Updated Failed.',null);
                return response()->json($response,400);
            }
        } catch (\Exception $e) {
            //return $e->getMessage();
            $response = APIHelpers::createAPIResponse(false,500,'Internal Server Error.',null);
            return response()->json($response,500);
        }
    }

}
