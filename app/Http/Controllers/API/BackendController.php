<?php

namespace App\Http\Controllers\API;

use App\Attendance;
use App\Department;
use App\Designation;
use App\Employee;
use App\EmployeeOfficeInformation;
use App\EmployeeSalaryInformation;
use App\Helpers\UserInfo;
use App\Holiday;
use App\Http\Controllers\Controller;
use App\LeaveApplication;
use App\LeaveCategory;
use App\Party;
use App\PaymentCollection;
use App\PaymentPaid;
use App\Payroll;
use App\Payslip;
use App\Product;
use App\ProductBrand;
use App\ProductPurchase;
use App\ProductPurchaseDetail;
use App\ProductPurchaseReturn;
use App\ProductPurchaseReturnDetail;
use App\ProductSale;
use App\ProductSaleDetail;
use App\ProductSaleReturn;
use App\ProductSaleReturnDetail;
use App\ProductUnit;
use App\ProductVat;
use App\Stock;
use App\StockTransfer;
use App\StockTransferDetail;
use App\StockTransferRequest;
use App\StockTransferRequestDetail;
use App\Store;
use App\TangibleAssets;
use App\Transaction;
use App\User;
use App\VoucherType;
use App\Warehouse;
use App\WarehouseCurrentStock;
use App\WarehouseProductDamage;
use App\WarehouseStoreCurrentStock;
use App\Weekend;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class BackendController extends Controller
{
    public $successStatus = 200;
    public $authStatus = 401;
    public $failStatus = 402;
    public $ExistsStatus = 403;
    public $validationStatus = 404;

    public function test()
    {
        //return 'test';
        return response()->json(['success'=>true,'response' => 'Test Action Api!'], $this->successStatus);
    }




    // product unit
    public function warehouseList(){
        $warehouses = DB::table('warehouses')->select('id','name','phone','email','address','status')->get();

        if($warehouses)
        {
            $success['warehouses'] =  $warehouses;
            return response()->json(['success'=>true,'response' => $success], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Warehouses List Found!'], $this->failStatus);
        }
    }

    public function warehouseCreate(Request $request){

        $validator = Validator::make($request->all(), [
            'name' => 'required|unique:warehouses,name',
            'phone' => 'required|unique:warehouses,phone',
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


        $warehouse = new Warehouse();
        $warehouse->name = $request->name;
        $warehouse->phone = $request->phone;
        $warehouse->email = $request->email;
        $warehouse->address = $request->address;
        $warehouse->status = $request->status;
        $warehouse->save();
        $insert_id = $warehouse->id;

        if($insert_id){
            return response()->json(['success'=>true,'response' => $warehouse], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'Warehouse Not Created Successfully!'], $this->failStatus);
        }
    }

    public function warehouseEdit(Request $request){

        $validator = Validator::make($request->all(), [
            'warehouse_id'=> 'required',
            'name' => 'required|unique:warehouses,name,'.$request->warehouse_id,
            'phone' => 'required|unique:warehouses,phone,'.$request->warehouse_id,
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

        $check_exists_warehouse = DB::table("warehouses")->where('id',$request->warehouse_id)->pluck('id')->first();
        if($check_exists_warehouse == null){
            return response()->json(['success'=>false,'response'=>'No Warehouse Found!'], $this->failStatus);
        }

        $warehouse = Warehouse::find($request->warehouse_id);
        $warehouse->name = $request->name;
        $warehouse->phone = $request->phone;
        $warehouse->email = $request->email;
        $warehouse->address = $request->address;
        $warehouse->status = $request->status;
        $update_warehouse = $warehouse->save();

        if($update_warehouse){
            return response()->json(['success'=>true,'response' => $warehouse], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'Warehouse Not Updated Successfully!'], $this->failStatus);
        }
    }

    public function warehouseDelete(Request $request){
        $check_exists_warehouse = DB::table("warehouses")->where('id',$request->warehouse_id)->pluck('id')->first();
        if($check_exists_warehouse == null){
            return response()->json(['success'=>false,'response'=>'No Warehouse Found!'], $this->failStatus);
        }

        //$delete_warehouse = DB::table("warehouses")->where('id',$request->warehouse_id)->delete();
        $soft_delete_warehouse = Warehouse::find($request->warehouse_id);
        $soft_delete_warehouse->status=0;
        $affected_row = $soft_delete_warehouse->update();
        if($affected_row)
        {
            return response()->json(['success'=>true,'response' => 'Warehouse Successfully Soft Deleted!'], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Warehouse Deleted!'], $this->failStatus);
        }
    }

    public function storeList(){
        $stores = DB::table('stores')
            ->leftJoin('warehouses','stores.warehouse_id','warehouses.id')
            ->select('stores.id','stores.name as store_name','stores.phone','stores.email','stores.address','stores.status','warehouses.id as warehouse_id','warehouses.name as warehouse_name')
            ->get();

        if($stores)
        {
            $success['stores'] =  $stores;
            return response()->json(['success'=>true,'response' => $success], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Store List Found!'], $this->failStatus);
        }
    }

    public function storeCreate(Request $request){

        $validator = Validator::make($request->all(), [
            'warehouse_id'=> 'required',
            'name' => 'required|unique:stores,name',
            'phone'=> 'required',
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


        $store = new Store();
        $store->warehouse_id = $request->warehouse_id;
        $store->name = $request->name;
        $store->phone = $request->phone;
        $store->email = $request->email ? $request->email : NULL;
        $store->address = $request->address ? $request->address : NULL;
        $store->status = $request->status;
        $store->save();
        $insert_id = $store->id;

        if($insert_id){
            return response()->json(['success'=>true,'response' => $store], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'Store Not Created Successfully!'], $this->failStatus);
        }
    }

    public function storeEdit(Request $request){

        $validator = Validator::make($request->all(), [
            'warehouse_id'=> 'required',
            'name' => 'required|unique:stores,name,'.$request->store_id,
            'phone'=> 'required',
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

        $check_exists_store = DB::table("stores")->where('id',$request->store_id)->pluck('id')->first();
        if($check_exists_store == null){
            return response()->json(['success'=>false,'response'=>'No Store Found!'], $this->failStatus);
        }

        $store = Store::find($request->store_id);
        $store->warehouse_id = $request->warehouse_id;
        $store->name = $request->name;
        $store->phone = $request->phone;
        $store->email = $request->email ? $request->email : NULL;
        $store->address = $request->address ? $request->address : NULL;
        $store->status = $request->status;
        $update_store = $store->save();

        if($update_store){
            return response()->json(['success'=>true,'response' => $store], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'Store Not Updated Successfully!'], $this->failStatus);
        }
    }

    public function storeDelete(Request $request){
        $check_exists_store = DB::table("stores")->where('id',$request->store_id)->pluck('id')->first();
        if($check_exists_store == null){
            return response()->json(['success'=>false,'response'=>'No Store Found!'], $this->failStatus);
        }

        //$delete_store = DB::table("stores")->where('id',$request->store_id)->delete();
        $store_soft_delete = Store::find($request->store_id);
        $store_soft_delete->status=0;
        $affected_row = $store_soft_delete->update();
        if($affected_row)
        {
            return response()->json(['success'=>true,'response' => 'Store Successfully Soft Deleted!'], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Store Deleted!'], $this->failStatus);
        }
    }





    // first permission create
    // and then role create
    // final user create

    public function permissionListShow(){
        $permissions = DB::table('permissions')->select('id','name')->get();

        if($permissions)
        {
            $success['permissions'] =  $permissions;
            return response()->json(['success'=>true,'response' => $success], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Permission List Found!'], $this->failStatus);
        }
    }

    public function permissionListCreate(Request $request){
        $validator = Validator::make($request->all(), [
            'name' => 'required|unique:permissions,name',
        ]);

        if ($validator->fails()) {
            $response = [
                'success' => false,
                'data' => 'Validation Error.',
                'message' => $validator->errors()
            ];

            return response()->json($response, $this->validationStatus);
        }

        $permission = Permission::create(['name' => $request->input('name')]);

        if($permission)
        {
            return response()->json(['success'=>true,'response' => $permission], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Permission Created!'], $this->failStatus);
        }
    }

    public function permissionListDetails(Request $request){
        $check_exists_party = DB::table("permissions")->where('id',$request->permission_id)->pluck('id')->first();
        if($check_exists_party == null){
            return response()->json(['success'=>false,'response'=>'No Permission Found, using this id!'], $this->failStatus);
        }

        $permissions = DB::table("permissions")->where('id',$request->permission_id)->latest()->first();
        if($permissions)
        {
            return response()->json(['success'=>true,'response' => $permissions], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Permission Found!'], $this->failStatus);
        }
    }

    public function permissionListUpdate(Request $request){

        $validator = Validator::make($request->all(), [
            'permission_id' => 'required',
            'name' => 'required|unique:permissions,name,'.$request->permission_id,
        ]);

        if ($validator->fails()) {
            $response = [
                'success' => false,
                'data' => 'Validation Error.',
                'message' => $validator->errors()
            ];

            return response()->json($response, $this-> validationStatus);
        }

        $check_exists_party = DB::table("permissions")->where('id',$request->permission_id)->pluck('id')->first();
        if($check_exists_party == null){
            return response()->json(['success'=>false,'response'=>'No Permission Found, using this id!'], $this->failStatus);
        }

        $permission = Permission::find($request->permission_id);
        $permission->name = $request->name;
        $update_permission = $permission->save();

        if($update_permission){
            return response()->json(['success'=>true,'response' => $permission], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'Permission Not Updated Successfully!'], $this->failStatus);
        }
    }

    public function roleList(){

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

            $success['role'] =  $data;
            return response()->json(['success'=>true,'response' => $success], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Role List Found!'], $this->failStatus);
        }
    }

    public function rolePermissionCreate(Request $request){
        $validator = Validator::make($request->all(), [
            'name' => 'required|unique:roles,name',
            'permission' => 'required',
        ]);

        if ($validator->fails()) {
            $response = [
                'success' => false,
                'data' => 'Validation Error.',
                'message' => $validator->errors()
            ];

            return response()->json($response, $this-> validationStatus);
        }

        $role = Role::create(['name' => $request->input('name')]);
        $role->syncPermissions($request->input('permission'));

        if($role)
        {
            return response()->json(['success'=>true,'response' => $role], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Role Created!'], $this->failStatus);
        }
    }

    public function rolePermissionUpdate(Request $request){
        $validator = Validator::make($request->all(), [
            'role_id' => 'required',
            'name' => 'required',
            'permission' => 'required',
        ]);

        if ($validator->fails()) {
            $response = [
                'success' => false,
                'data' => 'Validation Error.',
                'message' => $validator->errors()
            ];

            return response()->json($response, $this->validationStatus);
        }

        //$role = Role::create(['name' => $request->input('name')]);
        //$role->syncPermissions($request->input('permission'));


        $role = Role::find($request->role_id);
        $role->name = $request->input('name');
        $role->save();

        $role->syncPermissions($request->input('permission'));

        if($role)
        {
            return response()->json(['success'=>true,'response' => $role], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Role Created!'], $this->failStatus);
        }
    }

    public function userList(){
        //$users = DB::table('users')->select('id','name','phone','email','status')->get();
        $users = DB::table("users")
            ->join('model_has_roles','model_has_roles.model_id','users.id')
            ->join('roles','model_has_roles.role_id','roles.id')
            ->leftJoin('warehouses','users.warehouse_id','warehouses.id')
            ->leftJoin('stores','users.store_id','stores.id')
            ->select('users.id','users.name','users.phone','users.email','users.status','roles.name as role','warehouses.id as warehouse_id','warehouses.name as warehouse_name','stores.id as store_id','stores.name as store_name')
            ->get();

        if($users)
        {
            $success['users'] =  $users;
            return response()->json(['success'=>true,'response' => $success], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No User List Found!'], $this->failStatus);
        }
    }

    public function userCreate(Request $request){

        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'phone' => 'required|unique:users,phone',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|same:confirm_password',
            'roles' => 'required',
            'status' => 'required',
            'warehouse_id' => 'required',
        ]);

        if ($validator->fails()) {
            $response = [
                'success' => false,
                'data' => 'Validation Error.',
                'message' => $validator->errors()
            ];

            return response()->json($response, $this->validationStatus);
        }


        $input = $request->all();
        $input['password'] = Hash::make($input['password']);

        $user = User::create($input);
        $user->assignRole($request->input('roles'));

        if($user){
            return response()->json(['success'=>true,'response' => $user], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'User Not Created Successfully!'], $this->failStatus);
        }
    }

    public function userDetails(Request $request){
        $check_exists_party = DB::table("users")->where('id',$request->user_id)->pluck('id')->first();
        if($check_exists_party == null){
            return response()->json(['success'=>false,'response'=>'No User Found, Using this id!'], $this->failStatus);
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


        if($users)
        {
            return response()->json(['success'=>true,'response' => $users], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No User Found!'], $this->failStatus);
        }
    }

    public function userEdit(Request $request){

        $input = $request->all();

        if(!empty($input['password'])){
            $validator = Validator::make($request->all(), [
                'user_id' => 'required',
                'name' => 'required',
                'phone' => 'required|unique:users,phone,'.$request->user_id,
                'email' => 'required|email|unique:users,email,'.$request->user_id,
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
                'email' => 'required|email|unique:users,email,'.$request->user_id,
                'roles' => 'required',
                'status' => 'required',
                'warehouse_id' => 'required',
            ]);
        }



        if ($validator->fails()) {
            $response = [
                'success' => false,
                'data' => 'Validation Error.',
                'message' => $validator->errors()
            ];

            return response()->json($response, $this->validationStatus);
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
            $user->update($input);
            DB::table('model_has_roles')->where('model_id',$request->user_id)->delete();

            $user->assignRole($request->input('roles'));

            if($user){
                return response()->json(['success'=>true,'response' => $user], $this->successStatus);
            }else{
                return response()->json(['success'=>false,'response'=>'User Not Updated Successfully!'], $this->failStatus);
            }
        }else{
            return response()->json(['success'=>false,'response'=>'No User Found, Using this id!'], $this->failStatus);
        }
    }

    public function userDelete(Request $request){
        $check_exists_party = DB::table("users")->where('id',$request->user_id)->pluck('id')->first();
        if($check_exists_party == null){
            return response()->json(['success'=>false,'response'=>'No User Found, Using This Id!'], $this->failStatus);
        }

        //$delete_user = DB::table("users")->where('id',$request->user_id)->delete();
        $soft_delete_user = User::find($request->user_id);
        $soft_delete_user->status=0;
        $affected_row = $soft_delete_user->update();
        if($affected_row)
        {
            return response()->json(['success'=>true,'response' => 'User Successfully Soft Deleted!'], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No User Deleted!'], $this->failStatus);
        }
    }

    public function partyList(){
        $parties = DB::table('parties')->select('id','type','customer_type','name','phone','address','virtual_balance','status')->orderBy('id','desc')->get();

        if($parties)
        {
            $success['parties'] =  $parties;
            return response()->json(['success'=>true,'response' => $success], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Party List Found!'], $this->failStatus);
        }
    }

    public function partyCreate(Request $request){

        $validator = Validator::make($request->all(), [
            'type'=> 'required',
            'name' => 'required',
            'phone'=> 'required',
            'status' => 'required',
        ]);

        if ($validator->fails()) {
            $response = [
                'success' => false,
                'data' => 'Validation Error.',
                'message' => $validator->errors()
            ];

            return response()->json($response, $this-> validationStatus);
        }


        $parties = new Party();
        $parties->type = $request->type;
        $parties->customer_type = $request->customer_type;
        $parties->name = $request->name;
        $parties->slug = Str::slug($request->name);
        $parties->phone = $request->phone;
        $parties->email = $request->email;
        $parties->address = $request->address;
        $parties->status = $request->status;
        $parties->save();
        $insert_id = $parties->id;

        if($insert_id){
            if($request->type == 'customer'){
                $user_data['name'] = $request->name;
                $user_data['email'] = $request->email;
                $user_data['phone'] = $request->phone;
                $user_data['password'] = Hash::make(123456);
                $user_data['party_id'] = $insert_id;
                $user = User::create($user_data);
                // first create customer role, then bellow code enable
                $user->assignRole('customer');

                $text = "Dear ".$request->name." Sir, Your Username is ".$request->phone." and password is: 123456";
                UserInfo::smsAPI("88".$request->phone,$text);
            }

            return response()->json(['success'=>true,'response' => $parties], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'Party Not Created Successfully!'], $this->failStatus);
        }
    }

    public function partyDetails(Request $request){
        $check_exists_party = DB::table("parties")->where('id',$request->party_id)->pluck('id')->first();
        if($check_exists_party == null){
            return response()->json(['success'=>false,'response'=>'No Party Found, using this id!'], $this->failStatus);
        }

        $party = DB::table("parties")->where('id',$request->party_id)->latest()->first();
        if($party)
        {
            return response()->json(['success'=>true,'response' => $party], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Party Found!'], $this->failStatus);
        }
    }

    public function partyUpdate(Request $request){

        $validator = Validator::make($request->all(), [
            'party_id'=> 'required',
            'type'=> 'required',
            'name' => 'required',
            'phone'=> 'required',
            'status' => 'required',
        ]);

        if ($validator->fails()) {
            $response = [
                'success' => false,
                'data' => 'Validation Error.',
                'message' => $validator->errors()
            ];

            return response()->json($response, $this->validationStatus);
        }

        $check_exists_party = DB::table("parties")->where('id',$request->party_id)->pluck('id')->first();
        if($check_exists_party == null){
            return response()->json(['success'=>false,'response'=>'No Party Found!'], $this->failStatus);
        }

        $parties = Party::find($request->party_id);
        $parties->type = $request->type;
        $parties->customer_type = $request->customer_type;
        $parties->name = $request->name;
        $parties->slug = Str::slug($request->name);
        $parties->phone = $request->phone;
        $parties->email = $request->email;
        $parties->address = $request->address;
        $parties->status = $request->status;
        $update_party = $parties->save();

        if($update_party){
            return response()->json(['success'=>true,'response' => $parties], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'Party Not Created Successfully!'], $this->failStatus);
        }
    }

    public function partyDelete(Request $request){
        $check_exists_party = DB::table("parties")->where('id',$request->party_id)->pluck('id')->first();
        if($check_exists_party == null){
            return response()->json(['success'=>false,'response'=>'No Party Found!'], $this->failStatus);
        }

        $delete_party = DB::table("parties")->where('id',$request->party_id)->delete();
        if($delete_party)
        {
            return response()->json(['success'=>true,'response' => 'Party Successfully Deleted!'], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Party Deleted!'], $this->failStatus);
        }
    }

    public function customerVirtualBalance(Request $request){
        $check_exists_user = DB::table("users")->where('id',$request->user_id)->pluck('id')->first();
        if($check_exists_user == null){
            return response()->json(['success'=>false,'response'=>'No User Found, using this id!'], $this->failStatus);
        }

        $party = DB::table("parties")
            ->join('users','parties.id','=','users.party_id')
            ->where('users.id',$request->user_id)
            ->select('parties.virtual_balance','parties.id')
            ->first();
        if($party)
        {
            $virtual_balance = $party->virtual_balance;

            return response()->json(['success'=>true,'response' => $virtual_balance], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Party Found!'], $this->failStatus);
        }
    }

    public function customerSaleInformation(Request $request){
        $check_exists_user = DB::table("users")->where('id',$request->user_id)->pluck('id')->first();
        if($check_exists_user == null){
            return response()->json(['success'=>false,'response'=>'No User Found, using this id!'], $this->failStatus);
        }

        $party = DB::table("parties")
            ->join('users','parties.id','=','users.party_id')
            ->where('users.id',$request->user_id)
            ->select('parties.virtual_balance','parties.id')
            ->first();
        if($party)
        {
            $success['virtual_balance'] = $party->virtual_balance;

            $product_sales = DB::table('product_sales')
                ->leftJoin('users','product_sales.user_id','users.id')
                ->leftJoin('parties','product_sales.party_id','parties.id')
                ->leftJoin('warehouses','product_sales.warehouse_id','warehouses.id')
                ->leftJoin('stores','product_sales.store_id','stores.id')
                ->where('product_sales.party_id',$party->id)
                ->select('product_sales.id','product_sales.invoice_no','product_sales.discount_type','product_sales.discount_amount','product_sales.total_amount','product_sales.paid_amount','product_sales.due_amount','product_sales.sale_date_time','users.name as user_name','parties.id as customer_id','parties.name as customer_name','warehouses.id as warehouse_id','warehouses.name as warehouse_name','stores.id as store_id','stores.name as store_name')
                ->get();

            $success['product_sales'] = $product_sales;

            return response()->json(['success'=>true,'response' => $success], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Sale Found!'], $this->failStatus);
        }
    }

    public function customerSaleDetailsInformation(Request $request){
        $check_exists_user = DB::table("users")->where('id',$request->user_id)->pluck('id')->first();
        if($check_exists_user == null){
            return response()->json(['success'=>false,'response'=>'No User Found, using this id!'], $this->failStatus);
        }

        $product_sale_details = DB::table('product_sales')
                ->join('product_sale_details','product_sales.id','product_sale_details.product_sale_id')
                ->leftJoin('products','product_sale_details.product_id','products.id')
                ->leftJoin('product_units','product_sale_details.product_unit_id','product_units.id')
                ->leftJoin('product_brands','product_sale_details.product_brand_id','product_brands.id')
                ->where('product_sales.id',$request->sale_id)
                ->select('products.id as product_id','products.name as product_name','product_units.id as product_unit_id','product_units.name as product_unit_name','product_brands.id as product_brand_id','product_brands.name as product_brand_name','product_sale_details.qty','product_sale_details.id as product_sale_detail_id','product_sale_details.price as mrp_price','product_sale_details.sale_date','product_sale_details.return_among_day','product_sale_details.price as mrp_price')
                ->get();
        if(count($product_sale_details) > 0){

            $success['product_sale_details'] = $product_sale_details;

            return response()->json(['success'=>true,'response' => $success], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Sale Details Found!'], $this->failStatus);
        }
    }

    // product brand
    public function productBrandList(){
        $product_brands = DB::table('product_brands')->select('id','name','status')->orderBy('id','desc')->get();

        if($product_brands)
        {
            $success['product_brand'] =  $product_brands;
            return response()->json(['success'=>true,'response' => $success], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Product Brand List Found!'], $this->failStatus);
        }
    }

    public function productBrandCreate(Request $request){

        $validator = Validator::make($request->all(), [
            'name' => 'required|unique:product_brands,name',
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


        $productBrand = new ProductBrand();
        $productBrand->name = $request->name;
        $productBrand->status = $request->status;
        $productBrand->save();
        $insert_id = $productBrand->id;

        if($insert_id){
            return response()->json(['success'=>true,'response' => $productBrand], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'Product Brand Not Created Successfully!'], $this->failStatus);
        }
    }

    public function productBrandEdit(Request $request){

        $validator = Validator::make($request->all(), [
            'product_brand_id'=> 'required',
            'name' => 'required|unique:product_brands,name,'.$request->product_brand_id,
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

        $check_exists_product_brand = DB::table("product_brands")->where('id',$request->product_brand_id)->pluck('id')->first();
        if($check_exists_product_brand == null){
            return response()->json(['success'=>false,'response'=>'No Product Brand Found!'], $this->failStatus);
        }

        $product_brands = ProductBrand::find($request->product_brand_id);
        $product_brands->name = $request->name;
        $product_brands->status = $request->status;
        $update_product_brand = $product_brands->save();

        if($update_product_brand){
            return response()->json(['success'=>true,'response' => $product_brands], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'Product Brand Not Created Successfully!'], $this->failStatus);
        }
    }

    public function productBrandDelete(Request $request){
        $check_exists_product_brand = DB::table("product_brands")->where('id',$request->product_brand_id)->pluck('id')->first();
        if($check_exists_product_brand == null){
            return response()->json(['success'=>false,'response'=>'No Product Brand Found!'], $this->failStatus);
        }

        //$delete_party = DB::table("product_brands")->where('id',$request->product_brand_id)->delete();
        $soft_delete_product_brand = ProductBrand::find($request->product_brand_id);
        $soft_delete_product_brand->status=0;
        $affected_row = $soft_delete_product_brand->update();
        if($affected_row)
        {
            return response()->json(['success'=>true,'response' => 'Product Brand Successfully Soft Deleted!'], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Product Brand Deleted!'], $this->failStatus);
        }
    }

    // product unit
    public function productUnitList(){
        $product_units = DB::table('product_units')->select('id','name','status')->get();

        if($product_units)
        {
            $success['product_units'] =  $product_units;
            return response()->json(['success'=>true,'response' => $success], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Product Unit List Found!'], $this->failStatus);
        }
    }

    public function productUnitCreate(Request $request){

        $validator = Validator::make($request->all(), [
            'name' => 'required|unique:product_units,name',
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


        $productUnit = new ProductUnit();
        $productUnit->name = $request->name;
        $productUnit->status = $request->status;
        $productUnit->save();
        $insert_id = $productUnit->id;

        if($insert_id){
            return response()->json(['success'=>true,'response' => $productUnit], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'Product Not Not Created Successfully!'], $this->failStatus);
        }
    }

    public function productUnitEdit(Request $request){

        $validator = Validator::make($request->all(), [
            'product_unit_id'=> 'required',
            'name' => 'required|unique:product_units,name,'.$request->product_unit_id,
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

        $check_exists_product_unit = DB::table("product_units")->where('id',$request->product_unit_id)->pluck('id')->first();
        if($check_exists_product_unit == null){
            return response()->json(['success'=>false,'response'=>'No Product Unit Found!'], $this->failStatus);
        }

        $product_units = ProductUnit::find($request->product_unit_id);
        $product_units->name = $request->name;
        $product_units->status = $request->status;
        $update_product_unit = $product_units->save();

        if($update_product_unit){
            return response()->json(['success'=>true,'response' => $product_units], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'Product Unit Not Updated Successfully!'], $this->failStatus);
        }
    }

    public function productUnitDelete(Request $request){
        $check_exists_product_unit = DB::table("product_units")->where('id',$request->product_unit_id)->pluck('id')->first();
        if($check_exists_product_unit == null){
            return response()->json(['success'=>false,'response'=>'No Product Unit Found!'], $this->failStatus);
        }

        //$delete_product_unit = DB::table("product_units")->where('id',$request->product_unit_id)->delete();
        $soft_delete_product_unit = ProductUnit::find($request->product_unit_id);
        $soft_delete_product_unit->status=0;
        $affected_row = $soft_delete_product_unit->update();
        if($affected_row)
        {
            return response()->json(['success'=>true,'response' => 'Product Unit Successfully Soft Deleted!'], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Product Unit Deleted!'], $this->failStatus);
        }
    }

    // product vat
    public function productVatList(){
        $product_vats = DB::table('product_vats')->select('id','name','vat_percentage','status')->orderBy('id','desc')->get();

        if($product_vats)
        {
            $success['product_vats'] =  $product_vats;
            return response()->json(['success'=>true,'response' => $success], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Product Vat List Found!'], $this->failStatus);
        }
    }

    public function productVatCreate(Request $request){

        $validator = Validator::make($request->all(), [
            'name' => 'required|unique:product_vats,name',
            'vat_percentage'=> 'required',
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


        $productVat = new ProductVat();
        $productVat->name = $request->name;
        $productVat->vat_percentage = $request->vat_percentage;
        $productVat->status = $request->status;
        $productVat->save();
        $insert_id = $productVat->id;

        if($insert_id){
            return response()->json(['success'=>true,'response' => $productVat], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'Product Vat Not Created Successfully!'], $this->failStatus);
        }
    }

    public function productVatEdit(Request $request){

        $validator = Validator::make($request->all(), [
            'product_vat_id'=> 'required',
            'name' => 'required|unique:product_vats,name,'.$request->product_vat_id,
            'vat_percentage'=> 'required',
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

        $check_exists_product_vat = DB::table("product_vats")->where('id',$request->product_vat_id)->pluck('id')->first();
        if($check_exists_product_vat == null){
            return response()->json(['success'=>false,'response'=>'No Product Vat Found!'], $this->failStatus);
        }

        $product_vats = ProductVat::find($request->product_vat_id);
        $product_vats->name = $request->name;
        $product_vats->vat_percentage = $request->vat_percentage;
        $product_vats->status = $request->status;
        $update_product_vat = $product_vats->save();

        if($update_product_vat){
            return response()->json(['success'=>true,'response' => $product_vats], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'Product Vat Not Updated Successfully!'], $this->failStatus);
        }
    }

    public function productVatDelete(Request $request){
        $check_exists_product_vat = DB::table("product_vats")->where('id',$request->product_vat_id)->pluck('id')->first();
        if($check_exists_product_vat == null){
            return response()->json(['success'=>false,'response'=>'No Product Vat Found!'], $this->failStatus);
        }

        $soft_delete_product_vat = ProductVat::find($request->product_vat_id);
        $soft_delete_product_vat->status=0;
        $affected_row = $soft_delete_product_vat->update();
        if($affected_row)
        {
            return response()->json(['success'=>true,'response' => 'Product Vat Successfully Soft Deleted!'], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Product Vat Deleted!'], $this->failStatus);
        }
    }

    public function productList(){
        $products = DB::table('products')
            ->leftJoin('product_units','products.product_unit_id','product_units.id')
            ->leftJoin('product_brands','products.product_brand_id','product_brands.id')
            ->select('products.id','products.name as product_name','products.image','product_units.id as unit_id','product_units.name as unit_name','products.item_code','products.barcode','products.self_no','products.low_inventory_alert','product_brands.id as brand_id','product_brands.name as brand_name','products.purchase_price','products.whole_sale_price as whole_sale_price','products.selling_price','products.note','products.date','products.status','products.vat_status','products.vat_percentage','products.vat_amount','products.vat_whole_amount')
            ->orderBy('products.id','desc')
            ->get();

        if($products)
        {
            $success['products'] =  $products;
            return response()->json(['success'=>true,'response' => $success], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Product List Found!'], $this->failStatus);
        }
    }



    public function productListPagination(){
        $products = DB::table('products')
            ->leftJoin('product_units','products.product_unit_id','product_units.id')
            ->leftJoin('product_brands','products.product_brand_id','product_brands.id')
            //->where('products.id','>',$cursor)
            //->limit($limit)
            ->select('products.id','products.name as product_name','products.image','product_units.id as unit_id','product_units.name as unit_name','products.item_code','products.barcode','products.self_no','products.low_inventory_alert','product_brands.id as brand_id','product_brands.name as brand_name','products.purchase_price','products.whole_sale_price','products.selling_price','products.note','products.date','products.status','products.vat_status','products.vat_percentage','products.vat_amount')
            //->orderBy('products.id','desc')1
            ->paginate(12);

        if($products)
        {
            $p=$products[$products->count()-1];
            $success['products'] =  $products;
            $success['nextCursor'] =  $p->id;

            return response()->json(['success'=>true,'response' => $success], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Product List Found!'], $this->failStatus);
        }
    }

    public function productListPaginationBarcode(Request $request){
        $products = DB::table('products')
            ->leftJoin('product_units','products.product_unit_id','product_units.id')
            ->leftJoin('product_brands','products.product_brand_id','product_brands.id')
            ->where('products.barcode',$request->barcode)
            ->select('products.id','products.name as product_name','products.image','product_units.id as unit_id','product_units.name as unit_name','products.item_code','products.barcode','products.self_no','products.low_inventory_alert','product_brands.id as brand_id','product_brands.name as brand_name','products.purchase_price','products.whole_sale_price','products.selling_price','products.note','products.date','products.status','products.vat_status','products.vat_percentage','products.vat_amount')
            ->paginate(1);

        if($products)
        {
            $p=$products[$products->count()-1];
            $success['products'] =  $products;
            //$success['nextCursor'] =  $p->id;
            //$success['nextCursor'] =  1;

            return response()->json(['success'=>true,'response' => $success], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Product List Found!'], $this->failStatus);
        }
    }

    public function productListPaginationItemcode(Request $request){
        $products = DB::table('products')
            ->leftJoin('product_units','products.product_unit_id','product_units.id')
            ->leftJoin('product_brands','products.product_brand_id','product_brands.id')
            ->where('products.item_code',$request->item_code)
            ->select('products.id','products.name as product_name','products.image','product_units.id as unit_id','product_units.name as unit_name','products.item_code','products.barcode','products.self_no','products.low_inventory_alert','product_brands.id as brand_id','product_brands.name as brand_name','products.purchase_price','products.whole_sale_price','products.selling_price','products.note','products.date','products.status','products.vat_status','products.vat_percentage','products.vat_amount')
            ->paginate(1);

        if($products)
        {
            $p=$products[$products->count()-1];
            $success['products'] =  $products;
            //$success['nextCursor'] =  $p->id;
            //$success['nextCursor'] =  1;

            return response()->json(['success'=>true,'response' => $success], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Product List Found!'], $this->failStatus);
        }
    }

    public function productListPaginationProductname(Request $request){
        $products = DB::table('products')
            ->leftJoin('product_units','products.product_unit_id','product_units.id')
            ->leftJoin('product_brands','products.product_brand_id','product_brands.id')
            ->where('products.name','like','%'.$request->name.'%')
            ->select('products.id','products.name as product_name','products.image','product_units.id as unit_id','product_units.name as unit_name','products.item_code','products.barcode','products.self_no','products.low_inventory_alert','product_brands.id as brand_id','product_brands.name as brand_name','products.purchase_price','products.whole_sale_price','products.selling_price','products.note','products.date','products.status','products.vat_status','products.vat_percentage','products.vat_amount')
            ->paginate(12);

        if($products)
        {
            $p=$products[$products->count()-1];
            $success['products'] =  $products;
            //$success['nextCursor'] =  $p->id;

            return response()->json(['success'=>true,'response' => $success], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Product List Found!'], $this->failStatus);
        }
    }

    public function allActiveProductList(){
        $products = DB::table('products')
            ->leftJoin('product_units','products.product_unit_id','product_units.id')
            ->leftJoin('product_brands','products.product_brand_id','product_brands.id')
            ->where('products.status',1)
            ->select('products.id','products.name as product_name','products.image','product_units.id as unit_id','product_units.name as unit_name','products.item_code','products.barcode','products.self_no','products.low_inventory_alert','product_brands.id as brand_id','product_brands.name as brand_name','products.purchase_price','products.whole_sale_price','products.selling_price','products.note','products.date','products.status','products.vat_status','products.vat_percentage','products.vat_amount')
            ->orderBy('products.id','desc')
            ->get();

        if($products)
        {
            $success['products'] =  $products;
            return response()->json(['success'=>true,'response' => $success], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Product List Found!'], $this->failStatus);
        }
    }

    public function productCreate(Request $request){

        $fourRandomDigit = rand(1000,9999);
        $barcode = time().$fourRandomDigit;

        $validator = Validator::make($request->all(), [
            'name' => 'required|unique:products,name',
            'product_unit_id'=> 'required',
            //'barcode'=> 'required',
            //'barcode' => 'required|unique:products,barcode',
            'purchase_price'=> 'required',
            'whole_sale_price'=> 'required',
            'selling_price'=> 'required',
            'date'=> 'required',
            'status'=> 'required',
            'vat_status'=> 'required',
        ]);

        if ($validator->fails()) {
            $response = [
                'success' => false,
                'data' => 'Validation Error.',
                'message' => $validator->errors()
            ];

            return response()->json($response, $this-> validationStatus);
        }

        $product_vat = ProductVat::latest()->first();
        $vat_percentage = 0;
        $vat_amount = 0;
        $vat_whole_amount = 0;
        if($product_vat && ($request->vat_status == 1)){
            $vat_percentage = $product_vat->vat_percentage;
            if($request->selling_price > 0){
                $vat_amount = $request->selling_price*$vat_percentage/100;
            }
            if($request->whole_sale_price > 0){
                $vat_whole_amount = $request->whole_sale_price*$vat_percentage/100;
            }
        }

        $product = new Product();
        $product->name = $request->name;
        $product->product_unit_id = $request->product_unit_id;
        $product->item_code = $request->item_code ? $request->item_code : NULL;
        //$product->barcode = $request->barcode;
        $product->barcode = $barcode;
        $product->self_no = $request->self_no ? $request->self_no : NULL;
        $product->low_inventory_alert = $request->low_inventory_alert ? $request->low_inventory_alert : NULL;
        $product->product_brand_id = $request->product_brand_id ? $request->product_brand_id : NULL;
        $product->purchase_price = $request->purchase_price;
        $product->whole_sale_price = $request->whole_sale_price;
        $product->selling_price = $request->selling_price;
        $product->vat_status = $request->vat_status;
        $product->vat_percentage = $vat_percentage;
        $product->vat_amount = $vat_amount;
        $product->vat_whole_amount = $vat_whole_amount;
        $product->note = $request->note ? $request->note : NULL;
        $product->date = $request->date;
        $product->status = $request->status;
        $product->image = 'default.png';
        $product->save();
        $insert_id = $product->id;

        if($insert_id){
            return response()->json(['success'=>true,'response' => $product], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'Product Not Created Successfully!'], $this->failStatus);
        }
    }

    public function productEdit(Request $request){

        $validator = Validator::make($request->all(), [
            'product_id'=> 'required',
            'name' => 'required|unique:products,name,'.$request->product_id,
            'product_unit_id'=> 'required',
            //'barcode'=> 'required',
            //'barcode' => 'required|unique:products,barcode,'.$request->product_id,
            'purchase_price'=> 'required',
            'whole_sale_price'=> 'required',
            'selling_price'=> 'required',
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

        $check_exists_product = DB::table("products")->where('id',$request->product_id)->pluck('id')->first();
        if($check_exists_product == null){
            return response()->json(['success'=>false,'response'=>'No Product Found!'], $this->failStatus);
        }

        $image = Product::where('id',$request->product_id)->pluck('image')->first();

        $product_vat = ProductVat::latest()->first();
        $vat_percentage = 0;
        $vat_amount = 0;
        $vat_whole_amount = 0;
        if($product_vat && ($request->vat_status == 1)){
            $vat_percentage = $product_vat->vat_percentage;
            if($request->selling_price > 0){
                $vat_amount = $request->selling_price*$vat_percentage/100;
            }
            if($request->whole_sale_price > 0){
                $vat_whole_amount = $request->whole_sale_price*$vat_percentage/100;
            }
        }

        $product = Product::find($request->product_id);
        $product->name = $request->name;
        $product->product_unit_id = $request->product_unit_id;
        $product->item_code = $request->item_code ? $request->item_code : NULL;
        $product->barcode = $request->barcode;
        $product->self_no = $request->self_no ? $request->self_no : NULL;
        $product->low_inventory_alert = $request->low_inventory_alert ? $request->low_inventory_alert : NULL;
        $product->product_brand_id = $request->product_brand_id ? $request->product_brand_id : NULL;
        $product->purchase_price = $request->purchase_price;
        $product->whole_sale_price = $request->whole_sale_price;
        $product->selling_price = $request->selling_price;
        $product->vat_status = $request->vat_status;
        $product->vat_percentage = $vat_percentage;
        $product->vat_amount = $vat_amount;
        $product->vat_whole_amount = $vat_whole_amount;
        $product->note = $request->note ? $request->note : NULL;
        $product->date = $request->date;
        $product->status = $request->status;
        $product->image = $image;
        $update_product = $product->save();

        if($update_product){
            return response()->json(['success'=>true,'response' => $product], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'Product Not Updated Successfully!'], $this->failStatus);
        }
    }

    public function productDelete(Request $request){
        $check_exists_product = DB::table("products")->where('id',$request->product_id)->pluck('id')->first();
        if($check_exists_product == null){
            return response()->json(['success'=>false,'response'=>'No Product Found!'], $this->failStatus);
        }

        //$delete_product = DB::table("products")->where('id',$request->product_id)->delete();
        $soft_delete_product = Product::find($request->product_id);
        $soft_delete_product->status=0;
        $affected_row = $soft_delete_product->update();
        if($affected_row)
        {
            return response()->json(['success'=>true,'response' => 'Product Successfully Soft Deleted!'], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Product Deleted!'], $this->failStatus);
        }
    }

    // delivery service
//    public function deliveryServiceList(){
//        $delivery_services = DB::table('delivery_services')->select('id','name','status')->get();
//
//        if($delivery_services)
//        {
//            $success['delivery_services'] =  $delivery_services;
//            return response()->json(['success'=>true,'response' => $success], $this->successStatus);
//        }else{
//            return response()->json(['success'=>false,'response'=>'No Delivery Services List Found!'], $this->failStatus);
//        }
//    }
//
//
//    public function deliveryServiceCreate(Request $request){
//
//        $validator = Validator::make($request->all(), [
//            'name' => 'required|unique:delivery_services,name',
//            'status'=> 'required',
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
//        $deliveryService = new deliveryService();
//        $deliveryService->name = $request->name;
//        $deliveryService->status = $request->status;
//        $deliveryService->save();
//        $insert_id = $deliveryService->id;
//
//        if($insert_id){
//            return response()->json(['success'=>true,'response' => $deliveryService], $this->successStatus);
//        }else{
//            return response()->json(['success'=>false,'response'=>'Delivery Service Not Created Successfully!'], $this->failStatus);
//        }
//    }
//
//    public function deliveryServiceEdit(Request $request){
//
//        $validator = Validator::make($request->all(), [
//            'delivery_service_id'=> 'required',
//            'name' => 'required|unique:delivery_services,name,'.$request->delivery_service_id,
//            'status'=> 'required',
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
//        $check_exists_delivery_service = DB::table("delivery_services")->where('id',$request->delivery_service_id)->pluck('id')->first();
//        if($check_exists_delivery_service == null){
//            return response()->json(['success'=>false,'response'=>'No Delivery Service Found!'], $this->failStatus);
//        }
//
//        $deliveryService = deliveryService::find($request->delivery_service_id);
//        $deliveryService->name = $request->name;
//        $deliveryService->status = $request->status;
//        $update_delivery_service = $deliveryService->save();
//
//        if($update_delivery_service){
//            return response()->json(['success'=>true,'response' => $deliveryService], $this->successStatus);
//        }else{
//            return response()->json(['success'=>false,'response'=>'Delivery Service Not Created Successfully!'], $this->failStatus);
//        }
//    }
//
//    public function deliveryServiceDelete(Request $request){
//        $check_exists_delivery_service = DB::table("delivery_services")->where('id',$request->delivery_service_id)->pluck('id')->first();
//        if($check_exists_delivery_service == null){
//            return response()->json(['success'=>false,'response'=>'No Delivery Service Found!'], $this->failStatus);
//        }
//
//        //$delete_party = DB::table("product_brands")->where('id',$request->product_brand_id)->delete();
//        $soft_delete_delivery_service = deliveryService::find($request->delivery_service_id);
//        $soft_delete_delivery_service->status=0;
//        $affected_row = $soft_delete_delivery_service->update();
//        if($affected_row)
//        {
//            return response()->json(['success'=>true,'response' => 'Delivery Service Successfully Soft Deleted!'], $this->successStatus);
//        }else{
//            return response()->json(['success'=>false,'response'=>'No Product Brand Deleted!'], $this->failStatus);
//        }
//    }

    public function productUnitAndBrand(Request $request){
        $product_brand_and_unit = DB::table('products')
            ->leftJoin('product_units','products.product_unit_id','product_units.id')
            ->leftJoin('product_brands','products.product_brand_id','product_brands.id')
            ->where('products.id',$request->product_id)
            ->select('products.purchase_price','products.selling_price','product_units.id as product_unit_id','product_units.name as product_unit_name','product_brands.id as product_brand_id','product_brands.name as product_brand_name')
            ->get();

        if($product_brand_and_unit)
        {
            $success['product_brand_and_unit'] =  $product_brand_and_unit;
            return response()->json(['success'=>true,'response' => $success], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Product List Found!'], $this->failStatus);
        }
    }

    public function productWholePurchaseList(){
        $product_whole_purchases = DB::table('product_purchases')
            ->leftJoin('users','product_purchases.user_id','users.id')
            ->leftJoin('parties','product_purchases.party_id','parties.id')
            ->leftJoin('warehouses','product_purchases.warehouse_id','warehouses.id')
            ->where('product_purchases.purchase_type','whole_purchase')
            ->select('product_purchases.id','product_purchases.invoice_no','product_purchases.discount_type','product_purchases.discount_amount','product_purchases.total_amount','product_purchases.paid_amount','product_purchases.due_amount','product_purchases.purchase_date_time','users.name as user_name','parties.id as supplier_id','parties.name as supplier_name','warehouses.id as warehouse_id','warehouses.name as warehouse_name')
            ->orderBy('product_purchases.id','desc')
            ->get();

        if(count($product_whole_purchases) > 0)
        {
            $product_whole_purchase_arr = [];
            foreach ($product_whole_purchases as $data){
                $payment_type = DB::table('transactions')->where('ref_id',$data->id)->where('transaction_type','whole_purchase')->pluck('payment_type')->first();

                $nested_data['id']=$data->id;
                $nested_data['invoice_no']=$data->invoice_no;
                $nested_data['discount_type']=$data->discount_type;
                $nested_data['discount_amount']=$data->discount_amount;
                //$nested_data['total_vat_amount']=$data->total_vat_amount;
                $nested_data['total_amount']=$data->total_amount;
                $nested_data['paid_amount']=$data->paid_amount;
                $nested_data['due_amount']=$data->due_amount;
                $nested_data['purchase_date_time']=$data->purchase_date_time;
                $nested_data['user_name']=$data->user_name;
                $nested_data['supplier_id']=$data->supplier_id;
                $nested_data['supplier_name']=$data->supplier_name;
                $nested_data['warehouse_id']=$data->warehouse_id;
                $nested_data['warehouse_name']=$data->warehouse_name;
                $nested_data['payment_type']=$payment_type;

                array_push($product_whole_purchase_arr,$nested_data);
            }
            $success['product_whole_purchases'] =  $product_whole_purchase_arr;
            return response()->json(['success'=>true,'response' => $success], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Product Whole Purchase List Found!'], $this->failStatus);
        }
    }

    public function productWholePurchaseDetails(Request $request){
        $product_purchase_details = DB::table('product_purchases')
            ->join('product_purchase_details','product_purchases.id','product_purchase_details.product_purchase_id')
            ->leftJoin('products','product_purchase_details.product_id','products.id')
            ->leftJoin('product_units','product_purchase_details.product_unit_id','product_units.id')
            ->leftJoin('product_brands','product_purchase_details.product_brand_id','product_brands.id')
            ->where('product_purchases.id',$request->product_purchase_id)
            ->select('products.id as product_id','products.name as product_name','product_units.id as product_unit_id','product_units.name as product_unit_name','product_brands.id as product_brand_id','product_brands.name as product_brand_name','product_purchase_details.qty','product_purchase_details.id as product_purchase_detail_id','product_purchase_details.price','product_purchase_details.mrp_price')
            ->get();

        if($product_purchase_details)
        {
            $success['product_whole_purchase_details'] =  $product_purchase_details;
            return response()->json(['success'=>true,'response' => $success], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Product Whole Purchase Detail Found!'], $this->failStatus);
        }
    }

    public function productWholePurchaseCreate(Request $request){


//        $test = [
//            "party_id" => 15,
//            "warehouse_id" => 1,
//            "products" => [
//                0 =>
//                    [
//                        "product_id" => 4,
//                        "product_name" => "test product",
//                        "product_unit_id" => 1,
//                        "product_unit_name" => "Pcs",
//                        "product_brand_id" => 4,
//                        "product_brand_name" => "Brand Test",
//                        "price" => 200,
//                        "mrp_price" => 250,
//                        "qty" => 1,
//                    ],
//                1 =>
//                    [
//                        "product_id" => 6,
//                        "product_name" => "test product 1",
//                        "product_unit_id" => 2,
//                        "product_unit_name" => "Set",
//                        "product_brand_id" => 5,
//                        "product_brand_name" => "Brand Test 1",
//                        "price" => 300,
//                        "mrp_price" => 350,
//                        "qty" => 2,
//                    ]
//            ],
//            "total_amount" => 500,
//            "paid_amount" => 400,
//            "due_amount" => 100,
//            "payment_type" => "Cash"
//        ];

//        dd($test['products'][0]['product_id']);

//        [0=>["product_id" => 4,"product_name" => "test product","product_unit_id" => 1,"product_unit_name" => "Pcs","product_brand_id" => 4,"product_brand_name" => "Brand Test","price" => 200,"qty" => 1], 1=>["product_id" => 6,"product_name" => "test product 1","product_unit_id" => 2,"product_unit_name" => "Set","product_brand_id" => 5,"product_brand_name" => "Brand Test 1","qty" => 2,]]




        //dd($request->all());
        //return response()->json(['success'=>true,'response' => $request->all()], $this->successStatus);








        $this->validate($request, [
            //'user_id'=> 'required',
            'party_id'=> 'required',
            'warehouse_id'=> 'required',
            'paid_amount'=> 'required',
            'due_amount'=> 'required',
            'total_amount'=> 'required',
            'payment_type'=> 'required',
            //'product_unit_id'=> 'required',
            //'product_id'=> 'required',
            //'qty'=> 'required',
            //'price'=> 'required',
            //'mrp_price'=> 'required'
        ]);

//        $row_count = count($request->product_id);
//        $total_amount = 0;
//        for($i=0; $i<$row_count;$i++)
//        {
//            $total_amount += $request->qty[$i]*$request->price[$i];
//        }

//        $total_amount = 0;
//        foreach ($request->products as $data) {
//            $total_amount += $data['qty']*$data['price'];
//        }

        $get_invoice_no = ProductPurchase::latest()->pluck('invoice_no')->first();
        if(!empty($get_invoice_no)){
            $get_invoice = str_replace("purchase-","",$get_invoice_no);
            $invoice_no = $get_invoice+1;
        }else{
            $invoice_no = 1000;
        }
        $final_invoice = 'purchase-'.$invoice_no;

        $date = date('Y-m-d');
        $date_time = date('Y-m-d h:i:s');

        $user_id = Auth::user()->id;

        // product purchase
        $productPurchase = new ProductPurchase();
        $productPurchase ->invoice_no = $final_invoice;
        $productPurchase ->user_id = $user_id;
        $productPurchase ->party_id = $request->party_id;
        $productPurchase ->warehouse_id = $request->warehouse_id;
        $productPurchase ->purchase_type = 'whole_purchase';
        $productPurchase ->discount_type = $request->discount_type ? $request->discount_type : NULL;
        $productPurchase ->discount_amount = $request->discount_amount ? $request->discount_amount : 0;
        $productPurchase ->paid_amount = $request->paid_amount;
        $productPurchase ->due_amount = $request->due_amount;
        $productPurchase ->total_amount = $request->total_amount;
        $productPurchase ->purchase_date = $date;
        $productPurchase ->purchase_date_time = $date_time;
        $productPurchase->save();
        $insert_id = $productPurchase->id;

        if($insert_id)
        {
//            for($i=0; $i<$row_count;$i++)
//            {
//                $product_id = $request['products']['product_id'][$i];
//
//                $barcode = Product::where('id',$product_id)->pluck('barcode')->first();
//
//                // product purchase detail
//                $purchase_purchase_detail = new ProductPurchaseDetail();
//                $purchase_purchase_detail->product_purchase_id = $insert_id;
//                $purchase_purchase_detail->product_unit_id = $request->product_unit_id[$i];
//                $purchase_purchase_detail->product_brand_id = $request->product_brand_id[$i] ? $request->product_brand_id[$i] : NULL;
//                $purchase_purchase_detail->product_id = $request->product_id[$i];
//                $purchase_purchase_detail->qty = $request->qty[$i];
//                $purchase_purchase_detail->price = $request->price[$i];
//                $purchase_purchase_detail->mrp_price = $request->mrp_price[$i];
//                $purchase_purchase_detail->sub_total = $request->qty[$i]*$request->price[$i];
//                $purchase_purchase_detail->barcode = $barcode;
//                $purchase_purchase_detail->save();
//
//                $check_previous_stock = Stock::where('product_id',$product_id)->latest()->pluck('current_stock')->first();
//                if(!empty($check_previous_stock)){
//                    $previous_stock = $check_previous_stock;
//                }else{
//                    $previous_stock = 0;
//                }
//
//                // product stock
//                $stock = new Stock();
//                $stock->ref_id = $insert_id;
//                $stock->user_id = $user_id;
//                $stock->warehouse_id = $request->warehouse_id;
//                $stock->product_id = $request->product_id[$i];
//                $stock->product_unit_id = $request->product_unit_id[$i];
//                $stock->product_brand_id = $request->product_brand_id[$i] ? $request->product_brand_id[$i] : NULL;
//                $stock->stock_type = 'whole_purchase';
//                $stock->previous_stock = $previous_stock;
//                $stock->stock_in = $request->qty[$i];
//                $stock->stock_out = 0;
//                $stock->current_stock = $previous_stock + $request->qty[$i];
//                $stock->stock_date = $date;
//                $stock->stock_date_time = $date_time;
//                $stock->save();
//            }

            foreach ($request->products as $data) {

                $product_id =  $data['product_id'];

                $barcode = Product::where('id',$product_id)->pluck('barcode')->first();

                // product purchase detail
                $purchase_purchase_detail = new ProductPurchaseDetail();
                $purchase_purchase_detail->product_purchase_id = $insert_id;
                $purchase_purchase_detail->product_unit_id = $data['product_unit_id'];
                $purchase_purchase_detail->product_brand_id = $data['product_brand_id'] ? $data['product_brand_id'] : NULL;
                $purchase_purchase_detail->product_id = $product_id;
                $purchase_purchase_detail->qty = $data['qty'];
                $purchase_purchase_detail->price = $data['price'];
                $purchase_purchase_detail->mrp_price = $data['mrp_price'];
                $purchase_purchase_detail->sub_total = $data['qty']*$data['price'];
                $purchase_purchase_detail->barcode = $barcode;
                $purchase_purchase_detail->save();

                $check_previous_stock = Stock::where('product_id',$product_id)->latest()->pluck('current_stock')->first();
                if(!empty($check_previous_stock)){
                    $previous_stock = $check_previous_stock;
                }else{
                    $previous_stock = 0;
                }

                // product stock
                $stock = new Stock();
                $stock->ref_id = $insert_id;
                $stock->user_id = $user_id;
                $stock->warehouse_id = $request->warehouse_id;
                $stock->product_id = $product_id;
                $stock->product_unit_id = $data['product_unit_id'];
                $stock->product_brand_id = $data['product_brand_id'] ? $data['product_brand_id'] : NULL;
                $stock->stock_type = 'whole_purchase';
                $stock->stock_where = 'warehouse';
                $stock->stock_in_out = 'stock_in';
                $stock->previous_stock = $previous_stock;
                $stock->stock_in = $data['qty'];
                $stock->stock_out = 0;
                $stock->current_stock = $previous_stock + $data['qty'];
                $stock->stock_date = $date;
                $stock->stock_date_time = $date_time;
                $stock->save();

                // warehouse current stock
                $check_exists_warehouse_current_stock = WarehouseCurrentStock::where('warehouse_id',$request->warehouse_id)
                    ->where('product_id',$product_id)
                    ->first();
                if($check_exists_warehouse_current_stock){
                    $warehouse_current_stock_update = WarehouseCurrentStock::find($check_exists_warehouse_current_stock->id);
                    $warehouse_current_stock_update->current_stock=$check_exists_warehouse_current_stock->current_stock + $data['qty'];
                    $warehouse_current_stock_update->save();
                }else{
                    $warehouse_current_stock = new WarehouseCurrentStock();
                    $warehouse_current_stock->warehouse_id=$request->warehouse_id;
                    $warehouse_current_stock->product_id=$product_id;
                    $warehouse_current_stock->current_stock=$data['qty'];
                    $warehouse_current_stock->save();
                }
            }

            // transaction
            $transaction = new Transaction();
            $transaction->ref_id = $insert_id;
            $transaction->invoice_no = $final_invoice;
            $transaction->user_id = $user_id;
            $transaction->warehouse_id = $request->warehouse_id;
            $transaction->party_id = $request->party_id;
            $transaction->transaction_type = 'whole_purchase';
            $transaction->payment_type = $request->payment_type;
            $transaction->amount = $request->paid_amount;
            $transaction->transaction_date = $date;
            $transaction->transaction_date_time = $date_time;
            $transaction->save();
            $transaction_id = $transaction->id;

            // payment paid
            $payment_paid = new PaymentPaid();
            $payment_paid->invoice_no = $final_invoice;
            $payment_paid->product_purchase_id = $insert_id;
            $payment_paid->user_id = $user_id;
            $payment_paid->party_id = $request->party_id;
            $payment_paid->paid_type = 'Purchase';
            $payment_paid->paid_amount = $request->paid_amount;
            $payment_paid->due_amount = $request->due_amount;
            $payment_paid->current_paid_amount = $request->paid_amount;
            $payment_paid->paid_date = $date;
            $payment_paid->paid_date_time = $date_time;
            $payment_paid->save();


            if($request->payment_type == 'SSL Commerz'){
                return response()->json(['success'=>true,'transaction_id' => $transaction_id,'payment_type' => $request->payment_type], $this->successStatus);
            }else{
                return response()->json(['success'=>true,'response' => 'Inserted Successfully.'], $this->successStatus);
            }
        }else{
            return response()->json(['success'=>false,'response'=>'No Role Created!'], $this->failStatus);
        }
    }

    public function productWholePurchaseEdit(Request $request){
        //dd($request->all());
        $this->validate($request, [
            'product_purchase_id'=> 'required',
            'party_id'=> 'required',
            'warehouse_id'=> 'required',
            'paid_amount'=> 'required',
            'due_amount'=> 'required',
            'total_amount'=> 'required',
            'payment_type'=> 'required',
            //'product_id'=> 'required',
            //'product_unit_id'=> 'required',
            //'qty'=> 'required',
            //'price'=> 'required',
            //'mrp_price'=> 'required'
        ]);

//        $total_amount = 0;
//        foreach ($request->products as $data) {
//            $total_amount += $data['qty']*$data['price'];
//        }

        $user_id = Auth::user()->id;

        // product purchase
        $productPurchase = ProductPurchase::find($request->product_purchase_id);
        $productPurchase ->user_id = $user_id;
        $productPurchase ->party_id = $request->party_id;
        $productPurchase ->warehouse_id = $request->warehouse_id;
        $productPurchase ->discount_type = $request->discount_type ? $request->discount_type : NULL;
        $productPurchase ->discount_amount = $request->discount_amount ? $request->discount_amount : 0;
        $productPurchase ->paid_amount = $request->paid_amount;
        $productPurchase ->due_amount = $request->due_amount;
        $productPurchase ->total_amount = $request->total_amount;
        $productPurchase->update();
        $affectedRows = $productPurchase->id;
        if($affectedRows)
        {
            foreach ($request->products as $data) {
                $product_id = $data['product_id'];
                $barcode = Product::where('id',$product_id)->pluck('barcode')->first();

                $product_purchase_detail_id = $data['product_purchase_detail_id'];
                // product purchase detail
                $purchase_purchase_detail = ProductPurchaseDetail::find($product_purchase_detail_id);
                $purchase_purchase_detail->product_unit_id = $data['product_unit_id'];
                $purchase_purchase_detail->product_brand_id = $data['product_brand_id'] ? $data['product_brand_id'] : NULL;
                $purchase_purchase_detail->product_id = $product_id;
                $purchase_purchase_detail->qty = $data['qty'];
                $purchase_purchase_detail->price = $data['price'];
                $purchase_purchase_detail->mrp_price = $data['mrp_price'];
                $purchase_purchase_detail->sub_total = $data['qty']*$data['price'];
                $purchase_purchase_detail->barcode = $barcode;
                $purchase_purchase_detail->update();


                // product stock
                $stock_row = Stock::where('ref_id',$request->product_purchase_id)->where('stock_type','whole_purchase')->where('product_id',$product_id)->first();


                // warehouse current stock
                $warehouse_current_stock_update = WarehouseCurrentStock::where('warehouse_id',$request->warehouse_id)
                    ->where('product_id',$product_id)
                    ->first();
                $exists_current_stock = $warehouse_current_stock_update->current_stock;

                if($stock_row->stock_in != $data['qty']){

                    if($data['qty'] > $stock_row->stock_in){
                        $add_or_minus_stock_in = $data['qty'] - $stock_row->stock_in;
                        $update_stock_in = $stock_row->stock_in + $add_or_minus_stock_in;
                        $update_current_stock = $stock_row->current_stock + $add_or_minus_stock_in;

                        // warehouse current stock
                        $update_warehouse_current_stock = $exists_current_stock + $add_or_minus_stock_in;
                    }else{
                        $add_or_minus_stock_in =  $stock_row->stock_in - $data['qty'];
                        $update_stock_in = $stock_row->stock_in - $add_or_minus_stock_in;
                        $update_current_stock = $stock_row->current_stock - $add_or_minus_stock_in;

                        // warehouse current stock
                        $update_warehouse_current_stock = $exists_current_stock - $add_or_minus_stock_in;
                    }

                    $stock_row->user_id = $user_id;
                    $stock_row->stock_in = $update_stock_in;
                    $stock_row->current_stock = $update_current_stock;
                    $stock_row->update();

                    // warehouse current stock
                    $warehouse_current_stock_update->current_stock=$update_warehouse_current_stock;
                    $warehouse_current_stock_update->save();
                }
            }

            // transaction
            $transaction = Transaction::where('ref_id',$request->product_purchase_id)->first();
            $transaction->user_id = $user_id;
            $transaction->warehouse_id = $request->warehouse_id;
            $transaction->party_id = $request->party_id;
            $transaction->payment_type = $request->payment_type;
            $transaction->amount = $request->paid_amount;
            $transaction->update();

            // payment paid
            $payment_paid = PaymentPaid::where('product_purchase_id',$request->product_purchase_id)->first();
            $payment_paid->user_id = $user_id;
            $payment_paid->party_id = $request->party_id;
            $payment_paid->paid_amount = $request->paid_amount;
            $payment_paid->due_amount = $request->due_amount;
            $payment_paid->current_paid_amount = $request->paid_amount;
            $payment_paid->update();


            return response()->json(['success'=>true,'response' => 'Updated Successfully.'], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Updated Successfully!'], $this->failStatus);
        }
    }

    public function productWholePurchaseDelete(Request $request){
        $check_exists_product_purchase = DB::table("product_purchases")->where('id',$request->product_purchase_id)->pluck('id')->first();
        if($check_exists_product_purchase == null){
            return response()->json(['success'=>false,'response'=>'No Product Purchase Found!'], $this->failStatus);
        }

        $productPurchase = ProductPurchase::find($request->product_purchase_id);
        $delete_purchase = $productPurchase->delete();

        DB::table('product_purchase_details')->where('product_purchase_id',$request->product_purchase_id)->delete();
        DB::table('stocks')->where('ref_id',$request->product_purchase_id)->delete();
        DB::table('transactions')->where('ref_id',$request->product_purchase_id)->delete();
        DB::table('payment_paids')->where('product_purchase_id',$request->product_purchase_id)->delete();

        if($delete_purchase)
        {
            return response()->json(['success'=>true,'response' => 'Purchase Successfully Deleted!'], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Purchase Deleted!'], $this->failStatus);
        }
    }

    public function productPOSPurchaseList(){
        $product_pos_purchases = DB::table('product_purchases')
            ->leftJoin('users','product_purchases.user_id','users.id')
            ->leftJoin('parties','product_purchases.party_id','parties.id')
            ->leftJoin('warehouses','product_purchases.warehouse_id','warehouses.id')
            ->where('product_purchases.purchase_type','pos_purchase')
            ->select('product_purchases.id','product_purchases.invoice_no','product_purchases.discount_type','product_purchases.discount_amount','product_purchases.total_amount','product_purchases.paid_amount','product_purchases.due_amount','product_purchases.purchase_date_time','users.name as user_name','parties.id as supplier_id','parties.name as supplier_name','warehouses.id as warehouse_id','warehouses.name as warehouse_name')
            ->orderBy('product_purchases.id','desc')
            ->get();

        if($product_pos_purchases)
        {
            $product_pos_purchases_arr = [];
            foreach ($product_pos_purchases as $data){
                $payment_type = DB::table('transactions')->where('ref_id',$data->id)->where('transaction_type','pos_purchase')->pluck('payment_type')->first();

                $nested_data['id']=$data->id;
                $nested_data['invoice_no']=$data->invoice_no;
                $nested_data['discount_type']=$data->discount_type;
                $nested_data['discount_amount']=$data->discount_amount;
                $nested_data['total_amount']=$data->total_amount;
                $nested_data['paid_amount']=$data->paid_amount;
                $nested_data['due_amount']=$data->due_amount;
                $nested_data['purchase_date_time']=$data->purchase_date_time;
                $nested_data['user_name']=$data->user_name;
                $nested_data['supplier_id']=$data->supplier_id;
                $nested_data['supplier_name']=$data->supplier_name;
                $nested_data['warehouse_id']=$data->warehouse_id;
                $nested_data['warehouse_name']=$data->warehouse_name;
                $nested_data['payment_type']=$payment_type;

                array_push($product_pos_purchases_arr,$nested_data);
            }

            $success['product_pos_purchases'] =  $product_pos_purchases_arr;
            return response()->json(['success'=>true,'response' => $success], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Product POS Purchase List Found!'], $this->failStatus);
        }
    }

    public function productPOSPurchaseDetails(Request $request){
        $product_pos_purchase_details = DB::table('product_purchases')
            ->join('product_purchase_details','product_purchases.id','product_purchase_details.product_purchase_id')
            ->leftJoin('products','product_purchase_details.product_id','products.id')
            ->leftJoin('product_units','product_purchase_details.product_unit_id','product_units.id')
            ->leftJoin('product_brands','product_purchase_details.product_brand_id','product_brands.id')
            ->where('product_purchases.id',$request->product_purchase_id)
            ->select('products.id as product_id','products.name as product_name','product_units.id as product_unit_id','product_units.name as product_unit_name','product_brands.id as product_brand_id','product_brands.name as product_brand_name','product_purchase_details.qty','product_purchase_details.id as product_purchase_detail_id','product_purchase_details.price','product_purchase_details.mrp_price')
            ->orderBy('product_purchases.id','desc')
            ->get();

        if($product_pos_purchase_details)
        {
            $success['product_pos_purchase_details'] =  $product_pos_purchase_details;
            return response()->json(['success'=>true,'response' => $success], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Product POS Purchase Detail Found!'], $this->failStatus);
        }
    }

    public function productPOSPurchaseCreate(Request $request){

        $this->validate($request, [
            'party_id'=> 'required',
            'warehouse_id'=> 'required',
            'paid_amount'=> 'required',
            'due_amount'=> 'required',
            'total_amount'=> 'required',
            'payment_type'=> 'required',
        ]);

        $get_invoice_no = ProductPurchase::latest()->pluck('invoice_no')->first();
        if(!empty($get_invoice_no)){
            $get_invoice = str_replace("purchase-","",$get_invoice_no);
            $invoice_no = $get_invoice+1;
        }else{
            $invoice_no = 1000;
        }
        $final_invoice = 'purchase-'.$invoice_no;

        $date = date('Y-m-d');
        $date_time = date('Y-m-d h:i:s');

        $user_id = Auth::user()->id;

        // product purchase
        $productPurchase = new ProductPurchase();
        $productPurchase ->invoice_no = $final_invoice;
        $productPurchase ->user_id = $user_id;
        $productPurchase ->party_id = $request->party_id;
        $productPurchase ->warehouse_id = $request->warehouse_id;
        $productPurchase ->purchase_type = 'pos_purchase';
        $productPurchase ->discount_type = $request->discount_type ? $request->discount_type : NULL;
        $productPurchase ->discount_amount = $request->discount_amount ? $request->discount_amount : 0;
        $productPurchase ->paid_amount = $request->paid_amount;
        $productPurchase ->due_amount = $request->due_amount;
        $productPurchase ->total_amount = $request->total_amount;
        $productPurchase ->purchase_date = $date;
        $productPurchase ->purchase_date_time = $date_time;
        $productPurchase->save();
        $insert_id = $productPurchase->id;

        if($insert_id)
        {
            foreach ($request->products as $data) {

                $product_id =  $data['product_id'];

                $barcode = Product::where('id',$product_id)->pluck('barcode')->first();

                // product purchase detail
                $purchase_purchase_detail = new ProductPurchaseDetail();
                $purchase_purchase_detail->product_purchase_id = $insert_id;
                $purchase_purchase_detail->product_unit_id = $data['product_unit_id'];
                $purchase_purchase_detail->product_brand_id = $data['product_brand_id'] ? $data['product_brand_id'] : NULL;
                $purchase_purchase_detail->product_id = $product_id;
                $purchase_purchase_detail->qty = $data['qty'];
                $purchase_purchase_detail->price = $data['price'];
                $purchase_purchase_detail->mrp_price = $data['mrp_price'];
                $purchase_purchase_detail->sub_total = $data['qty']*$data['price'];
                $purchase_purchase_detail->barcode = $barcode;
                $purchase_purchase_detail->save();

                $check_previous_stock = Stock::where('product_id',$product_id)->latest()->pluck('current_stock')->first();
                if(!empty($check_previous_stock)){
                    $previous_stock = $check_previous_stock;
                }else{
                    $previous_stock = 0;
                }

                // product stock
                $stock = new Stock();
                $stock->ref_id = $insert_id;
                $stock->user_id = $user_id;
                $stock->warehouse_id = $request->warehouse_id;
                $stock->product_id = $product_id;
                $stock->product_unit_id = $data['product_unit_id'];
                $stock->product_brand_id = $data['product_brand_id'] ? $data['product_brand_id'] : NULL;
                $stock->stock_type = 'pos_purchase';
                $stock->stock_where = 'warehouse';
                $stock->stock_in_out = 'stock_in';
                $stock->previous_stock = $previous_stock;
                $stock->stock_in = $data['qty'];
                $stock->stock_out = 0;
                $stock->current_stock = $previous_stock + $data['qty'];
                $stock->stock_date = $date;
                $stock->stock_date_time = $date_time;
                $stock->save();

                // warehouse current stock
                $check_exists_warehouse_current_stock = WarehouseCurrentStock::where('warehouse_id',$request->warehouse_id)
                    ->where('product_id',$product_id)
                    ->first();
                if($check_exists_warehouse_current_stock){
                    $warehouse_current_stock_update = WarehouseCurrentStock::find($check_exists_warehouse_current_stock->id);
                    $warehouse_current_stock_update->current_stock=$check_exists_warehouse_current_stock->current_stock + $data['qty'];
                    $warehouse_current_stock_update->save();
                }else{
                    $warehouse_current_stock = new WarehouseCurrentStock();
                    $warehouse_current_stock->warehouse_id=$request->warehouse_id;
                    $warehouse_current_stock->product_id=$product_id;
                    $warehouse_current_stock->current_stock=$data['qty'];
                    $warehouse_current_stock->save();
                }
            }

            // transaction
            $transaction = new Transaction();
            $transaction->ref_id = $insert_id;
            $transaction->invoice_no = $final_invoice;
            $transaction->user_id = $user_id;
            $transaction->warehouse_id = $request->warehouse_id;
            $transaction->party_id = $request->party_id;
            $transaction->transaction_type = 'pos_purchase';
            $transaction->payment_type = $request->payment_type;
            $transaction->amount = $request->paid_amount;
            $transaction->transaction_date = $date;
            $transaction->transaction_date_time = $date_time;
            $transaction->save();
            $transaction_id = $transaction->id;

            // payment paid
            $payment_paid = new PaymentPaid();
            $payment_paid->invoice_no = $final_invoice;
            $payment_paid->product_purchase_id = $insert_id;
            $payment_paid->user_id = $user_id;
            $payment_paid->party_id = $request->party_id;
            $payment_paid->paid_type = 'Purchase';
            $payment_paid->paid_amount = $request->paid_amount;
            $payment_paid->due_amount = $request->due_amount;
            $payment_paid->current_paid_amount = $request->paid_amount;
            $payment_paid->paid_date = $date;
            $payment_paid->paid_date_time = $date_time;
            $payment_paid->save();


            if($request->payment_type == 'SSL Commerz'){
                return response()->json(['success'=>true,'transaction_id' => $transaction_id,'payment_type' => $request->payment_type], $this->successStatus);
            }else{
                return response()->json(['success'=>true,'response' => 'Inserted Successfully.'], $this->successStatus);
            }
        }else{
            return response()->json(['success'=>false,'response'=>'No Role Created!'], $this->failStatus);
        }
    }

    public function productPOSPurchaseEdit(Request $request){
        $this->validate($request, [
            'product_purchase_id'=> 'required',
            'party_id'=> 'required',
            'warehouse_id'=> 'required',
            'paid_amount'=> 'required',
            'due_amount'=> 'required',
            'total_amount'=> 'required',
            'payment_type'=> 'required',
        ]);

        $user_id = Auth::user()->id;

        // product purchase
        $productPurchase = ProductPurchase::find($request->product_purchase_id);
        $productPurchase ->user_id = $user_id;
        $productPurchase ->party_id = $request->party_id;
        $productPurchase ->warehouse_id = $request->warehouse_id;
        $productPurchase ->discount_type = $request->discount_type ? $request->discount_type : NULL;
        $productPurchase ->discount_amount = $request->discount_amount ? $request->discount_amount : 0;
        $productPurchase ->paid_amount = $request->paid_amount;
        $productPurchase ->due_amount = $request->due_amount;
        $productPurchase ->total_amount = $request->total_amount;
        $productPurchase->update();
        $affectedRows = $productPurchase->id;
        if($affectedRows)
        {
            foreach ($request->products as $data) {
                $product_id = $data['product_id'];
                $barcode = Product::where('id',$product_id)->pluck('barcode')->first();

                $product_purchase_detail_id = $data['product_purchase_detail_id'];
                // product purchase detail
                $purchase_purchase_detail = ProductPurchaseDetail::find($product_purchase_detail_id);
                $purchase_purchase_detail->product_unit_id = $data['product_unit_id'];
                $purchase_purchase_detail->product_brand_id = $data['product_brand_id'] ? $data['product_brand_id'] : NULL;
                $purchase_purchase_detail->product_id = $product_id;
                $purchase_purchase_detail->qty = $data['qty'];
                $purchase_purchase_detail->price = $data['price'];
                $purchase_purchase_detail->mrp_price = $data['mrp_price'];
                $purchase_purchase_detail->sub_total = $data['qty']*$data['price'];
                $purchase_purchase_detail->barcode = $barcode;
                $purchase_purchase_detail->update();


                // product stock
                $stock_row = Stock::where('ref_id',$request->product_purchase_id)->where('stock_type','pos_purchase')->where('product_id',$product_id)->first();

                // warehouse current stock
                $warehouse_current_stock_update = WarehouseCurrentStock::where('warehouse_id',$request->warehouse_id)
                    ->where('product_id',$product_id)
                    ->first();
                $exists_current_stock = $warehouse_current_stock_update->current_stock;

                if($stock_row->stock_in != $data['qty']){

                    if($data['qty'] > $stock_row->stock_in){
                        $add_or_minus_stock_in = $data['qty'] - $stock_row->stock_in;
                        $update_stock_in = $stock_row->stock_in + $add_or_minus_stock_in;
                        $update_current_stock = $stock_row->current_stock + $add_or_minus_stock_in;

                        // warehouse current stock
                        $update_warehouse_current_stock = $exists_current_stock + $add_or_minus_stock_in;
                    }else{
                        $add_or_minus_stock_in =  $stock_row->stock_in - $data['qty'];
                        $update_stock_in = $stock_row->stock_in - $add_or_minus_stock_in;
                        $update_current_stock = $stock_row->current_stock - $add_or_minus_stock_in;

                        // warehouse current stock
                        $update_warehouse_current_stock = $exists_current_stock - $add_or_minus_stock_in;
                    }

                    $stock_row->user_id = $user_id;
                    $stock_row->stock_in = $update_stock_in;
                    $stock_row->current_stock = $update_current_stock;
                    $stock_row->update();

                    // warehouse current stock
                    $warehouse_current_stock_update->current_stock=$update_warehouse_current_stock;
                    $warehouse_current_stock_update->save();
                }
            }

            // transaction
            $transaction = Transaction::where('ref_id',$request->product_purchase_id)->first();
            $transaction->user_id = $user_id;
            $transaction->warehouse_id = $request->warehouse_id;
            $transaction->party_id = $request->party_id;
            $transaction->payment_type = $request->payment_type;
            $transaction->amount = $request->paid_amount;
            $transaction->update();

            // payment paid
            $payment_paid = PaymentPaid::where('product_purchase_id',$request->product_purchase_id)->first();
            $payment_paid->user_id = $user_id;
            $payment_paid->party_id = $request->party_id;
            $payment_paid->paid_amount = $request->paid_amount;
            $payment_paid->due_amount = $request->due_amount;
            $payment_paid->current_paid_amount = $request->paid_amount;
            $payment_paid->update();


            return response()->json(['success'=>true,'response' => 'Updated Successfully.'], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Updated Successfully!'], $this->failStatus);
        }
    }

    public function productPOSPurchaseDelete(Request $request){
        $check_exists_product_purchase = DB::table("product_purchases")->where('id',$request->product_purchase_id)->pluck('id')->first();
        if($check_exists_product_purchase == null){
            return response()->json(['success'=>false,'response'=>'No Product Purchase Found!'], $this->failStatus);
        }

        $productPurchase = ProductPurchase::find($request->product_purchase_id);
        $delete_purchase = $productPurchase->delete();

        DB::table('product_purchase_details')->where('product_purchase_id',$request->product_purchase_id)->delete();
        DB::table('stocks')->where('ref_id',$request->product_purchase_id)->delete();
        DB::table('transactions')->where('ref_id',$request->product_purchase_id)->delete();
        DB::table('payment_paids')->where('product_purchase_id',$request->product_purchase_id)->delete();

        if($delete_purchase)
        {
            return response()->json(['success'=>true,'response' => 'Purchase Successfully Deleted!'], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Purchase Deleted!'], $this->failStatus);
        }
    }

    // product purchase invoice list
    public function productPurchaseInvoiceList(){
        $product_purchase_invoices = DB::table('product_purchases')
            ->select('id','invoice_no')
            ->get();

        if($product_purchase_invoices)
        {
            $success['product_purchase_invoices'] =  $product_purchase_invoices;
            return response()->json(['success'=>true,'response' => $success], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Product Purchase List Found!'], $this->failStatus);
        }
    }

    public function productPurchaseReturnCreate(Request $request){
        //dd($request->all());
        $this->validate($request, [
            'party_id'=> 'required',
            'warehouse_id'=> 'required',
            'paid_amount'=> 'required',
            'due_amount'=> 'required',
            'total_amount'=> 'required',
            'payment_type'=> 'required',
            'product_purchase_invoice_no'=> 'required',
        ]);

        $product_purchase_id = ProductPurchase::where('invoice_no',$request->product_purchase_invoice_no)->pluck('id')->first();

        $get_invoice_no = ProductPurchaseReturn::latest('id','desc')->pluck('invoice_no')->first();
        if(!empty($get_invoice_no)){
            $get_invoice = str_replace("purchase-return","",$get_invoice_no);
            $invoice_no = $get_invoice+1;
        }else{
            $invoice_no = 5000;
        }
        $final_invoice = 'purchase-return'.$invoice_no;

        $date = date('Y-m-d');
        $date_time = date('Y-m-d h:i:s');

        $user_id = Auth::user()->id;

        // product purchase
        $productPurchaseReturn = new ProductPurchaseReturn();
        $productPurchaseReturn ->invoice_no = $final_invoice;
        $productPurchaseReturn ->product_purchase_invoice_no = $request->product_purchase_invoice_no;
        $productPurchaseReturn ->user_id = $user_id;
        $productPurchaseReturn ->party_id = $request->party_id;
        $productPurchaseReturn ->warehouse_id = $request->warehouse_id;
        $productPurchaseReturn ->product_purchase_return_type = 'purchase_return';
        $productPurchaseReturn ->discount_type = $request->discount_type ? $request->discount_type : NULL;
        $productPurchaseReturn ->discount_amount = $request->discount_amount ? $request->discount_amount : 0;
        $productPurchaseReturn ->paid_amount = $request->total_amount;
        $productPurchaseReturn ->due_amount = $request->due_amount;
        $productPurchaseReturn ->total_amount = $request->total_amount;
        $productPurchaseReturn ->product_purchase_return_date = $date;
        $productPurchaseReturn ->product_purchase_return_date_time = $date_time;
        $productPurchaseReturn->save();
        $insert_id = $productPurchaseReturn->id;

        if($insert_id)
        {
            foreach ($request->products as $data) {

                $product_id =  $data['product_id'];

                $barcode = Product::where('id',$product_id)->pluck('barcode')->first();

                // product purchase detail
                $purchase_purchase_return_detail = new ProductPurchaseReturnDetail();
                $purchase_purchase_return_detail->pro_pur_return_id = $insert_id;
                $purchase_purchase_return_detail->pro_pur_detail_id = $data['product_purchase_detail_id'];
                $purchase_purchase_return_detail->product_unit_id = $data['product_unit_id'];
                $purchase_purchase_return_detail->product_brand_id = $data['product_brand_id'] ? $data['product_brand_id'] : NULL;
                $purchase_purchase_return_detail->product_id = $product_id;
                $purchase_purchase_return_detail->barcode = $barcode;
                $purchase_purchase_return_detail->qty = $data['qty'];
                $purchase_purchase_return_detail->price = $data['price'];
                $purchase_purchase_return_detail->sub_total = $data['qty']*$data['price'];
                $purchase_purchase_return_detail->save();

                $check_previous_stock = Stock::where('product_id',$product_id)->where('stock_where','warehouse')->latest('id','desc')->pluck('current_stock')->first();
                if(!empty($check_previous_stock)){
                    $previous_stock = $check_previous_stock;
                }else{
                    $previous_stock = 0;
                }

                // product stock
                $stock = new Stock();
                $stock->ref_id = $insert_id;
                $stock->user_id = $user_id;
                $stock->warehouse_id = $request->warehouse_id;
                $stock->product_id = $product_id;
                $stock->product_unit_id = $data['product_unit_id'];
                $stock->product_brand_id = $data['product_brand_id'] ? $data['product_brand_id'] : NULL;
                $stock->stock_type = 'purchase_return';
                $stock->stock_where = 'warehouse';
                $stock->stock_in_out = 'stock_out';
                $stock->previous_stock = $previous_stock;
                $stock->stock_in = 0;
                $stock->stock_out = $data['qty'];
                $stock->current_stock = $previous_stock - $data['qty'];
                $stock->stock_date = $date;
                $stock->stock_date_time = $date_time;
                $stock->save();

                // warehouse current stock
                $warehouse_current_stock_update = WarehouseCurrentStock::where('warehouse_id',$request->warehouse_id)
                    ->where('product_id',$product_id)
                    ->first();
                $exists_current_stock = $warehouse_current_stock_update->current_stock;
                $update_warehouse_current_stock = $exists_current_stock - $data['qty'];
                $warehouse_current_stock_update->current_stock=$update_warehouse_current_stock;
                $warehouse_current_stock_update->save();
            }

            // transaction
            $transaction = new Transaction();
            $transaction->ref_id = $insert_id;
            $transaction->invoice_no = $final_invoice;
            $transaction->user_id = $user_id;
            $transaction->warehouse_id = $request->warehouse_id;
            $transaction->party_id = $request->party_id;
            $transaction->transaction_type = 'purchase_return';
            $transaction->payment_type = $request->payment_type;
            $transaction->amount = $request->total_amount;
            $transaction->transaction_date = $date;
            $transaction->transaction_date_time = $date_time;
            $transaction->save();

            // payment paid
            $payment_paid = new PaymentPaid();
            $payment_paid->invoice_no = $final_invoice;
            $payment_paid->product_purchase_id = $product_purchase_id;
            $payment_paid->product_purchase_return_id = $insert_id;
            $payment_paid->user_id = $user_id;
            $payment_paid->party_id = $request->party_id;
            $payment_paid->paid_type = 'Return';
            $payment_paid->paid_amount = $request->total_amount;
            $payment_paid->due_amount = $request->due_amount;
            $payment_paid->current_paid_amount = $request->total_amount;
            $payment_paid->paid_date = $date;
            $payment_paid->paid_date_time = $date_time;
            $payment_paid->save();


            return response()->json(['success'=>true,'response' => 'Inserted Successfully.'], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Role Created!'], $this->failStatus);
        }
    }

    public function productPurchaseReturnDetails(Request $request){
        //dd($request->all());
        $this->validate($request, [
            'product_purchase_invoice_no'=> 'required',
        ]);

        $product_purchases = DB::table('product_purchases')
            ->leftJoin('users','product_purchases.user_id','users.id')
            ->leftJoin('parties','product_purchases.party_id','parties.id')
            ->leftJoin('warehouses','product_purchases.warehouse_id','warehouses.id')
            ->where('product_purchases.invoice_no',$request->product_purchase_invoice_no)
            ->select('product_purchases.id','product_purchases.invoice_no','product_purchases.discount_type','product_purchases.discount_amount','product_purchases.total_amount','product_purchases.paid_amount','product_purchases.due_amount','product_purchases.purchase_date_time','users.name as user_name','parties.id as supplier_id','parties.name as supplier_name','warehouses.id as warehouse_id','warehouses.name as warehouse_name')
            ->first();

        if($product_purchases){

            $product_pos_purchase_details = DB::table('product_purchases')
                ->join('product_purchase_details','product_purchases.id','product_purchase_details.product_purchase_id')
                ->leftJoin('products','product_purchase_details.product_id','products.id')
                ->leftJoin('product_units','product_purchase_details.product_unit_id','product_units.id')
                ->leftJoin('product_brands','product_purchase_details.product_brand_id','product_brands.id')
                ->where('product_purchases.invoice_no',$request->product_purchase_invoice_no)
                ->select('products.id as product_id','products.name as product_name','product_units.id as product_unit_id','product_units.name as product_unit_name','product_brands.id as product_brand_id','product_brands.name as product_brand_name','product_purchase_details.qty','product_purchase_details.qty as current_qty','product_purchase_details.id as product_purchase_detail_id','product_purchase_details.price','product_purchase_details.mrp_price')
                ->get();

            $success['product_purchases'] = $product_purchases;
            $success['product_pos_purchase_details'] = $product_pos_purchase_details;
            return response()->json(['success'=>true,'response' => $success], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Product Purchase Data Found!'], $this->failStatus);
        }
    }

    public function supplierList(){
        $supplier_lists = DB::table('parties')
            ->where('type','supplier')
            ->select('id','name')
            ->orderBy('id','desc')
            ->get();

        if(count($supplier_lists) > 0)
        {
            $success['supplier_lists'] =  $supplier_lists;
            return response()->json(['success'=>true,'response' => $success], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Supplier List Found!'], $this->failStatus);
        }
    }

    public function customerList(){
        $customer_lists = DB::table('parties')
            ->where('type','customer')
            ->select('id','name')
            ->orderBy('id','desc')
            ->get();

        if(count($customer_lists) > 0)
        {
            $success['customer_lists'] =  $customer_lists;
            return response()->json(['success'=>true,'response' => $success], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Customer List Found!'], $this->failStatus);
        }
    }

    public function wholeSaleCustomerList(){
        $customer_lists = DB::table('parties')
            ->where('type','customer')
            ->where('customer_type','Whole Sale')
            ->select('id','name')
            ->orderBy('id','desc')
            ->get();

        if(count($customer_lists) > 0)
        {
            $success['customer_lists'] =  $customer_lists;
            return response()->json(['success'=>true,'response' => $success], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Customer List Found!'], $this->failStatus);
        }
    }

    public function posSaleCustomerList(){
        $customer_lists = DB::table('parties')
            ->where('type','customer')
            ->where('customer_type','POS Sale')
            ->select('id','name')
            ->orderBy('id','desc')
            ->get();

        if(count($customer_lists) > 0)
        {
            $success['customer_lists'] =  $customer_lists;
            return response()->json(['success'=>true,'response' => $success], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Customer List Found!'], $this->failStatus);
        }
    }

    public function paymentPaidDueList(){
        $payment_paid_due_amount = DB::table('product_purchases')
            ->leftJoin('users','product_purchases.user_id','users.id')
            ->leftJoin('parties','product_purchases.party_id','parties.id')
            ->leftJoin('warehouses','product_purchases.warehouse_id','warehouses.id')
            ->where('product_purchases.due_amount','>',0)
            ->select('product_purchases.id','product_purchases.invoice_no','product_purchases.discount_type','product_purchases.discount_amount','product_purchases.total_amount','product_purchases.paid_amount','product_purchases.due_amount','product_purchases.purchase_date_time','users.name as user_name','parties.id as supplier_id','parties.name as supplier_name','warehouses.id as warehouse_id','warehouses.name as warehouse_name')
            ->orderBy('product_purchases.id','desc')
            ->paginate(12);

        if($payment_paid_due_amount)
        {
            $total_payment_paid_due_amount = 0;
            $sum_payment_paid_due_amount = DB::table('product_purchases')
                ->where('product_purchases.due_amount','>',0)
                ->select(DB::raw('SUM(due_amount) as total_payment_paid_due_amount'))
                ->first();
            if($sum_payment_paid_due_amount){
                $total_payment_paid_due_amount = $sum_payment_paid_due_amount->total_payment_paid_due_amount;
            }

            $success['payment_paid_due_amount'] =  $payment_paid_due_amount;
            return response()->json(['success'=>true,'response' => $success,'total_payment_paid_due_amount'=>$total_payment_paid_due_amount], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Payment Due List Found!'], $this->failStatus);
        }
    }

    public function paymentPaidDueListBySupplier(Request $request){
        $payment_paid_due_amount = DB::table('product_purchases')
            ->leftJoin('users','product_purchases.user_id','users.id')
            ->leftJoin('parties','product_purchases.party_id','parties.id')
            ->leftJoin('warehouses','product_purchases.warehouse_id','warehouses.id')
            ->where('product_purchases.due_amount','>',0)
            ->where('product_purchases.party_id',$request->supplier_id)
            ->select('product_purchases.id','product_purchases.invoice_no','product_purchases.discount_type','product_purchases.discount_amount','product_purchases.total_amount','product_purchases.paid_amount','product_purchases.due_amount','product_purchases.purchase_date_time','users.name as user_name','parties.id as supplier_id','parties.name as supplier_name','warehouses.id as warehouse_id','warehouses.name as warehouse_name')
            ->paginate(12);


        if($payment_paid_due_amount)
        {
            $total_payment_paid_due_amount = 0;
            $sum_payment_paid_due_amount = DB::table('product_purchases')
                ->where('product_purchases.due_amount','>',0)
                ->where('product_purchases.party_id',$request->supplier_id)
                ->select(DB::raw('SUM(due_amount) as total_payment_paid_due_amount'))
                ->first();
            if($sum_payment_paid_due_amount){
                $total_payment_paid_due_amount = $sum_payment_paid_due_amount->total_payment_paid_due_amount;
            }

            $success['payment_paid_due_amount'] =  $payment_paid_due_amount;
            return response()->json(['success'=>true,'response' => $success,'total_payment_paid_due_amount'=>$total_payment_paid_due_amount], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Payment Due List Found!'], $this->failStatus);
        }
    }

    public function paymentPaidDueCreate(Request $request){
        //dd($request->all());
        $this->validate($request, [
            'supplier_id'=> 'required',
            'warehouse_id'=> 'required',
            'paid_amount'=> 'required',
            'new_paid_amount'=> 'required',
            'due_amount'=> 'required',
            //'total_amount'=> 'required',
            'payment_type'=> 'required',
            'invoice_no'=> 'required',
        ]);

        $date = date('Y-m-d');
        $date_time = date('Y-m-d h:i:s');

        $user_id = Auth::user()->id;

        // product purchase
        $productPurchase = ProductPurchase::where('invoice_no',$request->invoice_no)->first();
        $productPurchase->paid_amount = $request->paid_amount;
        $productPurchase->due_amount = $request->due_amount;
        //$productPurchase ->total_amount = $request->total_amount;
        $affectedRow = $productPurchase->save();

        if($affectedRow) {
            // transaction
            $transaction = new Transaction();
            $transaction->ref_id = $productPurchase->id;
            $transaction->invoice_no = $request->invoice_no;
            $transaction->user_id = $user_id;
            $transaction->warehouse_id = $request->warehouse_id;
            $transaction->party_id = $request->supplier_id;
            $transaction->transaction_type = 'payment_paid';
            $transaction->payment_type = $request->payment_type;
            $transaction->amount = $request->new_paid_amount;
            $transaction->transaction_date = $date;
            $transaction->transaction_date_time = $date_time;
            $transaction->save();
            $transaction_id = $transaction->id;

            // payment paid
            $previous_current_paid_amount = PaymentPaid::where('invoice_no',$request->invoice_no)->latest()->pluck('current_paid_amount')->first();
            $payment_paid = new PaymentPaid();
            $payment_paid->invoice_no = $request->invoice_no;
            $payment_paid->product_purchase_id = $productPurchase->id;
            $payment_paid->product_purchase_return_id = NULL;
            $payment_paid->user_id = $user_id;
            $payment_paid->party_id = $request->supplier_id;
            $payment_paid->paid_type = 'Purchase';
            $payment_paid->paid_amount = $request->new_paid_amount;
            $payment_paid->due_amount = $request->due_amount;
            $payment_paid->current_paid_amount = $previous_current_paid_amount + $request->new_paid_amount;
            $payment_paid->paid_date = $date;
            $payment_paid->paid_date_time = $date_time;
            $payment_paid->save();


            if($request->payment_type == 'SSL Commerz'){
                return response()->json(['success'=>true,'transaction_id' => $transaction_id,'payment_type' => $request->payment_type], $this->successStatus);
            }else{
                return response()->json(['success'=>true,'response' => 'Inserted Successfully.'], $this->successStatus);
            }
        }else{
            return response()->json(['success'=>false,'response'=>'No Inserted Successfully!'], $this->failStatus);
        }
    }

    public function paymentCollectionDueCreate(Request $request){
        //dd($request->all());
        $this->validate($request, [
            'customer_id'=> 'required',
            'store_id'=> 'required',
            'paid_amount'=> 'required',
            'new_paid_amount'=> 'required',
            'due_amount'=> 'required',
            //'total_amount'=> 'required',
            'payment_type'=> 'required',
            'invoice_no'=> 'required',
        ]);

        $date = date('Y-m-d');
        $date_time = date('Y-m-d h:i:s');

        $user_id = Auth::user()->id;
        $store_id = $request->store_id;
        $warehouse_id = Store::where('id',$store_id)->latest('id')->pluck('warehouse_id')->first();

        // product sale return
        $productSale = ProductSale::where('invoice_no',$request->invoice_no)->first();
        $productSale ->paid_amount = $request->paid_amount;
        $productSale ->due_amount = $request->due_amount;
        //$productSale ->total_amount = $request->total_amount;
        $affectedRow = $productSale->save();

        if($affectedRow)
        {

            // transaction
            $transaction = new Transaction();
            $transaction->ref_id = $productSale->id;
            $transaction->invoice_no = $request->invoice_no;
            $transaction->user_id = $user_id;
            $transaction->warehouse_id = $warehouse_id;
            $transaction->store_id = $store_id;
            $transaction->party_id = $request->customer_id;
            $transaction->transaction_type = 'payment_collection';
            $transaction->payment_type = $request->payment_type;
            $transaction->amount = $request->new_paid_amount;
            $transaction->transaction_date = $date;
            $transaction->transaction_date_time = $date_time;
            $transaction->save();
            $transaction_id = $transaction->id;

            // payment paid
            $previous_current_collection_amount = PaymentCollection::where('invoice_no',$request->invoice_no)->latest()->pluck('current_collection_amount')->first();
            $payment_collection = new PaymentCollection();
            $payment_collection->invoice_no = $request->invoice_no;
            $payment_collection->product_sale_id = $productSale->id;
            $payment_collection->product_sale_return_id = NULL;
            $payment_collection->user_id = $user_id;
            $payment_collection->party_id = $request->customer_id;
            $payment_collection->warehouse_id = $request->warehouse_id;
            $payment_collection->store_id = $request->store_id;
            $payment_collection->collection_type = 'Sale';
            $payment_collection->collection_amount = $request->new_paid_amount;
            $payment_collection->due_amount = $productSale->due_amount;
            $payment_collection->current_collection_amount = $previous_current_collection_amount + $request->new_paid_amount;
            $payment_collection->collection_date = $date;
            $payment_collection->collection_date_time = $date_time;
            $payment_collection->save();

            if($request->payment_type == 'SSL Commerz'){
                return response()->json(['success'=>true,'transaction_id' => $transaction_id,'payment_type' => $request->payment_type], $this->successStatus);
            }else{
                return response()->json(['success'=>true,'response' => 'Inserted Successfully.'], $this->successStatus);
            }
        }else{
            return response()->json(['success'=>false,'response'=>'No Inserted Successfully!'], $this->failStatus);
        }
    }

    public function paymentCollectionDueList(){
        $payment_collection_due_list = DB::table('product_sales')
            ->leftJoin('users','product_sales.user_id','users.id')
            ->leftJoin('parties','product_sales.party_id','parties.id')
            ->leftJoin('warehouses','product_sales.warehouse_id','warehouses.id')
            ->leftJoin('stores','product_sales.store_id','stores.id')
            ->where('product_sales.due_amount','>',0)
            ->select('product_sales.id','product_sales.invoice_no','product_sales.discount_type','product_sales.discount_amount','product_sales.total_amount','product_sales.paid_amount','product_sales.due_amount','product_sales.sale_date_time','users.name as user_name','parties.id as customer_id','parties.name as customer_name','warehouses.id as warehouse_id','warehouses.name as warehouse_name','stores.id as store_id','stores.name as store_name')
            ->orderBy('product_sales.id','desc')
            ->paginate(12);

        if($payment_collection_due_list)
        {

            $total_payment_collection_due_amount = 0;
            $sum_payment_collection_due_amount = DB::table('product_sales')
                ->where('product_sales.due_amount','>',0)
                ->select(DB::raw('SUM(due_amount) as total_payment_collection_due_amount'))
                ->first();
            if($sum_payment_collection_due_amount){
                $total_payment_collection_due_amount = $sum_payment_collection_due_amount->total_payment_collection_due_amount;
            }

            $success['payment_collection_due_list'] =  $payment_collection_due_list;

            return response()->json(['success'=>true,'response' => $success,'total_payment_collection_due_amount'=>$total_payment_collection_due_amount], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Payment Collection Due List Found!'], $this->failStatus);
        }
    }

    public function paymentCollectionDueListByCustomer(Request $request){
        $payment_collection_due_list = DB::table('product_sales')
            ->leftJoin('users','product_sales.user_id','users.id')
            ->leftJoin('parties','product_sales.party_id','parties.id')
            ->leftJoin('warehouses','product_sales.warehouse_id','warehouses.id')
            ->leftJoin('stores','product_sales.store_id','stores.id')
            ->where('product_sales.due_amount','>',0)
            ->where('product_sales.party_id',$request->customer_id)
            ->select('product_sales.id','product_sales.invoice_no','product_sales.discount_type','product_sales.discount_amount','product_sales.total_amount','product_sales.paid_amount','product_sales.due_amount','product_sales.sale_date_time','users.name as user_name','parties.id as customer_id','parties.name as customer_name','warehouses.id as warehouse_id','warehouses.name as warehouse_name','stores.id as store_id','stores.name as store_name')
            ->paginate(12);

        if($payment_collection_due_list)
        {

            $total_payment_collection_due_amount = 0;
            $sum_payment_collection_due_amount = DB::table('product_sales')
                ->where('product_sales.due_amount','>',0)
                ->where('product_sales.party_id',$request->customer_id)
                ->select(DB::raw('SUM(due_amount) as total_payment_collection_due_amount'))
                ->first();
            if($sum_payment_collection_due_amount){
                $total_payment_collection_due_amount = $sum_payment_collection_due_amount->total_payment_collection_due_amount;
            }

            $success['payment_collection_due_list'] =  $payment_collection_due_list;

            return response()->json(['success'=>true,'response' => $success,'total_payment_collection_due_amount'=>$total_payment_collection_due_amount], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Payment Collection Due List Found!'], $this->failStatus);
        }
    }

    public function storeDuePaidList(){
        $store_due_paid_amount = DB::table('stock_transfers')
            ->select('id','invoice_no','issue_date','total_vat_amount','total_amount','paid_amount','due_amount')
            ->paginate(12);

        if($store_due_paid_amount)
        {
            $success['store_due_paid_amount'] =  $store_due_paid_amount;
            return response()->json(['success'=>true,'response' => $success], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Store Due Paid List Found!'], $this->failStatus);
        }
    }

    public function storeDuePaidListByStoreDateDifference(Request $request){
        $store_due_paid_amount = DB::table('stock_transfers')
            ->where('store_id',$request->store_id)
            ->where('issue_date','>=',$request->start_date)
            ->where('issue_date','<=',$request->end_date)
            ->select('id','invoice_no','issue_date','total_vat_amount','total_amount','paid_amount','due_amount')
            ->paginate(12);

        if($store_due_paid_amount)
        {
            $success['store_due_paid_amount'] =  $store_due_paid_amount;
            return response()->json(['success'=>true,'response' => $success], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Store Due Paid List Found!'], $this->failStatus);
        }
    }

    public function warehouseStockList(){
        $warehouse_stock_list = DB::table('stocks')
            ->leftJoin('users','stocks.user_id','users.id')
            ->leftJoin('warehouses','stocks.warehouse_id','warehouses.id')
            ->leftJoin('product_units','stocks.product_unit_id','product_units.id')
            ->leftJoin('product_brands','stocks.product_brand_id','product_brands.id')
            ->leftJoin('products','stocks.product_id','products.id')
            ->where('stocks.stock_where','warehouse')
            ->select('stocks.id as stock_id','users.name as stock_by_user','warehouses.name as warehouse_name','product_units.name as product_unit_name','product_brands.name as product_brand_name','products.name as product_name','stocks.stock_type','stocks.stock_where','stocks.stock_in_out','stocks.previous_stock','stocks.stock_in','stocks.stock_out','stocks.current_stock','stocks.stock_date','stocks.stock_date_time')
            ->latest('stocks.id','desc')
            ->get();

        if($warehouse_stock_list)
        {
            $success['warehouse_stock_list'] =  $warehouse_stock_list;
            return response()->json(['success'=>true,'response' => $success], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Warehouse Stock List Found!'], $this->failStatus);
        }
    }

    public function warehouseStockLowList(){

        $warehouse_stock_low_list = DB::table('stocks')
            ->leftJoin('users','stocks.user_id','users.id')
            ->leftJoin('warehouses','stocks.warehouse_id','warehouses.id')
            ->leftJoin('product_units','stocks.product_unit_id','product_units.id')
            ->leftJoin('product_brands','stocks.product_brand_id','product_brands.id')
            ->leftJoin('products','stocks.product_id','products.id')
            ->where('stocks.stock_where','warehouse')
            //->where('stocks.current_stock','<',2)
            ->whereIn('stocks.id', function($query) {
                $query->from('stocks')->where('current_stock','<', 2)->groupBy('product_id')->selectRaw('MAX(id)');
            })
            ->select('stocks.id as stock_id','users.name as stock_by_user','warehouses.name as warehouse_name','product_units.name as product_unit_name','product_brands.name as product_brand_name','products.id as product_id','products.name as product_name','stocks.stock_type','stocks.stock_where','stocks.previous_stock','stocks.stock_in','stocks.stock_out','stocks.current_stock','stocks.stock_date','stocks.stock_date_time')
            ->latest('stocks.id','desc')
            ->get();

        if($warehouse_stock_low_list)
        {
            $success['warehouse_stock_low_list'] =  $warehouse_stock_low_list;
            return response()->json(['success'=>true,'response' => $success], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Warehouse Stock Low List Found!'], $this->failStatus);
        }
    }

//    public function warehouseCurrentStockList(Request $request){
//        $warehouse_stock_product_list = Stock::where('warehouse_id',$request->warehouse_id)
//            ->select('product_id')
//            ->groupBy('product_id')
//            ->latest('id')
//            ->get();
//
//        $warehouse_stock_product = [];
//        foreach($warehouse_stock_product_list as $data){
//
//            $stock_row = DB::table('stocks')
//                ->join('warehouses','stocks.warehouse_id','warehouses.id')
//                ->leftJoin('products','stocks.product_id','products.id')
//                ->leftJoin('product_units','stocks.product_unit_id','product_units.id')
//                ->leftJoin('product_brands','stocks.product_brand_id','product_brands.id')
//                ->where('stocks.stock_where','warehouse')
//                ->where('stocks.product_id',$data->product_id)
//                ->where('stocks.warehouse_id',$request->warehouse_id)
//                ->select('stocks.*','warehouses.name as warehouse_name','products.name as product_name','products.purchase_price','products.selling_price','products.item_code','products.barcode','products.image','products.vat_status','products.vat_percentage','products.vat_amount','product_units.name as product_unit_name','product_brands.name as product_brand_name')
//                ->orderBy('stocks.id','desc')
//                ->first();
//
//            if($stock_row){
//                $nested_data['stock_id'] = $stock_row->id;
//                $nested_data['warehouse_id'] = $stock_row->warehouse_id;
//                $nested_data['warehouse_name'] = $stock_row->warehouse_name;
//                $nested_data['product_id'] = $stock_row->product_id;
//                $nested_data['product_name'] = $stock_row->product_name;
//                $nested_data['purchase_price'] = $stock_row->purchase_price;
//                $nested_data['selling_price'] = $stock_row->selling_price;
//                $nested_data['item_code'] = $stock_row->item_code;
//                $nested_data['barcode'] = $stock_row->barcode;
//                $nested_data['image'] = $stock_row->image;
//                $nested_data['product_unit_id'] = $stock_row->product_unit_id;
//                $nested_data['product_unit_name'] = $stock_row->product_unit_name;
//                $nested_data['product_brand_id'] = $stock_row->product_brand_id;
//                $nested_data['product_brand_name'] = $stock_row->product_brand_name;
//                $nested_data['current_stock'] = $stock_row->current_stock;
//
//                array_push($warehouse_stock_product,$nested_data);
//            }
//        }
//
//        if($warehouse_stock_product)
//        {
//            $success['warehouse_current_stock_list'] =  $warehouse_stock_product;
//            return response()->json(['success'=>true,'response' => $success], $this->successStatus);
//        }else{
//            return response()->json(['success'=>false,'response'=>'No Warehouse Current Stock List Found!'], $this->failStatus);
//        }
//    }

    public function warehouseCurrentStockList(Request $request){

        $warehouse_stock_product_list = DB::table('warehouse_current_stocks')
            ->join('warehouses','warehouse_current_stocks.warehouse_id','warehouses.id')
            ->leftJoin('products','warehouse_current_stocks.product_id','products.id')
            ->leftJoin('product_units','products.product_unit_id','product_units.id')
            ->leftJoin('product_brands','products.product_brand_id','product_brands.id')
            ->where('warehouse_current_stocks.warehouse_id',$request->warehouse_id)
            ->select('warehouse_current_stocks.*','warehouses.name as warehouse_name','products.name as product_name','products.purchase_price','products.selling_price','products.item_code','products.barcode','products.image','products.vat_status','products.vat_percentage','products.vat_amount','product_units.id as product_unit_id','product_units.name as product_unit_name','product_brands.id as product_brand_id','product_brands.name as product_brand_name')
            ->get();

        $warehouse_stock_product = [];
        foreach($warehouse_stock_product_list as $stock_row){

                $nested_data['stock_id'] = $stock_row->id;
                $nested_data['warehouse_id'] = $stock_row->warehouse_id;
                $nested_data['warehouse_name'] = $stock_row->warehouse_name;
                $nested_data['product_id'] = $stock_row->product_id;
                $nested_data['product_name'] = $stock_row->product_name;
                $nested_data['purchase_price'] = $stock_row->purchase_price;
                $nested_data['selling_price'] = $stock_row->selling_price;
                $nested_data['item_code'] = $stock_row->item_code;
                $nested_data['barcode'] = $stock_row->barcode;
                $nested_data['image'] = $stock_row->image;
                $nested_data['product_unit_id'] = $stock_row->product_unit_id;
                $nested_data['product_unit_name'] = $stock_row->product_unit_name;
                $nested_data['product_brand_id'] = $stock_row->product_brand_id;
                $nested_data['product_brand_name'] = $stock_row->product_brand_name;
                $nested_data['current_stock'] = $stock_row->current_stock;

                array_push($warehouse_stock_product,$nested_data);

        }

        if($warehouse_stock_product)
        {
            $success['warehouse_current_stock_list'] =  $warehouse_stock_product;
            return response()->json(['success'=>true,'response' => $success], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Warehouse Current Stock List Found!'], $this->failStatus);
        }
    }

//    public function warehouseCurrentStockListWithoutZero(Request $request){
//        $warehouse_stock_product_list = Stock::where('warehouse_id',$request->warehouse_id)
//            //->where('current_stock','>',0)
//            ->select('product_id')
//            ->groupBy('product_id')
//            ->latest('id')
//            ->get();
//
//        $warehouse_stock_product = [];
//        foreach($warehouse_stock_product_list as $data){
//
//            $stock_row = DB::table('stocks')
//                ->join('warehouses','stocks.warehouse_id','warehouses.id')
//                ->leftJoin('products','stocks.product_id','products.id')
//                ->leftJoin('product_units','stocks.product_unit_id','product_units.id')
//                ->leftJoin('product_brands','stocks.product_brand_id','product_brands.id')
//                ->where('stocks.stock_where','warehouse')
//                ->where('stocks.product_id',$data->product_id)
//                ->where('stocks.warehouse_id',$request->warehouse_id)
//                ->where('stocks.current_stock','>',0)
//                ->select('stocks.*','warehouses.name as warehouse_name','products.name as product_name','products.purchase_price','products.selling_price','products.item_code','products.barcode','products.image','products.vat_status','products.vat_percentage','products.vat_amount','product_units.name as product_unit_name','product_brands.name as product_brand_name')
//                ->orderBy('stocks.id','desc')
//                ->first();
//
//            if($stock_row){
//                $nested_data['stock_id'] = $stock_row->id;
//                $nested_data['warehouse_id'] = $stock_row->warehouse_id;
//                $nested_data['warehouse_name'] = $stock_row->warehouse_name;
//                $nested_data['product_id'] = $stock_row->product_id;
//                $nested_data['product_name'] = $stock_row->product_name;
//                $nested_data['purchase_price'] = $stock_row->purchase_price;
//                $nested_data['selling_price'] = $stock_row->selling_price;
//                $nested_data['item_code'] = $stock_row->item_code;
//                $nested_data['barcode'] = $stock_row->barcode;
//                $nested_data['image'] = $stock_row->image;
//                $nested_data['product_unit_id'] = $stock_row->product_unit_id;
//                $nested_data['product_unit_name'] = $stock_row->product_unit_name;
//                $nested_data['product_brand_id'] = $stock_row->product_brand_id;
//                $nested_data['product_brand_name'] = $stock_row->product_brand_name;
//                $nested_data['current_stock'] = $stock_row->current_stock;
//
//                array_push($warehouse_stock_product,$nested_data);
//            }
//        }
//
//        if($warehouse_stock_product)
//        {
//            $success['warehouse_current_stock_list'] =  $warehouse_stock_product;
//            return response()->json(['success'=>true,'response' => $success], $this->successStatus);
//        }else{
//            return response()->json(['success'=>false,'response'=>'No Warehouse Current Stock List Found!'], $this->failStatus);
//        }
//    }

    public function warehouseCurrentStockListWithoutZero(Request $request){
        $warehouse_stock_product_list = DB::table('warehouse_current_stocks')
            ->join('warehouses','warehouse_current_stocks.warehouse_id','warehouses.id')
            ->leftJoin('products','warehouse_current_stocks.product_id','products.id')
            ->leftJoin('product_units','products.product_unit_id','product_units.id')
            ->leftJoin('product_brands','products.product_brand_id','product_brands.id')
            ->where('warehouse_current_stocks.warehouse_id',$request->warehouse_id)
            ->where('warehouse_current_stocks.current_stock','!=',0)
            ->select('warehouse_current_stocks.*','warehouses.name as warehouse_name','products.name as product_name','products.purchase_price','products.selling_price','products.item_code','products.barcode','products.image','products.vat_status','products.vat_percentage','products.vat_amount','product_units.id as product_unit_id','product_units.name as product_unit_name','product_brands.id as product_brand_id','product_brands.name as product_brand_name')
            ->get();

        $warehouse_stock_product = [];
        foreach($warehouse_stock_product_list as $stock_row){

            $nested_data['stock_id'] = $stock_row->id;
            $nested_data['warehouse_id'] = $stock_row->warehouse_id;
            $nested_data['warehouse_name'] = $stock_row->warehouse_name;
            $nested_data['product_id'] = $stock_row->product_id;
            $nested_data['product_name'] = $stock_row->product_name;
            $nested_data['purchase_price'] = $stock_row->purchase_price;
            $nested_data['selling_price'] = $stock_row->selling_price;
            $nested_data['item_code'] = $stock_row->item_code;
            $nested_data['barcode'] = $stock_row->barcode;
            $nested_data['image'] = $stock_row->image;
            $nested_data['product_unit_id'] = $stock_row->product_unit_id;
            $nested_data['product_unit_name'] = $stock_row->product_unit_name;
            $nested_data['product_brand_id'] = $stock_row->product_brand_id;
            $nested_data['product_brand_name'] = $stock_row->product_brand_name;
            $nested_data['current_stock'] = $stock_row->current_stock;

            array_push($warehouse_stock_product,$nested_data);

        }

        if($warehouse_stock_product)
        {
            $success['warehouse_current_stock_list'] =  $warehouse_stock_product;
            return response()->json(['success'=>true,'response' => $success], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Warehouse Current Stock List Found!'], $this->failStatus);
        }
    }

//    public function warehouseCurrentStockListPagination(Request $request){
//
//        $warehouse_stock_product = DB::table('stocks')
//                ->join('warehouses','stocks.warehouse_id','warehouses.id')
//                ->leftJoin('products','stocks.product_id','products.id')
//                ->leftJoin('product_units','stocks.product_unit_id','product_units.id')
//                ->leftJoin('product_brands','stocks.product_brand_id','product_brands.id')
//                //->where('stocks.stock_where','warehouse')
//                ->whereIn('stocks.id', function($query) {
//                    $query->from('stocks')->groupBy('product_id')->selectRaw('MAX(id)');
//                })
//                ->where('stocks.warehouse_id',$request->warehouse_id)
//                ->select('stocks.*','warehouses.name as warehouse_name','products.name as product_name','products.purchase_price','products.selling_price','products.item_code','products.barcode','products.image','products.vat_status','products.vat_percentage','products.vat_amount','product_units.name as product_unit_name','product_brands.name as product_brand_name')
//                ->latest('id','desc')
//                ->paginate(12);
//
//
//        if($warehouse_stock_product)
//        {
//            $success['warehouse_current_stock_list'] =  $warehouse_stock_product;
//            return response()->json(['success'=>true,'response' => $success], $this->successStatus);
//        }else{
//            return response()->json(['success'=>false,'response'=>'No Warehouse Current Stock List Found!'], $this->failStatus);
//        }
//    }

    public function warehouseCurrentStockListPagination(Request $request){

        $warehouse_stock_product = DB::table('warehouse_current_stocks')
            ->join('warehouses','warehouse_current_stocks.warehouse_id','warehouses.id')
            ->leftJoin('products','warehouse_current_stocks.product_id','products.id')
            ->leftJoin('product_units','products.product_unit_id','product_units.id')
            ->leftJoin('product_brands','products.product_brand_id','product_brands.id')
            ->where('warehouse_current_stocks.warehouse_id',$request->warehouse_id)
            ->select('warehouse_current_stocks.*','warehouses.name as warehouse_name','products.name as product_name','products.purchase_price','products.selling_price','products.item_code','products.barcode','products.image','products.vat_status','products.vat_percentage','products.vat_amount','product_units.id as product_unit_id','product_units.name as product_unit_name','product_brands.id as product_brand_id','product_brands.name as product_brand_name')
            ->paginate(12);


        if($warehouse_stock_product)
        {
            $success['warehouse_current_stock_list'] =  $warehouse_stock_product;
            return response()->json(['success'=>true,'response' => $success], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Warehouse Current Stock List Found!'], $this->failStatus);
        }
    }

//    public function warehouseCurrentStockListPaginationBarcode(Request $request){
//
//        $warehouse_stock_product = DB::table('stocks')
//            ->join('warehouses','stocks.warehouse_id','warehouses.id')
//            ->leftJoin('products','stocks.product_id','products.id')
//            ->leftJoin('product_units','stocks.product_unit_id','product_units.id')
//            ->leftJoin('product_brands','stocks.product_brand_id','product_brands.id')
//            ->where('stocks.stock_where','warehouse')
//            ->whereIn('stocks.id', function($query) {
//                $query->from('stocks')->groupBy('product_id')->selectRaw('MAX(id)');
//            })
//            ->where('products.barcode',$request->barcode)
//            ->where('stocks.warehouse_id',$request->warehouse_id)
//            ->select('stocks.*','warehouses.name as warehouse_name','products.name as product_name','products.purchase_price','products.selling_price','products.item_code','products.barcode','products.image','products.vat_status','products.vat_percentage','products.vat_amount','product_units.name as product_unit_name','product_brands.name as product_brand_name')
//            ->latest('id','desc')
//            ->paginate(1);
//
//        if($warehouse_stock_product)
//        {
//            $success['warehouse_current_stock_list'] =  $warehouse_stock_product;
//            return response()->json(['success'=>true,'response' => $success], $this->successStatus);
//        }else{
//            return response()->json(['success'=>false,'response'=>'No Warehouse Current Stock List Found!'], $this->failStatus);
//        }
//    }

    public function warehouseCurrentStockListPaginationBarcode(Request $request){

        $warehouse_stock_product = DB::table('warehouse_current_stocks')
            ->join('warehouses','warehouse_current_stocks.warehouse_id','warehouses.id')
            ->leftJoin('products','warehouse_current_stocks.product_id','products.id')
            ->leftJoin('product_units','products.product_unit_id','product_units.id')
            ->leftJoin('product_brands','products.product_brand_id','product_brands.id')
            ->where('warehouse_current_stocks.warehouse_id',$request->warehouse_id)
            ->where('products.barcode',$request->barcode)
            ->select('warehouse_current_stocks.*','warehouses.name as warehouse_name','products.name as product_name','products.purchase_price','products.selling_price','products.item_code','products.barcode','products.image','products.vat_status','products.vat_percentage','products.vat_amount','product_units.id as product_unit_id','product_units.name as product_unit_name','product_brands.id as product_brand_id','product_brands.name as product_brand_name')
            ->paginate(1);

        if($warehouse_stock_product)
        {
            $success['warehouse_current_stock_list'] =  $warehouse_stock_product;
            return response()->json(['success'=>true,'response' => $success], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Warehouse Current Stock List Found!'], $this->failStatus);
        }
    }

//    public function warehouseCurrentStockListPaginationItemcode(Request $request){
//
//        $warehouse_stock_product = DB::table('stocks')
//            ->join('warehouses','stocks.warehouse_id','warehouses.id')
//            ->leftJoin('products','stocks.product_id','products.id')
//            ->leftJoin('product_units','stocks.product_unit_id','product_units.id')
//            ->leftJoin('product_brands','stocks.product_brand_id','product_brands.id')
//            ->where('stocks.stock_where','warehouse')
//            ->whereIn('stocks.id', function($query) {
//                $query->from('stocks')->groupBy('product_id')->selectRaw('MAX(id)');
//            })
//            ->where('products.item_code',$request->item_code)
//            ->where('stocks.warehouse_id',$request->warehouse_id)
//            ->select('stocks.*','warehouses.name as warehouse_name','products.name as product_name','products.purchase_price','products.selling_price','products.item_code','products.barcode','products.image','products.vat_status','products.vat_percentage','products.vat_amount','product_units.name as product_unit_name','product_brands.name as product_brand_name')
//            ->latest('id','desc')
//            ->paginate(1);
//
//        if($warehouse_stock_product)
//        {
//            $success['warehouse_current_stock_list'] =  $warehouse_stock_product;
//            return response()->json(['success'=>true,'response' => $success], $this->successStatus);
//        }else{
//            return response()->json(['success'=>false,'response'=>'No Warehouse Current Stock List Found!'], $this->failStatus);
//        }
//    }

    public function warehouseCurrentStockListPaginationItemcode(Request $request){

        $warehouse_stock_product = DB::table('warehouse_current_stocks')
            ->join('warehouses','warehouse_current_stocks.warehouse_id','warehouses.id')
            ->leftJoin('products','warehouse_current_stocks.product_id','products.id')
            ->leftJoin('product_units','products.product_unit_id','product_units.id')
            ->leftJoin('product_brands','products.product_brand_id','product_brands.id')
            ->where('warehouse_current_stocks.warehouse_id',$request->warehouse_id)
            ->where('products.barcode',$request->item_code)
            ->select('warehouse_current_stocks.*','warehouses.name as warehouse_name','products.name as product_name','products.purchase_price','products.selling_price','products.item_code','products.barcode','products.image','products.vat_status','products.vat_percentage','products.vat_amount','product_units.id as product_unit_id','product_units.name as product_unit_name','product_brands.id as product_brand_id','product_brands.name as product_brand_name')
            ->paginate(1);

        if($warehouse_stock_product)
        {
            $success['warehouse_current_stock_list'] =  $warehouse_stock_product;
            return response()->json(['success'=>true,'response' => $success], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Warehouse Current Stock List Found!'], $this->failStatus);
        }
    }

//    public function warehouseCurrentStockListPaginationProductName(Request $request){
//
//        $warehouse_stock_product = DB::table('stocks')
//            ->join('warehouses','stocks.warehouse_id','warehouses.id')
//            ->leftJoin('products','stocks.product_id','products.id')
//            ->leftJoin('product_units','stocks.product_unit_id','product_units.id')
//            ->leftJoin('product_brands','stocks.product_brand_id','product_brands.id')
//            ->where('stocks.stock_where','warehouse')
//            ->whereIn('stocks.id', function($query) {
//                $query->from('stocks')->groupBy('product_id')->selectRaw('MAX(id)');
//            })
//            ->where('products.name','like','%'.$request->name.'%')
//            ->where('stocks.warehouse_id',$request->warehouse_id)
//            ->select('stocks.*','warehouses.name as warehouse_name','products.name as product_name','products.purchase_price','products.selling_price','products.item_code','products.barcode','products.image','products.vat_status','products.vat_percentage','products.vat_amount','product_units.name as product_unit_name','product_brands.name as product_brand_name')
//            ->latest('id','desc')
//            ->paginate(12);
//
//        if($warehouse_stock_product)
//        {
//            $success['warehouse_current_stock_list'] =  $warehouse_stock_product;
//            return response()->json(['success'=>true,'response' => $success], $this->successStatus);
//        }else{
//            return response()->json(['success'=>false,'response'=>'No Warehouse Current Stock List Found!'], $this->failStatus);
//        }
//    }

    public function warehouseCurrentStockListPaginationProductName(Request $request){

        $warehouse_stock_product = DB::table('warehouse_current_stocks')
            ->join('warehouses','warehouse_current_stocks.warehouse_id','warehouses.id')
            ->leftJoin('products','warehouse_current_stocks.product_id','products.id')
            ->leftJoin('product_units','products.product_unit_id','product_units.id')
            ->leftJoin('product_brands','products.product_brand_id','product_brands.id')
            ->where('warehouse_current_stocks.warehouse_id',$request->warehouse_id)
            ->where('products.name','like','%'.$request->name.'%')
            ->select('warehouse_current_stocks.*','warehouses.name as warehouse_name','products.name as product_name','products.purchase_price','products.selling_price','products.item_code','products.barcode','products.image','products.vat_status','products.vat_percentage','products.vat_amount','product_units.id as product_unit_id','product_units.name as product_unit_name','product_brands.id as product_brand_id','product_brands.name as product_brand_name')
            ->paginate(1);

        if($warehouse_stock_product)
        {
            $success['warehouse_current_stock_list'] =  $warehouse_stock_product;
            return response()->json(['success'=>true,'response' => $success], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Warehouse Current Stock List Found!'], $this->failStatus);
        }
    }

    public function storeCurrentStockListPagination(Request $request){

        $store_stock_product = DB::table('warehouse_store_current_stocks')
            ->join('warehouses','warehouse_store_current_stocks.warehouse_id','warehouses.id')
            ->leftJoin('products','warehouse_store_current_stocks.product_id','products.id')
            ->leftJoin('product_units','products.product_unit_id','product_units.id')
            ->leftJoin('product_brands','products.product_brand_id','product_brands.id')
            ->where('warehouse_store_current_stocks.store_id',$request->store_id)
            ->select('warehouse_store_current_stocks.*','warehouses.name as warehouse_name','products.name as product_name','products.purchase_price','products.whole_sale_price','products.selling_price','products.item_code','products.barcode','products.image','products.vat_status','products.vat_percentage','products.vat_amount','products.vat_whole_amount','product_units.id as product_unit_id','product_units.name as product_unit_name','product_brands.id as product_brand_id','product_brands.name as product_brand_name')
            ->paginate(12);

        if($store_stock_product)
        {
            $success['store_current_stock_list'] =  $store_stock_product;
            return response()->json(['success'=>true,'response' => $success], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Store Current Stock List Found!'], $this->failStatus);
        }
    }



    public function storeCurrentStockListPaginationBarcode(Request $request){

        $store_stock_product = DB::table('warehouse_store_current_stocks')
            ->join('warehouses','warehouse_store_current_stocks.warehouse_id','warehouses.id')
            ->leftJoin('products','warehouse_store_current_stocks.product_id','products.id')
            ->leftJoin('product_units','products.product_unit_id','product_units.id')
            ->leftJoin('product_brands','products.product_brand_id','product_brands.id')
            ->where('warehouse_store_current_stocks.store_id',$request->store_id)
            ->where('products.barcode',$request->barcode)
            ->select('warehouse_store_current_stocks.*','warehouses.name as warehouse_name','products.name as product_name','products.purchase_price','products.whole_sale_price','products.selling_price','products.item_code','products.barcode','products.image','products.vat_status','products.vat_percentage','products.vat_amount','products.vat_whole_amount','product_units.id as product_unit_id','product_units.name as product_unit_name','product_brands.id as product_brand_id','product_brands.name as product_brand_name')
            ->paginate(1);

        if($store_stock_product)
        {
            $success['store_current_stock_list'] =  $store_stock_product;
            return response()->json(['success'=>true,'response' => $success], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Store Current Stock List Found!'], $this->failStatus);
        }
    }

    public function storeCurrentStockListPaginationItemcode(Request $request){

        $store_stock_product = DB::table('warehouse_store_current_stocks')
            ->join('warehouses','warehouse_store_current_stocks.warehouse_id','warehouses.id')
            ->leftJoin('products','warehouse_store_current_stocks.product_id','products.id')
            ->leftJoin('product_units','products.product_unit_id','product_units.id')
            ->leftJoin('product_brands','products.product_brand_id','product_brands.id')
            ->where('warehouse_store_current_stocks.store_id',$request->store_id)
            ->where('products.item_code',$request->item_code)
            ->select('warehouse_store_current_stocks.*','warehouses.name as warehouse_name','products.name as product_name','products.purchase_price','products.whole_sale_price','products.selling_price','products.item_code','products.barcode','products.image','products.vat_status','products.vat_percentage','products.vat_amount','products.vat_whole_amount','product_units.id as product_unit_id','product_units.name as product_unit_name','product_brands.id as product_brand_id','product_brands.name as product_brand_name')
            ->paginate(1);

        if($store_stock_product)
        {
            $success['store_current_stock_list'] =  $store_stock_product;
            return response()->json(['success'=>true,'response' => $success], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Store Current Stock List Found!'], $this->failStatus);
        }
    }



    public function storeCurrentStockListPaginationProductName(Request $request){

        $store_stock_product = DB::table('warehouse_store_current_stocks')
            ->join('warehouses','warehouse_store_current_stocks.warehouse_id','warehouses.id')
            ->leftJoin('products','warehouse_store_current_stocks.product_id','products.id')
            ->leftJoin('product_units','products.product_unit_id','product_units.id')
            ->leftJoin('product_brands','products.product_brand_id','product_brands.id')
            ->where('warehouse_store_current_stocks.store_id',$request->store_id)
            ->where('products.name','like','%'.$request->name.'%')
            ->select('warehouse_store_current_stocks.*','warehouses.name as warehouse_name','products.name as product_name','products.purchase_price','products.whole_sale_price','products.selling_price','products.item_code','products.barcode','products.image','products.vat_status','products.vat_percentage','products.vat_amount','products.vat_whole_amount','product_units.id as product_unit_id','product_units.name as product_unit_name','product_brands.id as product_brand_id','product_brands.name as product_brand_name')
            ->paginate(12);

        if($store_stock_product)
        {
            $success['store_current_stock_list'] =  $store_stock_product;
            return response()->json(['success'=>true,'response' => $success], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Store Current Stock List Found!'], $this->failStatus);
        }
    }

//    public function checkWarehouseProductCurrentStock(Request $request){
//        $check_warehouse_product_current_stock = Stock::where('warehouse_id',$request->warehouse_id)
//            ->where('product_id',$request->product_id)
//            ->where('stock_where','warehouse')
//            ->latest('id','desc')
//            ->pluck('current_stock')
//            ->first();
//
//        if($check_warehouse_product_current_stock)
//        {
//            $success['check_warehouse_product_current_stock'] =  $check_warehouse_product_current_stock;
//            return response()->json(['success'=>true,'response' => $success], $this->successStatus);
//        }else{
//            return response()->json(['success'=>false,'response'=>'No Warehouse Product Current Stock Found!'], $this->failStatus);
//        }
//    }

    public function storeToWarehouseStockRequestCreate(Request $request){
        $this->validate($request, [
            'request_from_store_id'=> 'required',
            'request_to_warehouse_id'=> 'required',
        ]);

        $date = date('Y-m-d');
        $date_time = date('Y-m-d h:i:s');

        $user_id = Auth::user()->id;
        $request_to_warehouse_id = $request->request_to_warehouse_id;
        $request_from_store_id = $request->request_from_store_id;


        $get_invoice_no = StockTransferRequest::latest()->pluck('invoice_no')->first();
        if(!empty($get_invoice_no)){
            $get_invoice = str_replace("STRN-","",$get_invoice_no);
            $invoice_no = $get_invoice+1;
        }else{
            $invoice_no = 1;
        }

        $final_invoice = 'STRN-'.$invoice_no;
        $stock_transfer_request = new StockTransferRequest();
        $stock_transfer_request->invoice_no=$final_invoice;
        $stock_transfer_request->request_to_warehouse_id = $request_to_warehouse_id;
        $stock_transfer_request->request_from_store_id = $request_from_store_id;
        $stock_transfer_request->request_by_user_id=$user_id;
        $stock_transfer_request->request_date=$date;
        $stock_transfer_request->request_remarks=$request->request_remarks;
        $stock_transfer_request->request_status='Pending';
        //$stock_transfer_request->received_by_user_id=NULL;
        //$stock_transfer_request->received_status='Pending';
        $stock_transfer_request->save();
        $stock_transfer_request_insert_id = $stock_transfer_request->id;


        foreach ($request->products as $data) {

            $product_id = $data['product_id'];
            $product_info = Product::where('id',$product_id)->first();

            $stock_transfer_request_detail = new StockTransferRequestDetail();
            $stock_transfer_request_detail->stock_transfer_request_id = $stock_transfer_request_insert_id;
            $stock_transfer_request_detail->product_unit_id = $data['product_unit_id'];
            $stock_transfer_request_detail->product_brand_id = $data['product_brand_id'] ? $data['product_brand_id'] : NULL;
            $stock_transfer_request_detail->product_id = $product_id;
            $stock_transfer_request_detail->barcode = $product_info->barcode;
            $stock_transfer_request_detail->request_qty = $data['qty'];
            $stock_transfer_request_detail->send_qty = '';
            $stock_transfer_request_detail->received_qty = '';
            $stock_transfer_request_detail->price = $product_info->purchase_price;
            $stock_transfer_request_detail->vat_amount = $data['qty']*$product_info->whole_sale_price;
            $stock_transfer_request_detail->sub_total = ($data['qty']*$product_info->whole_sale_price) + ($data['qty']*$product_info->purchase_price);
            $stock_transfer_request_detail->received_date = $date;
            $stock_transfer_request_detail->save();
        }

        if($stock_transfer_request_insert_id){
            return response()->json(['success'=>true,'response' => 'Stock Transfer Request Successfully Inserted.'], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Stock Transfer Request Successfully Inserted.!'], $this->failStatus);
        }
    }

    public function storeToWarehouseStockRequestList(){
        $stock_transfer_request_lists = DB::table('stock_transfer_requests')
            ->leftJoin('users','stock_transfer_requests.request_by_user_id','users.id')
            ->leftJoin('warehouses','stock_transfer_requests.request_to_warehouse_id','warehouses.id')
            ->leftJoin('stores','stock_transfer_requests.request_from_store_id','stores.id')
            //->where('stock_transfers.sale_type','whole_sale')
            ->select('stock_transfer_requests.id','stock_transfer_requests.invoice_no','stock_transfer_requests.request_date','stock_transfer_requests.request_remarks','users.name as user_name','warehouses.id as warehouse_id','warehouses.name as warehouse_name','stores.id as store_id','stores.name as store_name','stores.phone as store_phone','stores.email as store_email','stores.address as store_address')
            ->get();

        if($stock_transfer_request_lists)
        {
            $success['stock_transfer_request_list'] =  $stock_transfer_request_lists;
            return response()->json(['success'=>true,'response' => $success], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Stock Transfer Request List Found!'], $this->failStatus);
        }
    }

    public function storeToWarehouseStockRequestDetails(Request $request){
        $stock_transfer_request_details = DB::table('stock_transfer_requests')
            ->join('stock_transfer_request_details','stock_transfer_requests.id','stock_transfer_request_details.stock_transfer_request_id')
            ->leftJoin('products','stock_transfer_request_details.product_id','products.id')
            ->leftJoin('product_units','stock_transfer_request_details.product_unit_id','product_units.id')
            ->leftJoin('product_brands','stock_transfer_request_details.product_brand_id','product_brands.id')
            ->where('stock_transfer_requests.id',$request->stock_transfer_request_id)
            ->select('products.id as product_id','products.name as product_name','product_units.id as product_unit_id','product_units.name as product_unit_name','product_brands.id as product_brand_id','product_brands.name as product_brand_name','stock_transfer_request_details.request_qty as qty','stock_transfer_request_details.id as stock_transfer_request_detail_id','stock_transfer_request_details.price','stock_transfer_request_details.sub_total','stock_transfer_request_details.vat_amount')
            ->get();

        if($stock_transfer_request_details)
        {
            $success['stock_transfer_request_details'] =  $stock_transfer_request_details;
            return response()->json(['success'=>true,'response' => $success], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Stock Transfer Request Details Found!'], $this->failStatus);
        }
    }

//    public function storeToWarehouseStockRequestStockTransferCreate(Request $request){
//        $this->validate($request, [
//            'stock_transfer_request_id'=> 'required',
//            'store_id'=> 'required',
//            'warehouse_id'=> 'required',
//        ]);
//
//        $date = date('Y-m-d');
//        $date_time = date('Y-m-d h:i:s');
//
//        $user_id = Auth::user()->id;
//        $warehouse_id = $request->warehouse_id;
//        $store_id = $request->store_id;
//
//
//        $get_invoice_no = StockTransfer::latest()->pluck('invoice_no')->first();
//        if(!empty($get_invoice_no)){
//            $get_invoice = str_replace("Stock-transfer-","",$get_invoice_no);
//            $invoice_no = $get_invoice+1;
//        }else{
//            $invoice_no = 1;
//        }
//
//        $total_amount = 0;
//        $total_vat_amount = 0;
//        foreach ($request->products as $data) {
//            $product_id = $data['product_id'];
//            //$price = Product::where('id',$product_id)->pluck('purchase_price')->first();
//            $Product_info = Product::where('id',$product_id)->first();
//            $total_vat_amount += ($data['qty']*$Product_info->whole_sale_price);
//            //$total_amount += $Product_info->purchase_price;
//            $total_amount += ($data['qty']*$Product_info->whole_sale_price) + ($data['qty']*$Product_info->purchase_price);
//        }
//
//        // stock transfer request table update
//        $stock_transfer_request = StockTransferRequest::find($request->stock_transfer_request_id);
//        $stock_transfer_request->send_by_user_id=Auth::user()->id;
//        $stock_transfer_request->send_date=$date;
//        $stock_transfer_request->send_remarks=$request->send_remarks;
//        $stock_transfer_request->send_status='Delivered';
//        $stock_transfer_request->save();
//
//
//        // stock transfer
//        $final_invoice = 'Stock-transfer-'.$invoice_no;
//        $stock_transfer = new StockTransfer();
//        $stock_transfer->invoice_no=$final_invoice;
//        $stock_transfer->user_id=Auth::user()->id;
//        $stock_transfer->warehouse_id = $warehouse_id;
//        $stock_transfer->store_id = $store_id;
//        $stock_transfer->total_vat_amount = $total_vat_amount;
//        //$stock_transfer->total_amount = $total_amount;
//        $stock_transfer->paid_amount = 0;
//        $stock_transfer->due_amount = $total_amount;
//        $stock_transfer->issue_date = $date;
//        $stock_transfer->due_date = $date;
//        $stock_transfer->save();
//        $stock_transfer_insert_id = $stock_transfer->id;
//
//        $insert_id = false;
//
//        foreach ($request->products as $data) {
//
//            $product_id = $data['product_id'];
//            $product_info = Product::where('id',$product_id)->first();
//
//
//            // stock transfer request details
//            $stock_transfer_request_detail = StockTransferRequestDetail::find($request->stock_transfer_request_detail_id);
//            $stock_transfer_request_detail->send_qty = $data['send_qty'];
//            $stock_transfer_request_detail->save();
//
//
//            // stock transfer details
//            $stock_transfer_detail = new StockTransferDetail();
//            $stock_transfer_detail->stock_transfer_id = $stock_transfer_insert_id;
//            $stock_transfer_detail->product_unit_id = $data['product_unit_id'];
//            $stock_transfer_detail->product_brand_id = $data['product_brand_id'] ? $data['product_brand_id'] : NULL;
//            $stock_transfer_detail->product_id = $product_id;
//            $stock_transfer_detail->barcode = $product_info->barcode;
//            $stock_transfer_detail->qty = $data['qty'];
//            $stock_transfer_detail->vat_amount = $data['qty']*$product_info->whole_sale_price;
//            $stock_transfer_detail->price = $product_info->purchase_price;
//            $stock_transfer_detail->sub_total = ($data['qty']*$product_info->whole_sale_price) + ($data['qty']*$product_info->purchase_price);
//            $stock_transfer_detail->issue_date = $date;
//            $stock_transfer_detail->save();
//
//
//            $check_previous_warehouse_current_stock = Stock::where('warehouse_id',$warehouse_id)
//                ->where('product_id',$product_id)
//                ->where('stock_where','warehouse')
//                ->latest('id','desc')
//                ->pluck('current_stock')
//                ->first();
//
//            if($check_previous_warehouse_current_stock){
//                $previous_warehouse_current_stock = $check_previous_warehouse_current_stock;
//            }else{
//                $previous_warehouse_current_stock = 0;
//            }
//
//            // stock out warehouse product
//            $stock = new Stock();
//            $stock->ref_id = $stock_transfer_insert_id;
//            $stock->user_id = $user_id;
//            $stock->warehouse_id = $warehouse_id;
//            $stock->store_id = NULL;
//            $stock->product_id = $product_id;
//            $stock->product_unit_id = $data['product_unit_id'];
//            $stock->product_brand_id = $data['product_brand_id'] ? $data['product_brand_id'] : NULL;
//            $stock->stock_type = 'from_warehouse_to_store';
//            $stock->stock_where = 'warehouse';
//            $stock->stock_in_out = 'stock_out';
//            $stock->previous_stock = $previous_warehouse_current_stock;
//            $stock->stock_in = 0;
//            $stock->stock_out = $data['qty'];
//            $stock->current_stock = $previous_warehouse_current_stock - $data['qty'];
//            $stock->stock_date = $date;
//            $stock->stock_date_time = $date_time;
//            $stock->save();
//
//
//            $check_previous_store_current_stock = Stock::where('warehouse_id',$warehouse_id)
//                ->where('store_id',$store_id)
//                ->where('product_id',$product_id)
//                ->where('stock_where','store')
//                ->latest('id','desc')
//                ->pluck('current_stock')
//                ->first();
//
//            if($check_previous_store_current_stock){
//                $previous_store_current_stock = $check_previous_store_current_stock;
//            }else{
//                $previous_store_current_stock = 0;
//            }
//
//            // stock in store product
//            $stock = new Stock();
//            $stock->ref_id = $stock_transfer_insert_id;
//            $stock->user_id = $user_id;
//            $stock->warehouse_id = $warehouse_id;
//            $stock->store_id = $store_id;
//            $stock->product_id = $product_id;
//            $stock->product_unit_id = $data['product_unit_id'];
//            $stock->product_brand_id = $data['product_brand_id'] ? $data['product_brand_id'] : NULL;
//            $stock->stock_type = 'from_warehouse_to_store';
//            $stock->stock_where = 'store';
//            $stock->stock_in_out = 'stock_in';
//            $stock->previous_stock = $previous_store_current_stock;
//            $stock->stock_in = $data['qty'];
//            $stock->stock_out = 0;
//            $stock->current_stock = $previous_store_current_stock + $data['qty'];
//            $stock->stock_date = $date;
//            $stock->stock_date_time = $date_time;
//            $stock->save();
//            $insert_id = $stock->id;
//        }


//
//
//
//        if($insert_id){
//            return response()->json(['success'=>true,'response' => 'Warehouse To Store Stock Successfully Inserted.'], $this->successStatus);
//        }else{
//            return response()->json(['success'=>false,'response'=>'No Warehouse To Store Stock Successfully Inserted.!'], $this->failStatus);
//        }
//    }


    public function warehouseToStoreStockCreate(Request $request){
        $this->validate($request, [
            'warehouse_id'=> 'required',
            'store_id'=> 'required',
        ]);

        $date = date('Y-m-d');
        $date_time = date('Y-m-d h:i:s');

        $user_id = Auth::user()->id;
        $warehouse_id = $request->warehouse_id;
        $store_id = $request->store_id;
        $miscellaneous_comment = $request->miscellaneous_comment;
        $miscellaneous_charge = $request->miscellaneous_charge ? $request->miscellaneous_charge : 0;


        $get_invoice_no = StockTransfer::latest()->pluck('invoice_no')->first();
        if(!empty($get_invoice_no)){
            $get_invoice = str_replace("STN-","",$get_invoice_no);
            $invoice_no = $get_invoice+1;
        }else{
            $invoice_no = 1000;
        }

        $total_amount = 0;
        //$total_vat_amount = 0;
        foreach ($request->products as $data) {
            $product_id = $data['product_id'];
            //$price = Product::where('id',$product_id)->pluck('purchase_price')->first();
            $Product_info = Product::where('id',$product_id)->first();
            //$total_vat_amount += ($data['qty']*$Product_info->vat_amount);
            //$total_amount += ($data['qty']*$Product_info->vat_amount) + ($data['qty']*$Product_info->purchase_price);
            $total_amount += $data['qty']*$Product_info->purchase_price;
        }

        $total_amount += $miscellaneous_charge;

        $final_invoice = 'STN-'.$invoice_no;
        $stock_transfer = new StockTransfer();
        $stock_transfer->invoice_no=$final_invoice;
        $stock_transfer->user_id=Auth::user()->id;
        $stock_transfer->warehouse_id = $warehouse_id;
        $stock_transfer->store_id = $store_id;
        $stock_transfer->total_vat_amount = 0;
        $stock_transfer->miscellaneous_comment = $miscellaneous_comment;
        $stock_transfer->miscellaneous_charge = $miscellaneous_charge;
        $stock_transfer->total_amount = $total_amount;
        $stock_transfer->paid_amount = 0;
        $stock_transfer->due_amount = $total_amount;
        $stock_transfer->issue_date = $date;
        $stock_transfer->due_date = $date;
        $stock_transfer->save();
        $stock_transfer_insert_id = $stock_transfer->id;

        $insert_id = false;

        foreach ($request->products as $data) {

            $product_id = $data['product_id'];
            $product_info = Product::where('id',$product_id)->first();


            $stock_transfer_detail = new StockTransferDetail();
            $stock_transfer_detail->stock_transfer_id = $stock_transfer_insert_id;
            $stock_transfer_detail->product_unit_id = $data['product_unit_id'];
            $stock_transfer_detail->product_brand_id = $data['product_brand_id'] ? $data['product_brand_id'] : NULL;
            $stock_transfer_detail->product_id = $product_id;
            $stock_transfer_detail->barcode = $product_info->barcode;
            $stock_transfer_detail->qty = $data['qty'];
            //$stock_transfer_detail->vat_amount = $data['qty']*$product_info->vat_percentage;
            $stock_transfer_detail->vat_amount = 0;
            $stock_transfer_detail->price = $product_info->purchase_price;
            //$stock_transfer_detail->sub_total = ($data['qty']*$product_info->vat_percentage) + ($data['qty']*$product_info->purchase_price);
            $stock_transfer_detail->sub_total = $data['qty']*$product_info->purchase_price;
            $stock_transfer_detail->issue_date = $date;
            $stock_transfer_detail->save();


            $check_previous_warehouse_current_stock = Stock::where('warehouse_id',$warehouse_id)
                ->where('product_id',$product_id)
                ->where('stock_where','warehouse')
                ->latest('id','desc')
                ->pluck('current_stock')
                ->first();

            if($check_previous_warehouse_current_stock){
                $previous_warehouse_current_stock = $check_previous_warehouse_current_stock;
            }else{
                $previous_warehouse_current_stock = 0;
            }

            // stock out warehouse product
            $stock = new Stock();
            $stock->ref_id = $stock_transfer_insert_id;
            $stock->user_id = $user_id;
            $stock->warehouse_id = $warehouse_id;
            $stock->store_id = NULL;
            $stock->product_id = $product_id;
            $stock->product_unit_id = $data['product_unit_id'];
            $stock->product_brand_id = $data['product_brand_id'] ? $data['product_brand_id'] : NULL;
            $stock->stock_type = 'from_warehouse_to_store';
            $stock->stock_where = 'warehouse';
            $stock->stock_in_out = 'stock_out';
            $stock->previous_stock = $previous_warehouse_current_stock;
            $stock->stock_in = 0;
            $stock->stock_out = $data['qty'];
            $stock->current_stock = $previous_warehouse_current_stock - $data['qty'];
            $stock->stock_date = $date;
            $stock->stock_date_time = $date_time;
            $stock->save();


            $check_previous_store_current_stock = Stock::where('warehouse_id',$warehouse_id)
                ->where('store_id',$store_id)
                ->where('product_id',$product_id)
                ->where('stock_where','store')
                ->latest('id','desc')
                ->pluck('current_stock')
                ->first();

            if($check_previous_store_current_stock){
                $previous_store_current_stock = $check_previous_store_current_stock;
            }else{
                $previous_store_current_stock = 0;
            }

            // stock in store product
            $stock = new Stock();
            $stock->ref_id = $stock_transfer_insert_id;
            $stock->user_id = $user_id;
            $stock->warehouse_id = $warehouse_id;
            $stock->store_id = $store_id;
            $stock->product_id = $product_id;
            $stock->product_unit_id = $data['product_unit_id'];
            $stock->product_brand_id = $data['product_brand_id'] ? $data['product_brand_id'] : NULL;
            $stock->stock_type = 'from_warehouse_to_store';
            $stock->stock_where = 'store';
            $stock->stock_in_out = 'stock_in';
            $stock->previous_stock = $previous_store_current_stock;
            $stock->stock_in = $data['qty'];
            $stock->stock_out = 0;
            $stock->current_stock = $previous_store_current_stock + $data['qty'];
            $stock->stock_date = $date;
            $stock->stock_date_time = $date_time;
            $stock->save();
            $insert_id = $stock->id;

            // warehouse current stock
            $warehouse_current_stock_update = WarehouseCurrentStock::where('warehouse_id',$request->warehouse_id)
                ->where('product_id',$product_id)
                ->first();
            $exists_current_stock = $warehouse_current_stock_update->current_stock;
            $final_warehouse_current_stock = $exists_current_stock - $data['qty'];
            $warehouse_current_stock_update->current_stock=$final_warehouse_current_stock;
            $warehouse_current_stock_update->save();

            // warehouse store current stock
            $check_exists_warehouse_store_current_stock = WarehouseStoreCurrentStock::where('warehouse_id',$warehouse_id)
                ->where('store_id',$store_id)
                ->where('product_id',$product_id)
                ->first();

            if($check_exists_warehouse_store_current_stock){
                $exists_current_stock = $check_exists_warehouse_store_current_stock->current_stock;
                $final_warehouse_current_stock = $exists_current_stock + $data['qty'];
                $check_exists_warehouse_store_current_stock->current_stock=$final_warehouse_current_stock;
                $check_exists_warehouse_store_current_stock->save();
            }else{
                $warehouse_store_current_stock = new WarehouseStoreCurrentStock();
                $warehouse_store_current_stock->warehouse_id=$warehouse_id;
                $warehouse_store_current_stock->store_id=$store_id;
                $warehouse_store_current_stock->product_id=$product_id;
                $warehouse_store_current_stock->current_stock=$data['qty'];
                $warehouse_store_current_stock->save();
            }
        }

        // transaction
//        $transaction = new Transaction();
//        $transaction->ref_id = $stock_transfer_insert_id;
//        $transaction->invoice_no = $final_invoice;
//        $transaction->user_id = $user_id;
//        $transaction->warehouse_id = $request->warehouse_id;
//        $transaction->party_id = $request->party_id;
//        $transaction->transaction_type = '';
//        $transaction->payment_type = 'Cash';
//        $transaction->amount = $request->total_amount;
//        $transaction->transaction_date = $date;
//        $transaction->transaction_date_time = $date_time;
//        $transaction->save();



        if($insert_id){
            return response()->json(['success'=>true,'response' => 'Warehouse To Store Stock Successfully Inserted.'], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Warehouse To Store Stock Successfully Inserted.!'], $this->failStatus);
        }
    }

    public function stockTransferList(){
        $stock_transfer_lists = DB::table('stock_transfers')
            ->leftJoin('users','stock_transfers.user_id','users.id')
            ->leftJoin('warehouses','stock_transfers.warehouse_id','warehouses.id')
            ->leftJoin('stores','stock_transfers.store_id','stores.id')
            //->where('stock_transfers.sale_type','whole_sale')
            ->select('stock_transfers.id','stock_transfers.invoice_no','stock_transfers.total_amount','stock_transfers.issue_date','stock_transfers.miscellaneous_comment','stock_transfers.miscellaneous_charge','stock_transfers.total_vat_amount','users.name as user_name','warehouses.id as warehouse_id','warehouses.name as warehouse_name','stores.id as store_id','stores.name as store_name','stores.phone as store_phone','stores.email as store_email','stores.address as store_address')
            ->orderBy('stock_transfers.id','desc')
            ->get();

        if($stock_transfer_lists)
        {
            $success['stock_transfer_list'] =  $stock_transfer_lists;
            return response()->json(['success'=>true,'response' => $success], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Stock Transfer List Found!'], $this->failStatus);
        }
    }

    public function stockTransferDetails(Request $request){
        $stock_transfer_details = DB::table('stock_transfers')
            ->join('stock_transfer_details','stock_transfers.id','stock_transfer_details.stock_transfer_id')
            ->leftJoin('products','stock_transfer_details.product_id','products.id')
            ->leftJoin('product_units','stock_transfer_details.product_unit_id','product_units.id')
            ->leftJoin('product_brands','stock_transfer_details.product_brand_id','product_brands.id')
            ->where('stock_transfers.id',$request->stock_transfer_id)
            ->select('products.id as product_id','products.name as product_name','product_units.id as product_unit_id','product_units.name as product_unit_name','product_brands.id as product_brand_id','product_brands.name as product_brand_name','stock_transfer_details.qty','stock_transfer_details.id as stock_transfer_detail_id','stock_transfer_details.price','stock_transfer_details.sub_total','stock_transfer_details.vat_amount')
            ->get();

        if($stock_transfer_details)
        {
            $success['stock_transfer_details'] =  $stock_transfer_details;
            return response()->json(['success'=>true,'response' => $success], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Stock Transfer Details Found!'], $this->failStatus);
        }
    }


//    public function storeStockList(Request $request){
//        $store_stock_list = DB::table('stocks')
//            ->leftJoin('users','stocks.user_id','users.id')
//            ->leftJoin('warehouses','stocks.warehouse_id','warehouses.id')
//            ->leftJoin('product_units','stocks.product_unit_id','product_units.id')
//            ->leftJoin('product_brands','stocks.product_brand_id','product_brands.id')
//            ->leftJoin('products','stocks.product_id','products.id')
//            ->where('stocks.stock_where','store')
//            ->where('stocks.store_id',$request->store_id)
//            ->select('stocks.id as stock_id','users.name as stock_by_user','warehouses.name as warehouse_name','product_units.name as product_unit_name','product_brands.name as product_brand_name','products.name as product_name','stocks.stock_type','stocks.stock_where','stocks.stock_in_out','stocks.previous_stock','stocks.stock_in','stocks.stock_out','stocks.current_stock','stocks.stock_date','stocks.stock_date_time')
//            ->latest('stocks.id','desc')
//            ->get();
//
//        if($store_stock_list)
//        {
//            $success['store_stock_list'] =  $store_stock_list;
//            return response()->json(['success'=>true,'response' => $success], $this->successStatus);
//        }else{
//            return response()->json(['success'=>false,'response'=>'No Store Stock List Found!'], $this->failStatus);
//        }
//    }

//    public function storeStockLowList(){
//
//        $store_stock_low_list = DB::table('stocks')
//            ->leftJoin('users','stocks.user_id','users.id')
//            ->leftJoin('warehouses','stocks.warehouse_id','warehouses.id')
//            ->leftJoin('product_units','stocks.product_unit_id','product_units.id')
//            ->leftJoin('product_brands','stocks.product_brand_id','product_brands.id')
//            ->leftJoin('products','stocks.product_id','products.id')
//            ->where('stocks.stock_where','store')
//            //->where('stocks.current_stock','<',2)
//            ->whereIn('stocks.id', function($query) {
//                $query->from('stocks')->where('current_stock','<', 2)->groupBy('product_id')->selectRaw('MAX(id)');
//            })
//            ->select('stocks.id as stock_id','users.name as stock_by_user','warehouses.name as warehouse_name','product_units.name as product_unit_name','product_brands.name as product_brand_name','products.id as product_id','products.name as product_name','stocks.stock_type','stocks.stock_where','stocks.previous_stock','stocks.stock_in','stocks.stock_out','stocks.current_stock','stocks.stock_date','stocks.stock_date_time')
//            ->latest('stocks.id','desc')
//            ->get();
//
//        if($store_stock_low_list)
//        {
//            $success['store_stock_low_list'] =  $store_stock_low_list;
//            return response()->json(['success'=>true,'response' => $success], $this->successStatus);
//        }else{
//            return response()->json(['success'=>false,'response'=>'No Store Stock Low List Found!'], $this->failStatus);
//        }
//    }

//    public function storeCurrentStockList(Request $request){
//        $store_stock_product_list = Stock::where('store_id',$request->store_id)
//            ->select('product_id')
//            ->groupBy('product_id')
//            ->latest('id')
//            ->get();
//
//        $store_stock_product = [];
//        foreach($store_stock_product_list as $data){
//
//            $stock_row = DB::table('stocks')
//                ->join('warehouses','stocks.warehouse_id','warehouses.id')
//                ->leftJoin('products','stocks.product_id','products.id')
//                ->leftJoin('product_units','stocks.product_unit_id','product_units.id')
//                ->leftJoin('product_brands','stocks.product_brand_id','product_brands.id')
//                ->where('stocks.stock_where','store')
//                ->where('stocks.product_id',$data->product_id)
//                ->where('stocks.store_id',$request->store_id)
//                ->select('stocks.*','warehouses.name as warehouse_name','products.name as product_name','products.purchase_price','products.whole_sale_price','products.selling_price','products.item_code','products.barcode','products.image','products.vat_status','products.vat_percentage','products.vat_amount','products.vat_whole_amount','product_units.name as product_unit_name','product_brands.name as product_brand_name')
//                ->orderBy('stocks.id','desc')
//                ->first();
//
//            if($stock_row){
//                $nested_data['stock_id'] = $stock_row->id;
//                $nested_data['warehouse_id'] = $stock_row->warehouse_id;
//                $nested_data['warehouse_name'] = $stock_row->warehouse_name;
//                $nested_data['product_id'] = $stock_row->product_id;
//                $nested_data['product_name'] = $stock_row->product_name;
//                $nested_data['purchase_price'] = $stock_row->purchase_price;
//                $nested_data['whole_sale_price'] = $stock_row->whole_sale_price;
//                $nested_data['selling_price'] = $stock_row->selling_price;
//                $nested_data['vat_status'] = $stock_row->vat_status;
//                $nested_data['vat_percentage'] = $stock_row->vat_percentage;
//                $nested_data['vat_amount'] = $stock_row->vat_amount;
//                $nested_data['vat_whole_amount'] = $stock_row->vat_whole_amount;
//                $nested_data['item_code'] = $stock_row->item_code;
//                $nested_data['barcode'] = $stock_row->barcode;
//                $nested_data['image'] = $stock_row->image;
//                $nested_data['product_unit_id'] = $stock_row->product_unit_id;
//                $nested_data['product_unit_name'] = $stock_row->product_unit_name;
//                $nested_data['product_brand_id'] = $stock_row->product_brand_id;
//                $nested_data['product_brand_name'] = $stock_row->product_brand_name;
//                $nested_data['current_stock'] = $stock_row->current_stock;
//
//                array_push($store_stock_product,$nested_data);
//            }
//        }
//
//        if($store_stock_product)
//        {
//            $success['store_current_stock_list'] =  $store_stock_product;
//            return response()->json(['success'=>true,'response' => $success], $this->successStatus);
//        }else{
//            return response()->json(['success'=>false,'response'=>'No Store Current Stock List Found!'], $this->failStatus);
//        }
//    }

    public function storeCurrentStockList(Request $request){

        $store_stock_product_list = DB::table('warehouse_store_current_stocks')
            ->join('warehouses','warehouse_store_current_stocks.warehouse_id','warehouses.id')
            ->leftJoin('products','warehouse_store_current_stocks.product_id','products.id')
            ->leftJoin('product_units','products.product_unit_id','product_units.id')
            ->leftJoin('product_brands','products.product_brand_id','product_brands.id')
            ->where('warehouse_store_current_stocks.store_id',$request->store_id)
            ->select('warehouse_store_current_stocks.*','warehouses.name as warehouse_name','products.name as product_name','products.purchase_price','products.whole_sale_price','products.selling_price','products.item_code','products.barcode','products.image','products.vat_status','products.vat_percentage','products.vat_amount','products.vat_whole_amount','product_units.id as product_unit_id','product_units.name as product_unit_name','product_brands.id as product_brand_id','product_brands.name as product_brand_name')
            ->get();

        $store_stock_product = [];
        foreach($store_stock_product_list as $stock_row){
                $nested_data['stock_id'] = $stock_row->id;
                $nested_data['warehouse_id'] = $stock_row->warehouse_id;
                $nested_data['warehouse_name'] = $stock_row->warehouse_name;
                $nested_data['product_id'] = $stock_row->product_id;
                $nested_data['product_name'] = $stock_row->product_name;
                $nested_data['purchase_price'] = $stock_row->purchase_price;
                $nested_data['whole_sale_price'] = $stock_row->whole_sale_price;
                $nested_data['selling_price'] = $stock_row->selling_price;
                $nested_data['vat_status'] = $stock_row->vat_status;
                $nested_data['vat_percentage'] = $stock_row->vat_percentage;
                $nested_data['vat_amount'] = $stock_row->vat_amount;
                $nested_data['vat_whole_amount'] = $stock_row->vat_whole_amount;
                $nested_data['item_code'] = $stock_row->item_code;
                $nested_data['barcode'] = $stock_row->barcode;
                $nested_data['image'] = $stock_row->image;
                $nested_data['product_unit_id'] = $stock_row->product_unit_id;
                $nested_data['product_unit_name'] = $stock_row->product_unit_name;
                $nested_data['product_brand_id'] = $stock_row->product_brand_id;
                $nested_data['product_brand_name'] = $stock_row->product_brand_name;
                $nested_data['current_stock'] = $stock_row->current_stock;

                array_push($store_stock_product,$nested_data);

        }

        if($store_stock_product)
        {
            $success['store_current_stock_list'] =  $store_stock_product;
            return response()->json(['success'=>true,'response' => $success], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Store Current Stock List Found!'], $this->failStatus);
        }
    }

    public function storeCurrentStockListWithoutZero(Request $request){
        $store_stock_product_list = DB::table('warehouse_store_current_stocks')
            ->join('warehouses','warehouse_store_current_stocks.warehouse_id','warehouses.id')
            ->leftJoin('products','warehouse_store_current_stocks.product_id','products.id')
            ->leftJoin('product_units','products.product_unit_id','product_units.id')
            ->leftJoin('product_brands','products.product_brand_id','product_brands.id')
            ->where('warehouse_store_current_stocks.store_id',$request->store_id)
            ->where('warehouse_store_current_stocks.current_stock','!=',0)
            ->select('warehouse_store_current_stocks.*','warehouses.name as warehouse_name','products.name as product_name','products.purchase_price','products.whole_sale_price','products.selling_price','products.item_code','products.barcode','products.image','products.vat_status','products.vat_percentage','products.vat_amount','products.vat_whole_amount','product_units.id as product_unit_id','product_units.name as product_unit_name','product_brands.id as product_brand_id','product_brands.name as product_brand_name')
            ->get();

        $store_stock_product = [];
        foreach($store_stock_product_list as $stock_row){
            $nested_data['stock_id'] = $stock_row->id;
            $nested_data['warehouse_id'] = $stock_row->warehouse_id;
            $nested_data['warehouse_name'] = $stock_row->warehouse_name;
            $nested_data['product_id'] = $stock_row->product_id;
            $nested_data['product_name'] = $stock_row->product_name;
            $nested_data['purchase_price'] = $stock_row->purchase_price;
            $nested_data['whole_sale_price'] = $stock_row->whole_sale_price;
            $nested_data['selling_price'] = $stock_row->selling_price;
            $nested_data['vat_status'] = $stock_row->vat_status;
            $nested_data['vat_percentage'] = $stock_row->vat_percentage;
            $nested_data['vat_amount'] = $stock_row->vat_amount;
            $nested_data['vat_whole_amount'] = $stock_row->vat_whole_amount;
            $nested_data['item_code'] = $stock_row->item_code;
            $nested_data['barcode'] = $stock_row->barcode;
            $nested_data['image'] = $stock_row->image;
            $nested_data['product_unit_id'] = $stock_row->product_unit_id;
            $nested_data['product_unit_name'] = $stock_row->product_unit_name;
            $nested_data['product_brand_id'] = $stock_row->product_brand_id;
            $nested_data['product_brand_name'] = $stock_row->product_brand_name;
            $nested_data['current_stock'] = $stock_row->current_stock;

            array_push($store_stock_product,$nested_data);

        }

        if($store_stock_product)
        {
            $success['store_current_stock_list'] =  $store_stock_product;
            return response()->json(['success'=>true,'response' => $success], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Store Current Stock List Found!'], $this->failStatus);
        }
    }

    public function productWholePurchaseCreateWithLowProduct(Request $request){

        $this->validate($request, [
            //'user_id'=> 'required',
            'party_id'=> 'required',
            'warehouse_id'=> 'required',
            'paid_amount'=> 'required',
            'due_amount'=> 'required',
            'total_amount'=> 'required',
            'payment_type'=> 'required',
        ]);


        $get_invoice_no = ProductPurchase::latest()->pluck('invoice_no')->first();
        if(!empty($get_invoice_no)){
            $get_invoice = str_replace("purchase-","",$get_invoice_no);
            $invoice_no = $get_invoice+1;
        }else{
            $invoice_no = 1000;
        }
        $final_invoice = 'purchase-'.$invoice_no;

        $date = date('Y-m-d');
        $date_time = date('Y-m-d h:i:s');

        $user_id = Auth::user()->id;

        // product purchase
        $productPurchase = new ProductPurchase();
        $productPurchase ->invoice_no = $final_invoice;
        $productPurchase ->user_id = $user_id;
        $productPurchase ->party_id = $request->party_id;
        $productPurchase ->warehouse_id = $request->warehouse_id;
        $productPurchase ->purchase_type = 'whole_purchase';
        $productPurchase ->paid_amount = $request->paid_amount;
        $productPurchase ->due_amount = $request->due_amount;
        $productPurchase ->total_amount = $request->total_amount;
        $productPurchase ->purchase_date = $date;
        $productPurchase ->purchase_date_time = $date_time;
        $productPurchase->save();
        $insert_id = $productPurchase->id;

        if($insert_id)
        {
            $product_id =  $request->product_id;

            $barcode = Product::where('id',$product_id)->pluck('barcode')->first();

            // product purchase detail
            $purchase_purchase_detail = new ProductPurchaseDetail();
            $purchase_purchase_detail->product_purchase_id = $insert_id;
            $purchase_purchase_detail->product_unit_id = $request->product_unit_id;
            $purchase_purchase_detail->product_brand_id = $request->product_brand_id ? $request->product_brand_id : NULL;
            $purchase_purchase_detail->product_id = $product_id;
            $purchase_purchase_detail->qty = $request->qty;
            $purchase_purchase_detail->price = $request->price;
            $purchase_purchase_detail->mrp_price = $request->mrp_price;
            $purchase_purchase_detail->sub_total = $request->qty*$request->price;
            $purchase_purchase_detail->barcode = $barcode;
            $purchase_purchase_detail->save();

            $check_previous_stock = Stock::where('product_id',$product_id)->latest()->pluck('current_stock')->first();
            if(!empty($check_previous_stock)){
                $previous_stock = $check_previous_stock;
            }else{
                $previous_stock = 0;
            }

            // product stock
            $stock = new Stock();
            $stock->ref_id = $insert_id;
            $stock->user_id = $user_id;
            $stock->warehouse_id = $request->warehouse_id;
            $stock->product_id = $product_id;
            $stock->product_unit_id = $request->product_unit_id;
            $purchase_purchase_detail->product_brand_id = $request->product_brand_id ? $request->product_brand_id : NULL;
            $stock->stock_type = 'whole_purchase';
            $stock->stock_where = 'warehouse';
            $stock->stock_in_out = 'stock_in';
            $stock->previous_stock = $previous_stock;
            $stock->stock_in = $request->qty;
            $stock->stock_out = 0;
            $stock->current_stock = $previous_stock +$request->qty;
            $stock->stock_date = $date;
            $stock->stock_date_time = $date_time;
            $stock->save();

            // warehouse current stock
            $check_exists_warehouse_current_stock = WarehouseCurrentStock::where('warehouse_id',$request->warehouse_id)
                ->where('product_id',$product_id)
                ->first();
            if($check_exists_warehouse_current_stock){
                $warehouse_current_stock_update = WarehouseCurrentStock::find($check_exists_warehouse_current_stock->id);
                $warehouse_current_stock_update->current_stock=$check_exists_warehouse_current_stock->current_stock + $data['qty'];
                $warehouse_current_stock_update->save();
            }else{
                $warehouse_current_stock = new WarehouseCurrentStock();
                $warehouse_current_stock->warehouse_id=$request->warehouse_id;
                $warehouse_current_stock->product_id=$product_id;
                $warehouse_current_stock->current_stock=$request->qty;
                $warehouse_current_stock->save();
            }


            // transaction
            $transaction = new Transaction();
            $transaction->ref_id = $insert_id;
            $transaction->invoice_no = $final_invoice;
            $transaction->user_id = $user_id;
            $transaction->warehouse_id = $request->warehouse_id;
            $transaction->party_id = $request->party_id;
            $transaction->transaction_type = 'whole_purchase';
            $transaction->payment_type = $request->payment_type;
            $transaction->amount = $request->paid_amount;
            $transaction->transaction_date = $date;
            $transaction->transaction_date_time = $date_time;
            $transaction->save();

            // payment paid
            $payment_paid = new PaymentPaid();
            $payment_paid->invoice_no = $final_invoice;
            $payment_paid->product_purchase_id = $insert_id;
            $payment_paid->user_id = $user_id;
            $payment_paid->party_id = $request->party_id;
            $payment_paid->paid_type = 'Purchase';
            $payment_paid->paid_amount = $request->paid_amount;
            $payment_paid->due_amount = $request->due_amount;
            $payment_paid->current_paid_amount = $request->paid_amount;
            $payment_paid->paid_date = $date;
            $payment_paid->paid_date_time = $date_time;
            $payment_paid->save();


            return response()->json(['success'=>true,'response' => 'Inserted Successfully.'], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Inserted Created!'], $this->failStatus);
        }
    }

    public function productWholeSaleList(){
        $product_whole_sales = DB::table('product_sales')
            ->leftJoin('users','product_sales.user_id','users.id')
            ->leftJoin('parties','product_sales.party_id','parties.id')
            ->leftJoin('warehouses','product_sales.warehouse_id','warehouses.id')
            ->leftJoin('stores','product_sales.store_id','stores.id')
            ->where('product_sales.sale_type','whole_sale')
            ->select('product_sales.id','product_sales.invoice_no','product_sales.discount_type','product_sales.discount_amount','product_sales.total_vat_amount','product_sales.total_amount','product_sales.paid_amount','product_sales.due_amount','product_sales.sale_date_time','users.name as user_name','parties.id as customer_id','parties.name as customer_name','warehouses.id as warehouse_id','warehouses.name as warehouse_name','stores.id as store_id','stores.name as store_name','stores.address as store_address')
            ->orderBy('product_sales.id','desc')
            ->get();

        if(count($product_whole_sales) > 0)
        {
            $product_whole_sale_arr = [];
            foreach ($product_whole_sales as $data){
                $payment_type = DB::table('transactions')->where('ref_id',$data->id)->where('transaction_type','whole_sale')->pluck('payment_type')->first();

                $nested_data['id']=$data->id;
                $nested_data['invoice_no']=$data->invoice_no;
                $nested_data['discount_type']=$data->discount_type;
                $nested_data['discount_amount']=$data->discount_amount;
                $nested_data['total_vat_amount']=$data->total_vat_amount;
                $nested_data['total_amount']=$data->total_amount;
                $nested_data['paid_amount']=$data->paid_amount;
                $nested_data['due_amount']=$data->due_amount;
                $nested_data['sale_date_time']=$data->sale_date_time;
                $nested_data['user_name']=$data->user_name;
                $nested_data['customer_id']=$data->customer_id;
                $nested_data['customer_name']=$data->customer_name;
                $nested_data['warehouse_id']=$data->warehouse_id;
                $nested_data['warehouse_name']=$data->warehouse_name;
                $nested_data['store_id']=$data->store_id;
                $nested_data['store_name']=$data->store_name;
                $nested_data['store_address']=$data->store_address;
                $nested_data['payment_type']=$payment_type;

                array_push($product_whole_sale_arr,$nested_data);
            }

            $success['product_whole_sales'] =  $product_whole_sale_arr;
            return response()->json(['success'=>true,'response' => $success], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Product Whole Sale List Found!'], $this->failStatus);
        }
    }

//    public function productWholeSaleDetails(Request $request){
//        $product_sale_details = DB::table('product_sales')
//            ->join('product_sale_details','product_sales.id','product_sale_details.product_sale_id')
//            ->leftJoin('products','product_sale_details.product_id','products.id')
//            ->leftJoin('product_units','product_sale_details.product_unit_id','product_units.id')
//            ->leftJoin('product_brands','product_sale_details.product_brand_id','product_brands.id')
//            ->where('product_sales.id',$request->product_purchase_id)
//            ->select('products.id as product_id','products.name as product_name','product_units.id as product_unit_id','product_units.name as product_unit_name','product_brands.id as product_brand_id','product_brands.name as product_brand_name','product_sale_details.qty','product_sale_details.price')
//            ->get();
//
//        if($product_sale_details)
//        {
//            $success['product_whole_sale_details'] =  $product_sale_details;
//            return response()->json(['success'=>true,'response' => $success], $this->successStatus);
//        }else{
//            return response()->json(['success'=>false,'response'=>'No Product Whole Sale Detail Found!'], $this->failStatus);
//        }
//    }

    public function productWholeSaleDetails(Request $request){
        $product_sale_details = DB::table('product_sales')
            ->join('product_sale_details','product_sales.id','product_sale_details.product_sale_id')
            ->leftJoin('products','product_sale_details.product_id','products.id')
            ->leftJoin('product_units','product_sale_details.product_unit_id','product_units.id')
            ->leftJoin('product_brands','product_sale_details.product_brand_id','product_brands.id')
            ->where('product_sales.id',$request->product_sale_id)
            ->select('products.id as product_id','products.name as product_name','product_units.id as product_unit_id','product_units.name as product_unit_name','product_brands.id as product_brand_id','product_brands.name as product_brand_name','product_sale_details.qty','product_sale_details.id as product_sale_detail_id','product_sale_details.price as mrp_price','product_sale_details.vat_amount')
            ->get();

        if($product_sale_details)
        {
            $success['product_whole_sale_details'] =  $product_sale_details;
            return response()->json(['success'=>true,'response' => $success], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Product Whole Sale Detail Found!'], $this->failStatus);
        }
    }

    public function productWholeSaleCreate(Request $request){

        $this->validate($request, [
            'party_id'=> 'required',
            'store_id'=> 'required',
            'paid_amount'=> 'required',
            'due_amount'=> 'required',
            'total_amount'=> 'required',
            'payment_type'=> 'required',
        ]);

        $get_invoice_no = ProductSale::latest()->pluck('invoice_no')->first();
        if(!empty($get_invoice_no)){
            $get_invoice = str_replace("sale-","",$get_invoice_no);
            $invoice_no = $get_invoice+1;
        }else{
            $invoice_no = 100000;
        }
        $final_invoice = 'sale-'.$invoice_no;

        $date = date('Y-m-d');
        $date_time = date('Y-m-d h:i:s');
        $add_two_day_date =  date('Y-m-d', strtotime("+2 days"));

        $user_id = Auth::user()->id;
        $store_id = $request->store_id;
        $warehouse_id = Store::where('id',$store_id)->pluck('warehouse_id')->first();

        // product purchase
        $productSale = new ProductSale();
        $productSale->invoice_no = $final_invoice;
        $productSale->user_id = $user_id;
        $productSale->store_id = $store_id;
        $productSale->warehouse_id = $warehouse_id;
        $productSale->party_id = $request->party_id;
        $productSale->sale_type = 'whole_sale';
        $productSale->discount_type = $request->discount_type ? $request->discount_type : NULL;
        $productSale->discount_amount = $request->discount_amount ? $request->discount_amount : 0;
        $productSale->miscellaneous_comment = $request->miscellaneous_comment ? $request->miscellaneous_comment : NULL;
        $productSale->miscellaneous_charge = $request->miscellaneous_charge ? $request->miscellaneous_charge : 0;
        $productSale->paid_amount = $request->paid_amount;
        $productSale->due_amount = $request->due_amount;
        $productSale->total_vat_amount = $request->total_vat_amount;
        $productSale->total_amount = $request->total_amount;
        $productSale->sale_date = $date;
        $productSale->sale_date_time = $date_time;
        $productSale->save();
        $insert_id = $productSale->id;

        if($insert_id)
        {
            // for postman testing
//            for($i=0; $i<$row_count;$i++)
//            {
//                $product_id = $request['products']['product_id'][$i];
//
//                $barcode = Product::where('id',$product_id)->pluck('barcode')->first();
//
//                // product purchase detail
//                $purchase_purchase_detail = new ProductPurchaseDetail();
//                $purchase_purchase_detail->product_purchase_id = $insert_id;
//                $purchase_purchase_detail->product_unit_id = $request->product_unit_id[$i];
//                $purchase_purchase_detail->product_brand_id = $request->product_brand_id[$i] ? $request->product_brand_id[$i] : NULL;
//                $purchase_purchase_detail->product_id = $request->product_id[$i];
//                $purchase_purchase_detail->qty = $request->qty[$i];
//                $purchase_purchase_detail->price = $request->price[$i];
//                $purchase_purchase_detail->mrp_price = $request->mrp_price[$i];
//                $purchase_purchase_detail->sub_total = $request->qty[$i]*$request->price[$i];
//                $purchase_purchase_detail->barcode = $barcode;
//                $purchase_purchase_detail->save();
//
//                $check_previous_stock = Stock::where('product_id',$product_id)->latest()->pluck('current_stock')->first();
//                if(!empty($check_previous_stock)){
//                    $previous_stock = $check_previous_stock;
//                }else{
//                    $previous_stock = 0;
//                }
//
//                // product stock
//                $stock = new Stock();
//                $stock->ref_id = $insert_id;
//                $stock->user_id = $user_id;
//                $stock->warehouse_id = $request->warehouse_id;
//                $stock->product_id = $request->product_id[$i];
//                $stock->product_unit_id = $request->product_unit_id[$i];
//                $stock->product_brand_id = $request->product_brand_id[$i] ? $request->product_brand_id[$i] : NULL;
//                $stock->stock_type = 'whole_purchase';
//                $stock->previous_stock = $previous_stock;
//                $stock->stock_in = $request->qty[$i];
//                $stock->stock_out = 0;
//                $stock->current_stock = $previous_stock + $request->qty[$i];
//                $stock->stock_date = $date;
//                $stock->stock_date_time = $date_time;
//                $stock->save();
//            }

            // for live testing
            foreach ($request->products as $data) {

                $product_id =  $data['product_id'];

                $barcode = Product::where('id',$product_id)->pluck('barcode')->first();

                // product sale detail
                $product_sale_detail = new ProductSaleDetail();
                $product_sale_detail->product_sale_id = $insert_id;
                $product_sale_detail->product_unit_id = $data['product_unit_id'];
                $product_sale_detail->product_brand_id = $data['product_brand_id'] ? $data['product_brand_id'] : NULL;
                $product_sale_detail->product_id = $product_id;
                $product_sale_detail->qty = $data['qty'];
                $product_sale_detail->price = $data['mrp_price'];
                $product_sale_detail->vat_amount = $data['vat_amount'];
                $product_sale_detail->sub_total = ($data['qty']*$data['mrp_price']) + ($data['qty']*$data['vat_amount']);
                $product_sale_detail->barcode = $barcode;
                $product_sale_detail->sale_date = $date;
                $product_sale_detail->return_among_day = 2;
                $product_sale_detail->return_last_date = $add_two_day_date;
                $product_sale_detail->save();

                $check_previous_stock = Stock::where('warehouse_id',$warehouse_id)->where('store_id',$store_id)->where('stock_where','store')->where('product_id',$product_id)->latest()->pluck('current_stock')->first();
                if(!empty($check_previous_stock)){
                    $previous_stock = $check_previous_stock;
                }else{
                    $previous_stock = 0;
                }

                // product stock
                $stock = new Stock();
                $stock->ref_id = $insert_id;
                $stock->user_id = $user_id;
                $stock->warehouse_id = $warehouse_id;
                $stock->store_id = $store_id;
                $stock->product_id = $product_id;
                $stock->product_unit_id = $data['product_unit_id'];
                $stock->product_brand_id = $data['product_brand_id'] ? $data['product_brand_id'] : NULL;
                $stock->stock_type = 'whole_sale';
                $stock->stock_where = 'store';
                $stock->stock_in_out = 'stock_out';
                $stock->previous_stock = $previous_stock;
                $stock->stock_in = 0;
                $stock->stock_out = $data['qty'];
                $stock->current_stock = $previous_stock - $data['qty'];
                $stock->stock_date = $date;
                $stock->stock_date_time = $date_time;
                $stock->save();

                // warehouse store current stock
                $update_warehouse_store_current_stock = WarehouseStoreCurrentStock::where('warehouse_id',$warehouse_id)
                    ->where('store_id',$store_id)
                    ->where('product_id',$product_id)
                    ->first();

                $exists_current_stock = $update_warehouse_store_current_stock->current_stock;
                $final_warehouse_current_stock = $exists_current_stock - $data['qty'];
                $update_warehouse_store_current_stock->current_stock=$final_warehouse_current_stock;
                $update_warehouse_store_current_stock->save();
            }

            // transaction
            $transaction = new Transaction();
            $transaction->ref_id = $insert_id;
            $transaction->invoice_no = $final_invoice;
            $transaction->user_id = $user_id;
            $transaction->warehouse_id = $warehouse_id;
            $transaction->store_id = $store_id;
            $transaction->party_id = $request->party_id;
            $transaction->transaction_type = 'whole_sale';
            $transaction->payment_type = $request->payment_type;
            $transaction->amount = $request->paid_amount;
            $transaction->transaction_date = $date;
            $transaction->transaction_date_time = $date_time;
            $transaction->save();
            $transaction_id = $transaction->id;

            // payment paid
            $payment_collection = new PaymentCollection();
            $payment_collection->invoice_no = $final_invoice;
            $payment_collection->product_sale_id = $insert_id;
            $payment_collection->user_id = $user_id;
            $payment_collection->party_id = $request->party_id;
            $payment_collection->collection_type = 'Sale';
            $payment_collection->collection_amount = $request->paid_amount;
            $payment_collection->due_amount = $request->due_amount;
            $payment_collection->current_collection_amount = $request->paid_amount;
            $payment_collection->collection_date = $date;
            $payment_collection->collection_date_time = $date_time;
            $payment_collection->save();


            if($request->payment_type == 'SSL Commerz'){
                return response()->json(['success'=>true,'transaction_id' => $transaction_id,'payment_type' => $request->payment_type], $this->successStatus);
            }else{
                return response()->json(['success'=>true,'response' => 'Inserted Successfully.'], $this->successStatus);
            }
        }else{
            return response()->json(['success'=>false,'response'=>'No Inserted Successfully!'], $this->failStatus);
        }
    }

    public function productWholeSaleEdit(Request $request){
        //dd($request->all());
        $this->validate($request, [
            'product_sale_id'=> 'required',
            'party_id'=> 'required',
            'store_id'=> 'required',
            'paid_amount'=> 'required',
            'due_amount'=> 'required',
            'total_amount'=> 'required',
            'payment_type'=> 'required',
        ]);

        $user_id = Auth::user()->id;
        $store_id = $request->store_id;
        $warehouse_id = Store::where('id',$store_id)->pluck('warehouse_id')->first();


        // product purchase
        $productSale = ProductSale::find($request->product_sale_id);
        $productSale->user_id = $user_id;
        $productSale->party_id = $request->party_id;
        $productSale->warehouse_id = $warehouse_id;
        $productSale->store_id = $store_id;
        $productSale->discount_type = $request->discount_type ? $request->discount_type : NULL;
        $productSale->discount_amount = $request->discount_amount ? $request->discount_amount : 0;
        $productSale->miscellaneous_comment = $request->miscellaneous_comment ? $request->miscellaneous_comment : NULL;
        $productSale->miscellaneous_charge = $request->miscellaneous_charge ? $request->miscellaneous_charge : 0;
        $productSale->paid_amount = $request->paid_amount;
        $productSale->due_amount = $request->due_amount;
        $productSale->total_vat_amount = $request->total_vat_amount;
        $productSale->total_amount = $request->total_amount;
        $productSale->update();
        $affectedRows = $productSale->id;
        if($affectedRows)
        {
            foreach ($request->products as $data) {
                $product_id = $data['product_id'];
                $barcode = Product::where('id',$product_id)->pluck('barcode')->first();

                $product_sale_detail_id = $data['product_sale_detail_id'];
                // product purchase detail
                $product_sale_detail = ProductSaleDetail::find($product_sale_detail_id);
                $product_sale_detail->product_unit_id = $data['product_unit_id'];
                $product_sale_detail->product_brand_id = $data['product_brand_id'] ? $data['product_brand_id'] : NULL;
                $product_sale_detail->product_id = $product_id;
                $product_sale_detail->qty = $data['qty'];
                $product_sale_detail->vat_amount = $data['vat_amount'];
                $product_sale_detail->price = $data['mrp_price'];
                $product_sale_detail->sub_total = ($data['qty']*$data['mrp_price']) + ($data['qty']*$data['vat_amount']);
                $product_sale_detail->barcode = $barcode;
                $product_sale_detail->update();


                // product stock
                $stock_row = Stock::where('ref_id',$request->product_sale_id)->where('stock_type','whole_sale')->where('product_id',$product_id)->first();

                if($stock_row->stock_out != $data['qty']){

                    if($data['qty'] > $stock_row->stock_out){
                        $add_or_minus_stock_out = $data['qty'] + $stock_row->stock_out;
                        $update_stock_out = $stock_row->stock_out - $add_or_minus_stock_out;
                        $update_current_stock = $stock_row->current_stock - $add_or_minus_stock_out;
                    }else{
                        $add_or_minus_stock_out =  $stock_row->stock_out + $data['qty'];
                        $update_stock_out = $stock_row->stock_out + $add_or_minus_stock_out;
                        $update_current_stock = $stock_row->current_stock + $add_or_minus_stock_out;
                    }

                    $stock_row->user_id = $user_id;
                    $stock_row->stock_out = $update_stock_out;
                    $stock_row->current_stock = $update_current_stock;
                    $stock_row->update();
                }
            }

            // transaction
            $transaction = Transaction::where('ref_id',$request->product_sale_id)->first();
            $transaction->user_id = $user_id;
            $transaction->warehouse_id = $warehouse_id;
            $transaction->store_id = $store_id;
            $transaction->party_id = $request->party_id;
            $transaction->payment_type = $request->payment_type;
            $transaction->amount = $request->paid_amount;
            $transaction->update();

            // payment paid
            $payment_collection = PaymentCollection::where('product_sale_id',$request->product_sale_id)->first();
            $payment_collection->user_id = $user_id;
            $payment_collection->party_id = $request->party_id;
            $payment_collection->warehouse_id = $warehouse_id;
            $payment_collection->store_id = $store_id;
            $payment_collection->collection_amount = $request->paid_amount;
            $payment_collection->due_amount = $request->due_amount;
            $payment_collection->current_collection_amount = $request->paid_amount;
            $payment_collection->update();


            return response()->json(['success'=>true,'response' => 'Updated Successfully.'], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Updated Successfully!'], $this->failStatus);
        }
    }

    public function productWholeSaleDelete(Request $request){
        $check_exists_product_sale = DB::table("product_sales")->where('id',$request->product_sale_id)->pluck('id')->first();
        if($check_exists_product_sale == null){
            return response()->json(['success'=>false,'response'=>'No Product Sale Found!'], $this->failStatus);
        }

        $productSale = ProductSale::find($request->product_sale_id);
        $delete_sale = $productSale->delete();

        DB::table('product_sale_details')->where('product_sale_id',$request->product_sale_id)->delete();
        DB::table('stocks')->where('ref_id',$request->product_sale_id)->delete();
        DB::table('transactions')->where('ref_id',$request->product_sale_id)->delete();
        DB::table('payment_collections')->where('product_sale_id',$request->product_sale_id)->delete();

        if($delete_sale)
        {
            return response()->json(['success'=>true,'response' =>'Sale Successfully Deleted!'], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'Sale Not Deleted!'], $this->failStatus);
        }
    }







    public function productPOSSaleList(){
        $product_pos_sales = DB::table('product_sales')
            ->leftJoin('users','product_sales.user_id','users.id')
            ->leftJoin('parties','product_sales.party_id','parties.id')
            ->leftJoin('warehouses','product_sales.warehouse_id','warehouses.id')
            ->leftJoin('stores','product_sales.store_id','stores.id')
            ->where('product_sales.sale_type','pos_sale')
            ->select('product_sales.id','product_sales.invoice_no','product_sales.discount_type','product_sales.discount_amount','product_sales.total_vat_amount','product_sales.total_amount','product_sales.paid_amount','product_sales.due_amount','product_sales.sale_date_time','users.name as user_name','parties.id as customer_id','parties.name as customer_name','warehouses.id as warehouse_id','warehouses.name as warehouse_name','stores.id as store_id','stores.name as store_name','stores.address as store_address')
            ->orderBy('product_sales.id','desc')
            ->get();

        if(count($product_pos_sales) > 0)
        {
            $product_pos_sale_arr = [];
            foreach ($product_pos_sales as $data){
                $payment_type = DB::table('transactions')->where('ref_id',$data->id)->where('transaction_type','pos_sale')->pluck('payment_type')->first();

                $nested_data['id']=$data->id;
                $nested_data['invoice_no']=$data->invoice_no;
                $nested_data['discount_type']=$data->discount_type;
                $nested_data['discount_amount']=$data->discount_amount;
                $nested_data['total_vat_amount']=$data->total_vat_amount;
                $nested_data['total_amount']=$data->total_amount;
                $nested_data['paid_amount']=$data->paid_amount;
                $nested_data['due_amount']=$data->due_amount;
                $nested_data['sale_date_time']=$data->sale_date_time;
                $nested_data['user_name']=$data->user_name;
                $nested_data['customer_id']=$data->customer_id;
                $nested_data['customer_name']=$data->customer_name;
                $nested_data['warehouse_id']=$data->warehouse_id;
                $nested_data['warehouse_name']=$data->warehouse_name;
                $nested_data['store_id']=$data->store_id;
                $nested_data['store_name']=$data->store_name;
                $nested_data['store_address']=$data->store_address;
                $nested_data['payment_type']=$payment_type;

                array_push($product_pos_sale_arr,$nested_data);
            }

            $success['product_pos_sales'] =  $product_pos_sale_arr;
            return response()->json(['success'=>true,'response' => $success], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Product Whole Sale List Found!'], $this->failStatus);
        }
    }

    public function productPOSSaleDetails(Request $request){
        $product_sale_details = DB::table('product_sales')
            ->join('product_sale_details','product_sales.id','product_sale_details.product_sale_id')
            ->leftJoin('products','product_sale_details.product_id','products.id')
            ->leftJoin('product_units','product_sale_details.product_unit_id','product_units.id')
            ->leftJoin('product_brands','product_sale_details.product_brand_id','product_brands.id')
            ->where('product_sales.id',$request->product_sale_id)
            ->select('products.id as product_id','products.name as product_name','product_units.id as product_unit_id','product_units.name as product_unit_name','product_brands.id as product_brand_id','product_brands.name as product_brand_name','product_sale_details.qty','product_sale_details.id as product_sale_detail_id','product_sale_details.price as mrp_price','product_sale_details.vat_amount')
            ->get();

        if($product_sale_details)
        {
            $success['product_pos_sale_details'] =  $product_sale_details;
            return response()->json(['success'=>true,'response' => $success], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Product POS Sale Detail Found!'], $this->failStatus);
        }
    }


    public function productPOSSaleCreate(Request $request){
        //dd($request->all());
        //return response()->json(['success'=>true,'response' => $request->all()], $this->successStatus);
        $this->validate($request, [
            'party_id'=> 'required',
            'store_id'=> 'required',
            'paid_amount'=> 'required',
            'due_amount'=> 'required',
            'total_amount'=> 'required',
            'payment_type'=> 'required',
        ]);

        $get_invoice_no = ProductSale::latest('id','desc')->pluck('invoice_no')->first();
        if(!empty($get_invoice_no)){
            $get_invoice = str_replace("sale-","",$get_invoice_no);
            $invoice_no = $get_invoice+1;
        }else{
            $invoice_no = 100000;
        }
        $final_invoice = 'sale-'.$invoice_no;

        $date = date('Y-m-d');
        $date_time = date('Y-m-d h:i:s');
        $add_two_day_date =  date('Y-m-d', strtotime("+2 days"));

        $user_id = Auth::user()->id;
        $store_id = $request->store_id;
        $warehouse_id = Store::where('id',$store_id)->pluck('warehouse_id')->first();

        // product purchase
        $productSale = new ProductSale();
        $productSale ->invoice_no = $final_invoice;
        $productSale ->user_id = $user_id;
        $productSale ->party_id = $request->party_id;
        $productSale ->warehouse_id = $warehouse_id;
        $productSale ->store_id = $store_id;
        $productSale ->sale_type = 'pos_sale';
        $productSale ->discount_type = $request->discount_type ? $request->discount_type : NULL;
        $productSale ->discount_amount = $request->discount_amount ? $request->discount_amount : 0;
        $productSale ->paid_amount = $request->paid_amount;
        $productSale ->due_amount = $request->due_amount;
        $productSale ->total_vat_amount = $request->total_vat_amount;
        $productSale ->total_amount = $request->total_amount;
        $productSale ->sale_date = $date;
        $productSale ->sale_date_time = $date_time;
        $productSale->save();
        $insert_id = $productSale->id;

        if($insert_id)
        {
            // for postman testing
//            for($i=0; $i<$row_count;$i++)
//            {
//                $product_id = $request['products']['product_id'][$i];
//
//                $barcode = Product::where('id',$product_id)->pluck('barcode')->first();
//
//                // product purchase detail
//                $purchase_purchase_detail = new ProductPurchaseDetail();
//                $purchase_purchase_detail->product_purchase_id = $insert_id;
//                $purchase_purchase_detail->product_unit_id = $request->product_unit_id[$i];
//                $purchase_purchase_detail->product_brand_id = $request->product_brand_id[$i] ? $request->product_brand_id[$i] : NULL;
//                $purchase_purchase_detail->product_id = $request->product_id[$i];
//                $purchase_purchase_detail->qty = $request->qty[$i];
//                $purchase_purchase_detail->price = $request->price[$i];
//                $purchase_purchase_detail->mrp_price = $request->mrp_price[$i];
//                $purchase_purchase_detail->sub_total = $request->qty[$i]*$request->price[$i];
//                $purchase_purchase_detail->barcode = $barcode;
//                $purchase_purchase_detail->save();
//
//                $check_previous_stock = Stock::where('product_id',$product_id)->latest()->pluck('current_stock')->first();
//                if(!empty($check_previous_stock)){
//                    $previous_stock = $check_previous_stock;
//                }else{
//                    $previous_stock = 0;
//                }
//
//                // product stock
//                $stock = new Stock();
//                $stock->ref_id = $insert_id;
//                $stock->user_id = $user_id;
//                $stock->warehouse_id = $request->warehouse_id;
//                $stock->product_id = $request->product_id[$i];
//                $stock->product_unit_id = $request->product_unit_id[$i];
//                $stock->product_brand_id = $request->product_brand_id[$i] ? $request->product_brand_id[$i] : NULL;
//                $stock->stock_type = 'whole_purchase';
//                $stock->previous_stock = $previous_stock;
//                $stock->stock_in = $request->qty[$i];
//                $stock->stock_out = 0;
//                $stock->current_stock = $previous_stock + $request->qty[$i];
//                $stock->stock_date = $date;
//                $stock->stock_date_time = $date_time;
//                $stock->save();
//            }

            // for live testing
            foreach ($request->products as $data) {

                $product_id =  $data['product_id'];

                $barcode = Product::where('id',$product_id)->pluck('barcode')->first();

                // product purchase detail
                $product_sale_detail = new ProductSaleDetail();
                $product_sale_detail->product_sale_id = $insert_id;
                $product_sale_detail->product_unit_id = $data['product_unit_id'];
                $product_sale_detail->product_brand_id = $data['product_brand_id'] ? $data['product_brand_id'] : NULL;
                $product_sale_detail->product_id = $product_id;
                $product_sale_detail->barcode = $barcode;
                $product_sale_detail->qty = $data['qty'];
                $product_sale_detail->price = $data['mrp_price'];
                $product_sale_detail->vat_amount = $data['vat_amount'];
                $product_sale_detail->sub_total = ($data['qty']*$data['mrp_price']) + ($data['qty']*$data['vat_amount']);
                $product_sale_detail->sale_date = $date;
                $product_sale_detail->return_among_day = 2;
                $product_sale_detail->return_last_date = $add_two_day_date;
                $product_sale_detail->save();

                $check_previous_stock = Stock::where('warehouse_id',$warehouse_id)->where('store_id',$store_id)->where('stock_where','store')->where('product_id',$product_id)->latest()->pluck('current_stock')->first();
                if(!empty($check_previous_stock)){
                    $previous_stock = $check_previous_stock;
                }else{
                    $previous_stock = 0;
                }

                // product stock
                $stock = new Stock();
                $stock->ref_id = $insert_id;
                $stock->user_id = $user_id;
                $stock->warehouse_id = $warehouse_id;
                $stock->store_id = $store_id;
                $stock->product_id = $product_id;
                $stock->product_unit_id = $data['product_unit_id'];
                $stock->product_brand_id = $data['product_brand_id'] ? $data['product_brand_id'] : NULL;
                $stock->stock_type = 'pos_sale';
                $stock->stock_where = 'store';
                $stock->stock_in_out = 'stock_out';
                $stock->previous_stock = $previous_stock;
                $stock->stock_in = 0;
                $stock->stock_out = $data['qty'];
                $stock->current_stock = $previous_stock - $data['qty'];
                $stock->stock_date = $date;
                $stock->stock_date_time = $date_time;
                $stock->save();

                // warehouse store current stock
                $update_warehouse_store_current_stock = WarehouseStoreCurrentStock::where('warehouse_id',$warehouse_id)
                    ->where('store_id',$store_id)
                    ->where('product_id',$product_id)
                    ->first();

                $exists_current_stock = $update_warehouse_store_current_stock->current_stock;
                $final_warehouse_current_stock = $exists_current_stock - $data['qty'];
                $update_warehouse_store_current_stock->current_stock=$final_warehouse_current_stock;
                $update_warehouse_store_current_stock->save();
            }

            // transaction
            $transaction = new Transaction();
            $transaction->ref_id = $insert_id;
            $transaction->invoice_no = $final_invoice;
            $transaction->user_id = $user_id;
            $transaction->warehouse_id = $warehouse_id;
            $transaction->store_id = $store_id;
            $transaction->party_id = $request->party_id;
            $transaction->transaction_type = 'pos_sale';
            $transaction->payment_type = $request->payment_type;
            $transaction->amount = $request->paid_amount;
            $transaction->transaction_date = $date;
            $transaction->transaction_date_time = $date_time;
            $transaction->save();
            $transaction_id = $transaction->id;

            // payment paid
            $payment_collection = new PaymentCollection();
            $payment_collection->invoice_no = $final_invoice;
            $payment_collection->product_sale_id = $insert_id;
            $payment_collection->user_id = $user_id;
            $payment_collection->party_id = $request->party_id;
            $payment_collection->warehouse_id = $warehouse_id;
            $payment_collection->store_id = $store_id;
            $payment_collection->collection_type = 'Sale';
            $payment_collection->collection_amount = $request->paid_amount;
            $payment_collection->due_amount = $request->due_amount;
            $payment_collection->current_collection_amount = $request->paid_amount;
            $payment_collection->collection_date = $date;
            $payment_collection->collection_date_time = $date_time;
            $payment_collection->save();

            if($request->payment_type == 'SSL Commerz'){
                $product_pos_sale = DB::table('product_sales')
                    ->leftJoin('users','product_sales.user_id','users.id')
                    ->leftJoin('parties','product_sales.party_id','parties.id')
                    ->leftJoin('warehouses','product_sales.warehouse_id','warehouses.id')
                    ->leftJoin('stores','product_sales.store_id','stores.id')
                    ->where('product_sales.sale_type','pos_sale')
                    ->where('product_sales.id',$insert_id)
                    ->select('product_sales.id','product_sales.invoice_no','product_sales.discount_type','product_sales.discount_amount','product_sales.total_vat_amount','product_sales.total_amount','product_sales.paid_amount','product_sales.due_amount','product_sales.sale_date_time','users.name as user_name','parties.id as customer_id','parties.name as customer_name','warehouses.id as warehouse_id','warehouses.name as warehouse_name','stores.id as store_id','stores.name as store_name','stores.address as store_address')
                    ->first();

                return response()->json(['success'=>true,'transaction_id' => $transaction_id,'payment_type' => $request->payment_type,'product_pos_sale' => $product_pos_sale], $this->successStatus);
            }else{

                $product_pos_sale = DB::table('product_sales')
                    ->leftJoin('users','product_sales.user_id','users.id')
                    ->leftJoin('parties','product_sales.party_id','parties.id')
                    ->leftJoin('warehouses','product_sales.warehouse_id','warehouses.id')
                    ->leftJoin('stores','product_sales.store_id','stores.id')
                    ->where('product_sales.sale_type','pos_sale')
                    ->where('product_sales.id',$insert_id)
                    ->select('product_sales.id','product_sales.invoice_no','product_sales.discount_type','product_sales.discount_amount','product_sales.total_vat_amount','product_sales.total_amount','product_sales.paid_amount','product_sales.due_amount','product_sales.sale_date_time','users.name as user_name','parties.id as customer_id','parties.name as customer_name','warehouses.id as warehouse_id','warehouses.name as warehouse_name','stores.id as store_id','stores.name as store_name','stores.address as store_address')
                    ->first();

                return response()->json(['success'=>true,'product_pos_sale' => $product_pos_sale], $this->successStatus);
            }
        }else{
            return response()->json(['success'=>false,'response'=>'No Inserted Successfully!'], $this->failStatus);
        }
    }

    public function productPOSSaleEdit(Request $request){
        //dd($request->all());
        $this->validate($request, [
            'product_sale_id'=> 'required',
            'party_id'=> 'required',
            'store_id'=> 'required',
            'paid_amount'=> 'required',
            'due_amount'=> 'required',
            'total_amount'=> 'required',
            'payment_type'=> 'required',
        ]);

        $user_id = Auth::user()->id;
        $store_id = $request->store_id;
        $warehouse_id = Store::where('id',$store_id)->pluck('warehouse_id')->first();


        // product purchase
        $productSale = ProductSale::find($request->product_sale_id);
        $productSale ->user_id = $user_id;
        $productSale ->party_id = $request->party_id;
        $productSale ->warehouse_id = $warehouse_id;
        $productSale ->store_id = $store_id;
        $productSale ->discount_type = $request->discount_type ? $request->discount_type : NULL;
        $productSale ->discount_amount = $request->discount_amount ? $request->discount_amount : 0;
        $productSale ->paid_amount = $request->paid_amount;
        $productSale ->due_amount = $request->due_amount;
        $productSale ->total_vat_amount = $request->total_vat_amount;
        $productSale ->total_amount = $request->total_amount;
        $productSale->update();
        $affectedRows = $productSale->id;
        if($affectedRows)
        {
            foreach ($request->products as $data) {
                $product_id = $data['product_id'];
                $barcode = Product::where('id',$product_id)->pluck('barcode')->first();

                $product_sale_detail_id = $data['product_sale_detail_id'];
                // product purchase detail
                $purchase_sale_detail = ProductSaleDetail::find($product_sale_detail_id);
                $purchase_sale_detail->product_unit_id = $data['product_unit_id'];
                $purchase_sale_detail->product_brand_id = $data['product_brand_id'] ? $data['product_brand_id'] : NULL;
                $purchase_sale_detail->product_id = $product_id;
                $purchase_sale_detail->qty = $data['qty'];
                $purchase_sale_detail->price = $data['mrp_price'];
                $purchase_sale_detail->vat_amount = $data['vat_amount'];
                $purchase_sale_detail->sub_total = ($data['qty']*$data['mrp_price']) + ($data['qty']*$data['vat_amount']);
                $purchase_sale_detail->barcode = $barcode;
                $purchase_sale_detail->update();


                // product stock
                $stock_row = Stock::where('ref_id',$request->product_sale_id)->where('stock_type','pos_sale')->where('product_id',$product_id)->first();

                // warehouse store current stock
                $update_warehouse_store_current_stock = WarehouseStoreCurrentStock::where('warehouse_id',$warehouse_id)
                    ->where('store_id',$store_id)
                    ->where('product_id',$product_id)
                    ->first();
                $exists_current_stock = $update_warehouse_store_current_stock->current_stock;

                if($stock_row->stock_out != $data['qty']){

                    if($data['qty'] > $stock_row->stock_out){
                        $add_or_minus_stock_out = $data['qty'] + $stock_row->stock_out;
                        $update_stock_out = $stock_row->stock_out - $add_or_minus_stock_out;
                        $update_current_stock = $stock_row->current_stock - $add_or_minus_stock_out;

                        $final_warehouse_current_stock = $exists_current_stock - $data['qty'];
                    }else{
                        $add_or_minus_stock_out =  $stock_row->stock_out + $data['qty'];
                        $update_stock_out = $stock_row->stock_out + $add_or_minus_stock_out;
                        $update_current_stock = $stock_row->current_stock + $add_or_minus_stock_out;

                        $final_warehouse_current_stock = $exists_current_stock + $data['qty'];
                    }

                    $stock_row->user_id = $user_id;
                    $stock_row->stock_out = $update_stock_out;
                    $stock_row->current_stock = $update_current_stock;
                    $stock_row->update();

                    //warehouse store current stock
                    $update_warehouse_store_current_stock->current_stock=$final_warehouse_current_stock;
                    $update_warehouse_store_current_stock->save();
                }
            }

            // transaction
            $transaction = Transaction::where('ref_id',$request->product_sale_id)->first();
            $transaction->user_id = $user_id;
            $transaction->warehouse_id = $warehouse_id;
            $transaction->store_id = $store_id;
            $transaction->party_id = $request->party_id;
            $transaction->payment_type = $request->payment_type;
            $transaction->amount = $request->paid_amount;
            $transaction->update();

            // payment paid
            $payment_collection = PaymentCollection::where('product_sale_id',$request->product_sale_id)->first();
            $payment_collection->user_id = $user_id;
            $payment_collection->party_id = $request->party_id;
            $payment_collection->warehouse_id = $warehouse_id;
            $payment_collection->store_id = $store_id;
            $payment_collection->collection_amount = $request->paid_amount;
            $payment_collection->due_amount = $request->due_amount;
            $payment_collection->current_collection_amount = $request->paid_amount;
            $payment_collection->update();


            return response()->json(['success'=>true,'response' => 'Updated Successfully.'], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Updated Successfully!'], $this->failStatus);
        }
    }

    public function productPOSSaleDelete(Request $request){
        $check_exists_product_sale = DB::table("product_sales")->where('id',$request->product_sale_id)->pluck('id')->first();
        if($check_exists_product_sale == null){
            return response()->json(['success'=>false,'response'=>'No Product Sale Found!'], $this->failStatus);
        }

        $productSale = ProductSale::find($request->product_sale_id);
        $delete_sale = $productSale->delete();

        DB::table('product_sale_details')->where('product_sale_id',$request->product_sale_id)->delete();
        DB::table('stocks')->where('ref_id',$request->product_sale_id)->delete();
        DB::table('transactions')->where('ref_id',$request->product_sale_id)->delete();
        DB::table('payment_collections')->where('product_sale_id',$request->product_sale_id)->delete();

        if($delete_sale)
        {
            return response()->json(['success'=>true,'response' =>'Sale Successfully Deleted!'], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'Sale Not Deleted!'], $this->failStatus);
        }
    }

    // product sale invoice list
    public function productSaleInvoiceList(){
        $product_sale_invoices = DB::table('product_sales')
            ->select('id','invoice_no')
            ->get();

        if($product_sale_invoices)
        {
            $success['product_sale_invoices'] =  $product_sale_invoices;
            return response()->json(['success'=>true,'response' => $success], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Product Sale List Found!'], $this->failStatus);
        }
    }

    public function productSaleReturnDetails(Request $request){
        //dd($request->all());
        $this->validate($request, [
            'product_sale_invoice_no'=> 'required',
        ]);

        $product_sales = DB::table('product_sales')
            ->leftJoin('users','product_sales.user_id','users.id')
            ->leftJoin('parties','product_sales.party_id','parties.id')
            ->leftJoin('warehouses','product_sales.warehouse_id','warehouses.id')
            ->leftJoin('stores','product_sales.store_id','stores.id')
            ->where('product_sales.invoice_no',$request->product_sale_invoice_no)
            ->select('product_sales.id','product_sales.invoice_no','product_sales.discount_type','product_sales.discount_amount','product_sales.total_amount','product_sales.paid_amount','product_sales.due_amount','product_sales.sale_date_time','users.name as user_name','parties.id as customer_id','parties.name as customer_name','warehouses.id as warehouse_id','warehouses.name as warehouse_name','stores.id as store_id','stores.name as store_name')
            ->first();

        if($product_sales){

            $product_sale_details = DB::table('product_sales')
                ->join('product_sale_details','product_sales.id','product_sale_details.product_sale_id')
                ->leftJoin('products','product_sale_details.product_id','products.id')
                ->leftJoin('product_units','product_sale_details.product_unit_id','product_units.id')
                ->leftJoin('product_brands','product_sale_details.product_brand_id','product_brands.id')
                ->where('product_sales.invoice_no',$request->product_sale_invoice_no)
                ->select('products.id as product_id','products.name as product_name','product_units.id as product_unit_id','product_units.name as product_unit_name','product_brands.id as product_brand_id','product_brands.name as product_brand_name','product_sale_details.qty','product_sale_details.qty as current_qty','product_sale_details.id as product_sale_detail_id','product_sale_details.price as mrp_price','product_sale_details.sale_date','product_sale_details.return_among_day','product_sale_details.price as mrp_price')
                ->get();

            $success['product_sales'] = $product_sales;
            $success['product_sale_details'] = $product_sale_details;
            return response()->json(['success'=>true,'response' => $success], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Product Sale Data Found!'], $this->failStatus);
        }
    }

    public function productSaleReturnCreate(Request $request){
        //dd($request->all());
        $this->validate($request, [
            'party_id'=> 'required',
            'store_id'=> 'required',
            'paid_amount'=> 'required',
            'due_amount'=> 'required',
            'total_amount'=> 'required',
            'payment_type'=> 'required',
            'product_sale_invoice_no'=> 'required',
        ]);

        $product_sale_id = ProductSale::where('invoice_no',$request->product_sale_invoice_no)->pluck('id')->first();
        $get_invoice_no = ProductSaleReturn::latest('id','desc')->pluck('invoice_no')->first();
        if(!empty($get_invoice_no)){
            $get_invoice = str_replace("sale-return","",$get_invoice_no);
            $invoice_no = $get_invoice+1;
        }else{
            $invoice_no = 800000;
        }
        $final_invoice = 'sale-return'.$invoice_no;

        $date = date('Y-m-d');
        $date_time = date('Y-m-d h:i:s');

        $user_id = Auth::user()->id;
        $store_id = $request->store_id;
        $warehouse_id = Store::where('id',$store_id)->latest('id')->pluck('warehouse_id')->first();

        // product sale return
        $productSaleReturn = new ProductSaleReturn();
        $productSaleReturn ->invoice_no = $final_invoice;
        $productSaleReturn ->product_sale_invoice_no = $request->product_sale_invoice_no;
        $productSaleReturn ->user_id = $user_id;
        $productSaleReturn ->party_id = $request->party_id;
        $productSaleReturn ->warehouse_id = $warehouse_id;
        $productSaleReturn ->store_id = $store_id;
        $productSaleReturn ->product_sale_return_type = 'sale_return';
        $productSaleReturn ->discount_type = $request->discount_type ? $request->discount_type : NULL;
        $productSaleReturn ->discount_amount = $request->discount_amount ? $request->discount_amount : 0;
        $productSaleReturn ->paid_amount = $request->total_amount;
        $productSaleReturn ->due_amount = $request->due_amount;
        $productSaleReturn ->total_amount = $request->total_amount;
        $productSaleReturn ->product_sale_return_date = $date;
        $productSaleReturn ->product_sale_return_date_time = $date_time;
        $productSaleReturn->save();
        $insert_id = $productSaleReturn->id;

        if($insert_id)
        {
            // for live testing
            foreach ($request->products as $data) {

                $product_id =  $data['product_id'];

                $barcode = Product::where('id',$product_id)->pluck('barcode')->first();

                // product purchase detail
                $purchase_sale_return_detail = new ProductSaleReturnDetail();
                $purchase_sale_return_detail->pro_sale_return_id = $insert_id;
                $purchase_sale_return_detail->pro_sale_detail_id = $data['product_sale_detail_id'];
                $purchase_sale_return_detail->product_unit_id = $data['product_unit_id'];
                $purchase_sale_return_detail->product_brand_id = $data['product_brand_id'] ? $data['product_brand_id'] : NULL;
                $purchase_sale_return_detail->product_id = $product_id;
                $purchase_sale_return_detail->barcode = $barcode;
                $purchase_sale_return_detail->qty = $data['qty'];
                $purchase_sale_return_detail->price = $data['mrp_price'];
                $purchase_sale_return_detail->sub_total = $data['qty']*$data['mrp_price'];
                $purchase_sale_return_detail->save();

                $check_previous_stock = Stock::where('warehouse_id',$warehouse_id)->where('store_id',$store_id)->where('product_id',$product_id)->latest('id','desc')->pluck('current_stock')->first();
                if(!empty($check_previous_stock)){
                    $previous_stock = $check_previous_stock;
                }else{
                    $previous_stock = 0;
                }

                // product stock
                $stock = new Stock();
                $stock->ref_id = $insert_id;
                $stock->user_id = $user_id;
                $stock->warehouse_id = $warehouse_id;
                $stock->store_id = $store_id;
                $stock->product_id = $product_id;
                $stock->product_unit_id = $data['product_unit_id'];
                $stock->product_brand_id = $data['product_brand_id'] ? $data['product_brand_id'] : NULL;
                $stock->stock_type = 'sale_return';
                $stock->stock_where = 'store';
                $stock->stock_in_out = 'stock_in';
                $stock->previous_stock = $previous_stock;
                $stock->stock_in = $data['qty'];
                $stock->stock_out = 0;
                $stock->current_stock = $previous_stock + $data['qty'];
                $stock->stock_date = $date;
                $stock->stock_date_time = $date_time;
                $stock->save();



                // warehouse store current stock
                $update_warehouse_store_current_stock = WarehouseStoreCurrentStock::where('warehouse_id',$warehouse_id)
                    ->where('store_id',$store_id)
                    ->where('product_id',$product_id)
                    ->first();

                $exists_current_stock = $update_warehouse_store_current_stock->current_stock;
                $final_warehouse_current_stock = $exists_current_stock + $data['qty'];
                $update_warehouse_store_current_stock->current_stock=$final_warehouse_current_stock;
                $update_warehouse_store_current_stock->save();



                $check_return_last_date = ProductSaleDetail::where('id',$data['product_sale_detail_id'])->pluck('return_last_date')->first();
                $today_date = date('Y-m-d');
                if($check_return_last_date >= $today_date){
                    // for sale return cash
                    // transaction
                    $transaction = new Transaction();
                    $transaction->ref_id = $insert_id;
                    $transaction->invoice_no = $final_invoice;
                    $transaction->user_id = $user_id;
                    $transaction->warehouse_id = $warehouse_id;
                    $transaction->store_id = $store_id;
                    $transaction->party_id = $request->party_id;
                    $transaction->transaction_type = 'sale_return_cash';
                    $transaction->payment_type = $request->payment_type;
                    $transaction->amount = $data['qty']*$data['mrp_price'];
                    $transaction->transaction_date = $date;
                    $transaction->transaction_date_time = $date_time;
                    $transaction->save();

                    // payment paid
                    $payment_collection = new PaymentCollection();
                    $payment_collection->invoice_no = $final_invoice;
                    $payment_collection->product_sale_id = $product_sale_id;
                    $payment_collection->product_sale_return_id = $insert_id;
                    $payment_collection->user_id = $user_id;
                    $payment_collection->party_id = $request->party_id;
                    $payment_collection->warehouse_id = $request->warehouse_id;
                    $payment_collection->store_id = $request->store_id;
                    $payment_collection->collection_type = 'Return Cash';
                    $payment_collection->collection_amount = $data['qty']*$data['mrp_price'];
                    $payment_collection->due_amount = 0;
                    $payment_collection->current_collection_amount = $data['qty']*$data['mrp_price'];
                    $payment_collection->collection_date = $date;
                    $payment_collection->collection_date_time = $date_time;
                    $payment_collection->save();
                }else{
                    // for sale return balance
                    // transaction
                    $transaction = new Transaction();
                    $transaction->ref_id = $insert_id;
                    $transaction->invoice_no = $final_invoice;
                    $transaction->user_id = $user_id;
                    $transaction->warehouse_id = $warehouse_id;
                    $transaction->store_id = $store_id;
                    $transaction->party_id = $request->party_id;
                    $transaction->transaction_type = 'sale_return_balance';
                    $transaction->payment_type = $request->payment_type;
                    $transaction->amount = $data['qty']*$data['mrp_price'];
                    $transaction->transaction_date = $date;
                    $transaction->transaction_date_time = $date_time;
                    $transaction->save();

                    // payment paid
                    $payment_collection = new PaymentCollection();
                    $payment_collection->invoice_no = $final_invoice;
                    $payment_collection->product_sale_id = $product_sale_id;
                    $payment_collection->product_sale_return_id = $insert_id;
                    $payment_collection->user_id = $user_id;
                    $payment_collection->party_id = $request->party_id;
                    $payment_collection->warehouse_id = $request->warehouse_id;
                    $payment_collection->store_id = $request->store_id;
                    $payment_collection->collection_type = 'Return Balance';
                    $payment_collection->collection_amount = $data['qty']*$data['mrp_price'];
                    $payment_collection->due_amount = 0;
                    $payment_collection->current_collection_amount = $data['qty']*$data['mrp_price'];
                    $payment_collection->collection_date = $date;
                    $payment_collection->collection_date_time = $date_time;
                    $payment_collection->save();

                    // add balance
                    $party_previous_virtual_balance = Party::where('id',$request->party_id)->pluck('virtual_balance')->first();

                    $party = Party::find($request->party_id);
                    $party->virtual_balance = $party_previous_virtual_balance + ($data['qty']*$data['mrp_price']);
                    $party->update();

                }

            }




            return response()->json(['success'=>true,'response' => 'Inserted Successfully.'], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Inserted Successfully!'], $this->failStatus);
        }
    }

    public function productImage(Request $request)
    {
        $product=Product::find($request->product_id);
        $base64_image_propic = $request->pro_img;
        //return response()->json(['response' => $base64_image_propic], $this-> successStatus);

        $data = $request->pro_img;
        $pos = strpos($data, ';');
        $type = explode(':', substr($data, 0, $pos))[1];
        $type1 = explode('/', $type)[1];

        if (preg_match('/^data:image\/(\w+);base64,/', $base64_image_propic)) {
            $data = substr($base64_image_propic, strpos($base64_image_propic, ',') + 1);
            $data = base64_decode($data);

            $currentDate = Carbon::now()->toDateString();
            $imagename = $currentDate . '-' . uniqid() . 'product_pic.'.$type1 ;

            // delete old image.....
            if(Storage::disk('public')->exists('uploads/products/'.$product->image))
            {
                Storage::disk('public')->delete('uploads/products/'.$product->image);

            }

            // resize image for service category and upload
            //$data = Image::make($data)->resize(100, 100)->save($data->getClientOriginalExtension());

            // store image
            Storage::disk('public')->put("uploads/products/". $imagename, $data);


            // update image db
            $product->image = $imagename;
            $product->update();

            $success['product'] = $product;
            return response()->json(['response' => $success], $this-> successStatus);

        }else{
            return response()->json(['response'=>'failed'], $this-> failStatus);
        }

    }

    // warehouse product damage
    public function warehouseProductDamageList(){
        $warehouse_product_damages = DB::table('warehouse_product_damages')
            ->leftJoin('users','warehouse_product_damages.user_id','users.id')
            ->leftJoin('warehouses','warehouse_product_damages.warehouse_id','warehouses.id')
            ->leftJoin('products','warehouse_product_damages.product_id','products.id')
            ->leftJoin('product_units','warehouse_product_damages.product_unit_id','product_units.id')
            ->leftJoin('product_brands','warehouse_product_damages.product_brand_id','product_brands.id')
            ->select(
                'warehouse_product_damages.id',
                'warehouse_product_damages.invoice_no',
                'warehouse_product_damages.product_id',
                'products.name as product_name',
                'warehouse_product_damages.barcode',
                'warehouse_product_damages.qty',
                'warehouse_product_damages.damage_date',
                'warehouse_product_damages.damage_date_time',
                'users.name as user_name',
                'warehouses.id as warehouse_id',
                'warehouses.name as warehouse_name',
                'warehouse_product_damages.product_unit_id',
                'product_units.name as product_unit_name',
                'warehouse_product_damages.product_brand_id',
                'product_brands.name as product_brand_name'
            )
            ->orderBy('warehouse_product_damages.id','desc')
            ->get();

        if(count($warehouse_product_damages) > 0)
        {
            $warehouse_product_damage_arr = [];
            foreach ($warehouse_product_damages as $data){
                $payment_type = DB::table('transactions')->where('ref_id',$data->id)->where('transaction_type','whole_sale')->pluck('payment_type')->first();

                $nested_data['id']=$data->id;
                $nested_data['invoice_no']=$data->invoice_no;
                $nested_data['product_id']=$data->product_id;
                $nested_data['product_name']=$data->product_name;
                $nested_data['barcode']=$data->barcode;
                $nested_data['qty']=$data->qty;
                $nested_data['damage_date']=$data->damage_date;
                $nested_data['damage_date_time']=$data->damage_date_time;
                $nested_data['user_name']=$data->user_name;
                $nested_data['warehouse_id']=$data->warehouse_id;
                $nested_data['warehouse_name']=$data->warehouse_name;
                $nested_data['product_unit_id']=$data->product_unit_id;
                $nested_data['product_unit_id']=$data->product_unit_id;
                $nested_data['unit_name']=$data->unit_name;
                $nested_data['product_brand_id']=$data->product_brand_id;
                $nested_data['product_brand_name']=$data->product_brand_name;

                array_push($warehouse_product_damage_arr,$nested_data);
            }

            $success['warehouse_product_damages'] =  $warehouse_product_damage_arr;
            return response()->json(['success'=>true,'response' => $success], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Warehouse Damage Product List Found!'], $this->failStatus);
        }
    }

    public function warehouseProductDamageCreate(Request $request){
        //dd($request->all());
        $this->validate($request, [
            'warehouse_id'=> 'required',
        ]);

        $user_id = Auth::user()->id;
        $warehouse_id = $request->warehouse_id;
        $date = date('Y-m-d');
        $date_time = date('Y-m-d H:i:s');


        $flag = true;
        foreach ($request->products as $data) {
            $product_id = $data['product_id'];
            $barcode = Product::where('id',$product_id)->pluck('barcode')->first();


            // warehouse damage product
            $warehouse_product_damage = new WarehouseProductDamage();
            $warehouse_product_damage->invoice_no = '';
            $warehouse_product_damage->user_id = $user_id;
            $warehouse_product_damage->warehouse_id = $warehouse_id;
            $warehouse_product_damage->product_unit_id = $data['product_unit_id'];
            $warehouse_product_damage->product_brand_id = $data['product_brand_id'] ? $data['product_brand_id'] : NULL;
            $warehouse_product_damage->product_id = $product_id;
            $warehouse_product_damage->qty = $data['qty'];
            $warehouse_product_damage->barcode = $barcode;
            $warehouse_product_damage->damage_date = $date;
            $warehouse_product_damage->damage_date_time = $date_time;
            $warehouse_product_damage->save();
            $insert_id=$warehouse_product_damage->id;


            // product stock
            $stock_row = Stock::where('stock_type','warehouse')->where('warehouse_id',$warehouse_id)->where('product_id',$product_id)->latest('id')->first();

            $stock = new Stock();
            $stock->ref_id=$insert_id;
            $stock->user_id=$user_id;
            $stock->product_unit_id= $data['product_unit_id'];
            $stock->product_brand_id=$data['product_brand_id'] ? $data['product_brand_id'] : NULL;
            $stock->product_id=$product_id;
            $stock->stock_type='warehouse_product_damage';
            $stock->warehouse_id=$warehouse_id;
            $stock->store_id=NULL;
            $stock->stock_where='warehouse';
            $stock->stock_in_out='stock_out';
            $stock->previous_stock=$stock_row->current_stock;
            $stock->stock_in=0;
            $stock->stock_out=$data['qty'];
            $stock->current_stock=$stock_row->current_stock - $data['qty'];
            $stock->stock_date=$date;
            $stock->stock_date_time=$date_time;
            $stock->save();


            $warehouse_current_stock = WarehouseCurrentStock::where('warehouse_id',$warehouse_id)->where('product_id',$product_id)->first();
            $exists_current_stock = $warehouse_current_stock->current_stock;
            $warehouse_current_stock->current_stock=$exists_current_stock - $data['qty'];
            $warehouse_current_stock->update();
        }
        if($flag == true){
            return response()->json(['success'=>true,'response' => 'Updated Successfully.'], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Updated Successfully!'], $this->failStatus);
        }
    }

    public function transactionHistory(Request $request){
        $transaction_history = DB::table('transactions')
            ->leftJoin('users','transactions.user_id','users.id')
            ->leftJoin('warehouses','transactions.warehouse_id','warehouses.id')
            ->leftJoin('stores','transactions.store_id','stores.id')
            ->leftJoin('parties','transactions.party_id','parties.id')
            ->select('transactions.id as transaction_id','users.name as transaction_by_user','warehouses.name as warehouse_name','stores.name as store_name','parties.name as party_name','transactions.transaction_type','transactions.payment_type','transactions.amount','transactions.transaction_date_time')
            ->latest('transactions.id','desc')
            ->get();

        if($transaction_history)
        {
            $success['transaction_history'] =  $transaction_history;
            return response()->json(['success'=>true,'response' => $success], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Transaction History Found!'], $this->failStatus);
        }
    }

    // report
    public function dateWiseSalesReport(Request $request){
        $validator = Validator::make($request->all(), [
            'from_date' => 'required',
            'to_date'=> 'required',
        ]);

        if ($validator->fails()) {
            $response = [
                'success' => false,
                'data' => 'Validation Error.',
                'message' => $validator->errors()
            ];

            return response()->json($response, $this-> validationStatus);
        }

        $from_date = $request->from_date ? $request->from_date : '';
        $to_date = $request->to_date ? $request->to_date : '';
        $sale_type = $request->sale_type ? $request->sale_type : '';


        if($sale_type != ''){
            $product_sales = ProductSale::where('sale_date','>=',$from_date)
                ->where('sale_date','<=',$to_date)
                ->where('sale_type',$sale_type)
                ->get();
            $total_sale_history = DB::table('product_sales')
                ->where('sale_date','>=',$from_date)
                ->where('sale_date','<=',$to_date)
                ->where('sale_type',$sale_type)
                ->select(DB::raw('SUM(total_amount) as total_sale'))
                ->first();
            $grand_total_amount = $total_sale_history->total_sale;
        }else{
            $product_sales = ProductSale::where('sale_date','>=',$from_date)
                ->where('sale_date','<=',$to_date)
                ->get();

            $total_sale_history = DB::table('product_sales')
                ->where('sale_date','>=',$from_date)
                ->where('sale_date','<=',$to_date)
                ->select(DB::raw('SUM(total_amount) as total_sale'))
                ->first();
            $grand_total_amount = $total_sale_history->total_sale;
        }

        if($product_sales)
        {
            return response()->json(['success'=>true,'response' => $product_sales,'grand_total_amount'=>$grand_total_amount], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>null], $this->successStatus);
        }
    }

    public function todayPurchase(Request $request){
        $today_purchase_history = DB::table('product_purchases')
            ->where('purchase_date', date('Y-m-d'))
            ->select(DB::raw('SUM(total_amount) as today_purchase'))
            ->first();

        if($today_purchase_history)
        {
            return response()->json(['success'=>true,'response' => $today_purchase_history->today_purchase], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>null], $this->successStatus);
        }
    }

    public function totalPurchase(Request $request){
        $total_purchase_history = DB::table('product_purchases')
            ->select(DB::raw('SUM(total_amount) as total_purchase'))
            ->first();

        if($total_purchase_history)
        {
            return response()->json(['success'=>true,'response' => $total_purchase_history->total_purchase], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>null], $this->successStatus);
        }
    }

    public function todayPurchaseReturn(Request $request){
        $today_purchase_return_history = DB::table('product_purchase_returns')
            ->where('product_purchase_return_date', date('Y-m-d'))
            ->select(DB::raw('SUM(total_amount) as today_purchase_return'))
            ->first();

        if($today_purchase_return_history)
        {
            return response()->json(['success'=>true,'response' => $today_purchase_return_history->today_purchase_return], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>null], $this->successStatus);
        }
    }

    public function totalPurchaseReturn(Request $request){
        $total_purchase_return_history = DB::table('product_purchase_returns')
            ->select(DB::raw('SUM(total_amount) as total_purchase_return'))
            ->first();

        if($total_purchase_return_history)
        {
            return response()->json(['success'=>true,'response' => $total_purchase_return_history->total_purchase_return], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>null], $this->successStatus);
        }
    }

    public function todaySale(Request $request){
        $today_sale_history = DB::table('product_sales')
            ->where('sale_date', date('Y-m-d'))
            ->select(DB::raw('SUM(total_amount) as today_sale'))
            ->first();

        if($today_sale_history)
        {
            return response()->json(['success'=>true,'response' => $today_sale_history->today_sale], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>null], $this->successStatus);
        }
    }

    public function totalSale(Request $request){
        $total_sale_history = DB::table('product_sales')
            ->select(DB::raw('SUM(total_amount) as total_sale'))
            ->first();

        if($total_sale_history)
        {
            return response()->json(['success'=>true,'response' => $total_sale_history->total_sale], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>null], $this->successStatus);
        }
    }

    public function todaySaleReturn(Request $request){
        $today_sale_return_history = DB::table('product_sale_returns')
            ->where('product_sale_return_date', date('Y-m-d'))
            ->select(DB::raw('SUM(total_amount) as today_sale_return'))
            ->first();

        if($today_sale_return_history)
        {
            return response()->json(['success'=>true,'response' => $today_sale_return_history->today_sale_return], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>null], $this->successStatus);
        }
    }

    public function totalSaleReturn(Request $request){
        $total_sale_return_history = DB::table('product_sale_returns')
            ->select(DB::raw('SUM(total_amount) as total_sale_return'))
            ->first();

        if($total_sale_return_history)
        {
            return response()->json(['success'=>true,'response' => $total_sale_return_history->total_sale_return], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>null], $this->successStatus);
        }
    }

    public function todayProfit(){
        $sum_purchase_price = 0;
        $sum_sale_price = 0;
        $sum_purchase_return_price = 0;
        $sum_sale_return_price = 0;
        $sum_profit_or_loss_amount = 0;

        $productPurchaseDetails = DB::table('product_purchase_details')
            ->select('product_id','product_unit_id','product_brand_id', DB::raw('SUM(qty) as qty'), DB::raw('SUM(price) as price'), DB::raw('SUM(mrp_price) as mrp_price'), DB::raw('SUM(sub_total) as sub_total'))
            ->groupBy('product_id')
            ->groupBy('product_unit_id')
            ->groupBy('product_brand_id')
            ->get();

        if(!empty($productPurchaseDetails)){
            foreach($productPurchaseDetails as $key => $productPurchaseDetail){
                $purchase_average_price = $productPurchaseDetail->sub_total/$productPurchaseDetail->qty;
                $sum_purchase_price += $productPurchaseDetail->sub_total;


                // purchase return
                $productPurchaseReturnDetails = DB::table('product_purchase_return_details')
                    ->join('product_purchase_returns','product_purchase_return_details.pro_pur_return_id','=','product_purchase_returns.id')
                    ->select('product_purchase_return_details.product_id','product_purchase_return_details.product_unit_id','product_purchase_return_details.product_brand_id', DB::raw('SUM(qty) as qty'), DB::raw('SUM(price) as price'))
                    ->where('product_purchase_return_details.product_id',$productPurchaseDetail->product_id)
                    ->where('product_purchase_return_details.product_unit_id',$productPurchaseDetail->product_unit_id)
                    ->where('product_purchase_return_details.product_brand_id',$productPurchaseDetail->product_brand_id)
                    ->where('product_purchase_returns.product_purchase_return_date',date('Y-m-d'))
                    ->groupBy('product_purchase_return_details.product_id')
                    ->groupBy('product_purchase_return_details.product_unit_id')
                    ->groupBy('product_purchase_return_details.product_brand_id')
                    ->first();

                if(!empty($productPurchaseReturnDetails))
                {
                    $purchase_return_total_qty = $productPurchaseReturnDetails->qty;
                    $purchase_return_total_amount = $productPurchaseReturnDetails->price;
                    $sum_purchase_return_price += $productPurchaseReturnDetails->price;
                    $purchase_return_average_price = $purchase_return_total_amount/$productPurchaseReturnDetails->qty;

                    if($purchase_return_total_qty > 0){
                        $purchase_return_amount = $purchase_return_average_price - ($purchase_average_price*$purchase_return_total_qty);
                        if($purchase_return_amount > 0){
                            $sum_profit_or_loss_amount += $purchase_return_amount;
                        }else{
                            $sum_profit_or_loss_amount -= $purchase_return_amount;
                        }
                    }
                }

                // sale
                $productSaleDetails = DB::table('product_sale_details')
                    ->select('product_id','product_unit_id','product_brand_id', DB::raw('SUM(qty) as qty'), DB::raw('SUM(price) as price'), DB::raw('SUM(sub_total) as sub_total'))
                    ->where('product_id',$productPurchaseDetail->product_id)
                    ->where('product_unit_id',$productPurchaseDetail->product_unit_id)
                    ->where('product_brand_id',$productPurchaseDetail->product_brand_id)
                    ->where('sale_date',date('Y-m-d'))
                    ->groupBy('product_id')
                    ->groupBy('product_unit_id')
                    ->groupBy('product_brand_id')
                    ->first();

                if(!empty($productSaleDetails))
                {
                    $sale_total_qty = $productSaleDetails->qty;
                    $sum_sale_price += $productSaleDetails->sub_total;
                    $sale_average_price = $productSaleDetails->sub_total/ (int) $productSaleDetails->qty;

                    if($sale_total_qty > 0){
                        $sale_amount = ($sale_average_price*$sale_total_qty) - ($purchase_average_price*$sale_total_qty);
                        if($sale_amount > 0){
                            $sum_profit_or_loss_amount += $sale_amount;
                        }else{
                            $sum_profit_or_loss_amount -= $sale_amount;
                        }

                    }
                }

                // sale return
                $productSaleReturnDetails = DB::table('product_sale_return_details')
                    ->join('product_sale_returns','product_sale_return_details.pro_sale_return_id','=','product_sale_returns.id')
                    ->select('product_sale_return_details.product_id','product_sale_return_details.product_unit_id','product_sale_return_details.product_brand_id', DB::raw('SUM(qty) as qty'), DB::raw('SUM(price) as price'))
                    ->where('product_sale_return_details.product_id',$productPurchaseDetail->product_id)
                    ->where('product_sale_return_details.product_unit_id',$productPurchaseDetail->product_unit_id)
                    ->where('product_sale_return_details.product_brand_id',$productPurchaseDetail->product_brand_id)
                    ->where('product_sale_returns.product_sale_return_date',date('Y-m-d'))
                    ->groupBy('product_sale_return_details.product_id')
                    ->groupBy('product_sale_return_details.product_unit_id')
                    ->groupBy('product_sale_return_details.product_brand_id')
                    ->first();

                if(!empty($productSaleReturnDetails))
                {
                    $sale_return_total_qty = $productSaleReturnDetails->qty;
                    $sale_return_total_amount = $productSaleReturnDetails->price;
                    $sum_sale_return_price += $productSaleReturnDetails->price;
                    $sale_return_average_price = $sale_return_total_amount/$productSaleReturnDetails->qty;

                    if($sale_return_total_qty > 0){
                        $sale_return_amount = $sale_return_average_price - ($purchase_average_price*$sale_return_total_qty);
                        if($sale_return_amount > 0){
                            $sum_profit_or_loss_amount -= $sale_return_amount;
                        }else{
                            $sum_profit_or_loss_amount += $sale_return_amount;
                        }
                    }
                }
            }
        }

        $productSaleDiscounts = DB::table('product_sales')
            ->select(DB::raw('SUM(discount_amount) as sum_discount'))
            ->where('sale_date',date('Y-m-d'))
            ->first();

        if(!empty($productSaleDiscounts))
        {
            $sum_discount = $productSaleDiscounts->sum_discount;
            if($sum_discount > 0){
                $sum_profit_or_loss_amount += $sum_discount;
            }else{
                $sum_profit_or_loss_amount -= $sum_discount;
            }
        }

        if($sum_profit_or_loss_amount)
        {
            return response()->json(['success'=>true,'response' => $sum_profit_or_loss_amount], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>null], $this->successStatus);
        }
    }

    public function totalProfit(){
        $sum_purchase_price = 0;
        $sum_sale_price = 0;
        $sum_purchase_return_price = 0;
        $sum_sale_return_price = 0;
        $sum_profit_or_loss_amount = 0;

        $productPurchaseDetails = DB::table('product_purchase_details')
            ->select('product_id','product_unit_id','product_brand_id', DB::raw('SUM(qty) as qty'), DB::raw('SUM(price) as price'), DB::raw('SUM(mrp_price) as mrp_price'), DB::raw('SUM(sub_total) as sub_total'))
            //->where('product_purchases.store_id',$store->id)
            //->where('product_purchases.ref_id',NULL)
            //->where('product_purchases.purchase_product_type','Finish Goods')
            ->groupBy('product_id')
            ->groupBy('product_unit_id')
            ->groupBy('product_brand_id')
            ->get();

        if(!empty($productPurchaseDetails)){
            foreach($productPurchaseDetails as $key => $productPurchaseDetail){
                $purchase_average_price = $productPurchaseDetail->sub_total/$productPurchaseDetail->qty;
                $sum_purchase_price += $productPurchaseDetail->sub_total;


                // purchase return
                $productPurchaseReturnDetails = DB::table('product_purchase_return_details')
                    ->select('product_id','product_unit_id','product_brand_id', DB::raw('SUM(qty) as qty'), DB::raw('SUM(price) as price'))
                    ->where('product_id',$productPurchaseDetail->product_id)
                    ->where('product_unit_id',$productPurchaseDetail->product_unit_id)
                    ->where('product_brand_id',$productPurchaseDetail->product_brand_id)
                    ->groupBy('product_id')
                    ->groupBy('product_unit_id')
                    ->groupBy('product_brand_id')
                    ->first();

                if(!empty($productPurchaseReturnDetails))
                {
                    $purchase_return_total_qty = $productPurchaseReturnDetails->qty;
                    $purchase_return_total_amount = $productPurchaseReturnDetails->price;
                    $sum_purchase_return_price += $productPurchaseReturnDetails->price;
                    $purchase_return_average_price = $purchase_return_total_amount/$productPurchaseReturnDetails->qty;

                    if($purchase_return_total_qty > 0){
                        $purchase_return_amount = $purchase_return_average_price - ($purchase_average_price*$purchase_return_total_qty);
                        if($purchase_return_amount > 0){
                            $sum_profit_or_loss_amount += $purchase_return_amount;
                        }else{
                            $sum_profit_or_loss_amount -= $purchase_return_amount;
                        }
                    }
                }

                // sale
                $productSaleDetails = DB::table('product_sale_details')
                    ->select('product_id','product_unit_id','product_brand_id', DB::raw('SUM(qty) as qty'), DB::raw('SUM(price) as price'), DB::raw('SUM(sub_total) as sub_total'))
                    ->where('product_id',$productPurchaseDetail->product_id)
                    ->where('product_unit_id',$productPurchaseDetail->product_unit_id)
                    ->where('product_brand_id',$productPurchaseDetail->product_brand_id)
                    ->groupBy('product_id')
                    ->groupBy('product_unit_id')
                    ->groupBy('product_brand_id')
                    ->first();

                if(!empty($productSaleDetails))
                {
                    $sale_total_qty = $productSaleDetails->qty;
                    $sum_sale_price += $productSaleDetails->sub_total;
                    $sale_average_price = $productSaleDetails->sub_total/ (int) $productSaleDetails->qty;

                    if($sale_total_qty > 0){
                        $sale_amount = ($sale_average_price*$sale_total_qty) - ($purchase_average_price*$sale_total_qty);
                        if($sale_amount > 0){
                            $sum_profit_or_loss_amount += $sale_amount;
                        }else{
                            $sum_profit_or_loss_amount -= $sale_amount;
                        }

                    }
                }

                // sale return
                $productSaleReturnDetails = DB::table('product_sale_return_details')
                    ->select('product_id','product_unit_id','product_brand_id', DB::raw('SUM(qty) as qty'), DB::raw('SUM(price) as price'))
                    ->where('product_id',$productPurchaseDetail->product_id)
                    ->where('product_unit_id',$productPurchaseDetail->product_unit_id)
                    ->where('product_brand_id',$productPurchaseDetail->product_brand_id)
                    ->groupBy('product_id')
                    ->groupBy('product_unit_id')
                    ->groupBy('product_brand_id')
                    ->first();

                if(!empty($productSaleReturnDetails))
                {
                    $sale_return_total_qty = $productSaleReturnDetails->qty;
                    $sale_return_total_amount = $productSaleReturnDetails->price;
                    $sum_sale_return_price += $productSaleReturnDetails->price;
                    $sale_return_average_price = $sale_return_total_amount/$productSaleReturnDetails->qty;

                    if($sale_return_total_qty > 0){
                        $sale_return_amount = $sale_return_average_price - ($purchase_average_price*$sale_return_total_qty);
                        if($sale_return_amount > 0){
                            $sum_profit_or_loss_amount -= $sale_return_amount;
                        }else{
                            $sum_profit_or_loss_amount += $sale_return_amount;
                        }
                    }
                }
            }
        }

        $productSaleDiscounts = DB::table('product_sales')
            ->select(DB::raw('SUM(discount_amount) as sum_discount'))
            ->first();

        if(!empty($productSaleDiscounts))
        {
            $sum_discount = $productSaleDiscounts->sum_discount;
            if($sum_discount > 0){
                $sum_profit_or_loss_amount += $sum_discount;
            }else{
                $sum_profit_or_loss_amount -= $sum_discount;
            }
        }

        if($sum_profit_or_loss_amount)
        {
            return response()->json(['success'=>true,'response' => $sum_profit_or_loss_amount], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Profit Or Loss History Found!'], $this->failStatus);
        }
    }




    // stock sync
    function product_store_stock_sync($warehouse_id,$store_id,$product_id){

        if($store_id){
            $stock_data = Stock::where('warehouse_id',$warehouse_id)->where('store_id',$store_id)->where('product_id',$product_id)->get();
        }else{
            $stock_data = Stock::where('warehouse_id',$warehouse_id)->where('store_id','=',NULL)->where('product_id',$product_id)->get();
        }

        $row_count = count($stock_data);
        if($row_count > 0) {
            $store_previous_row_current_stock = null;
            $stock_in_flag = 0;
            $stock_out_flag = 0;

            foreach ($stock_data as $key => $data) {

                $id = $data->id;
                $previous_stock = $data->previous_stock;
                $stock_in = $data->stock_in;
                $stock_out = $data->stock_out;
                $current_stock = $data->current_stock;

                if($key == 0) {
                    $stock = Stock::find($id);
                    $stock->previous_stock = 0;
                    $stock->current_stock = $stock_in;
                    $affectedRow = $stock->update();
                    if($affectedRow){
                        $current_stock = $stock->current_stock;
                    }
                }else{
                    // update part
                    if($stock_in > 0){
                        if($stock_in_flag == 1){
                            $stock = Stock::find($id);
                            $stock->previous_stock = $store_previous_row_current_stock;
                            $stock->current_stock = $store_previous_row_current_stock + $stock_in;
                            $affectedRow = $stock->update();
                            if($affectedRow){
                                $current_stock = $stock->current_stock;
                            }
                        }else if($previous_stock != $store_previous_row_current_stock){
                            $stock_in_flag = 1;

                            $stock = Stock::find($id);
                            $stock->previous_stock = $store_previous_row_current_stock;
                            $stock->current_stock = $store_previous_row_current_stock + $stock_in;
                            $affectedRow = $stock->update();
                            if($affectedRow){
                                $current_stock = $stock->current_stock;
                            }
                        }else{}
                    }else if($stock_out > 0){
                        if($stock_out_flag == 1) {
                            $stock = Stock::find($id);
                            $stock->previous_stock = $store_previous_row_current_stock;
                            $stock->current_stock = $store_previous_row_current_stock - $stock_out;
                            $affectedRow = $stock->update();
                            if ($affectedRow) {
                                $current_stock = $stock->current_stock;
                            }
                        }else if($previous_stock != $store_previous_row_current_stock) {
                            $stock_out_flag = 1;

                            $stock = Stock::find($id);
                            $stock->previous_stock = $store_previous_row_current_stock;
                            $stock->current_stock = $store_previous_row_current_stock - $stock_out;
                            $affectedRow = $stock->update();
                            if ($affectedRow) {
                                $current_stock = $stock->current_stock;
                            }
                        }else{}
                    }else{}
                }
                $store_previous_row_current_stock = $current_stock;
            }
        }else{}
    }


    function stock_sync(){
        $stock_data = Stock::whereIn('id', function($query) {
            $query->from('stocks')->groupBy('warehouse_id')->groupBy('store_id')->groupBy('product_id')->selectRaw('MIN(id)');
        })->get();

        $row_count = count($stock_data);
        if($row_count > 0){
            foreach ($stock_data as $key => $data){
                $warehouse_id = $data->warehouse_id;
                $store_id = $data->store_id;
                $product_id = $data->product_id;
                $this->product_store_stock_sync($warehouse_id,$store_id,$product_id);
            }
            return response()->json(['success'=>true,'response' => 'Data Successfully Updated.'], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Data Found!'], $this->failStatus);
        }
    }






    // start HRM + Accounting

    public function departmentList(){
        $departments = DB::table('departments')->select('id','name','status')->orderBy('id','desc')->get();

        if($departments)
        {
            $success['departments'] =  $departments;
            return response()->json(['success'=>true,'response' => $success], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Departments List Found!'], $this->failStatus);
        }
    }

    public function departmentCreate(Request $request){

        $validator = Validator::make($request->all(), [
            'name' => 'required|unique:departments,name',
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


        $departments = new Department();
        $departments->name = $request->name;
        $departments->status = $request->status;
        $departments->save();
        $insert_id = $departments->id;

        if($insert_id){
            return response()->json(['success'=>true,'response' => $departments], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'Departments Not Created Successfully!'], $this->failStatus);
        }
    }

    public function departmentEdit(Request $request){

        $validator = Validator::make($request->all(), [
            'department_id'=> 'required',
            'name' => 'required|unique:departments,name,'.$request->department_id,
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

        $check_exists_department = DB::table("departments")->where('id',$request->department_id)->pluck('id')->first();
        if($check_exists_department == null){
            return response()->json(['success'=>false,'response'=>'No Department Found!'], $this->failStatus);
        }

        $department = Department::find($request->department_id);
        $department->name = $request->name;
        $department->status = $request->status;
        $update_department = $department->save();

        if($update_department){
            return response()->json(['success'=>true,'response' => $department], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'Department Not Created Successfully!'], $this->failStatus);
        }
    }

    public function departmentDelete(Request $request){
        $check_exists_department = DB::table("departments")->where('id',$request->department_id)->pluck('id')->first();
        if($check_exists_department == null){
            return response()->json(['success'=>false,'response'=>'No Department Found!'], $this->failStatus);
        }

        //$delete_party = DB::table("product_brands")->where('id',$request->product_brand_id)->delete();
        $soft_delete_department = Department::find($request->department_id);
        $soft_delete_department->status=0;
        $affected_row = $soft_delete_department->update();
        if($affected_row)
        {
            return response()->json(['success'=>true,'response' => 'Department Successfully Soft Deleted!'], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Department Deleted!'], $this->failStatus);
        }
    }

    public function designationList(){
        $designations = DB::table('designations')->select('id','name','status')->orderBy('id','desc')->get();

        if($designations)
        {
            $success['designations'] =  $designations;
            return response()->json(['success'=>true,'response' => $success], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Designations List Found!'], $this->failStatus);
        }
    }

    public function designationCreate(Request $request){

        $validator = Validator::make($request->all(), [
            'name' => 'required|unique:designations,name',
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


        $designations = new Designation();
        $designations->name = $request->name;
        $designations->status = $request->status;
        $designations->save();
        $insert_id = $designations->id;

        if($insert_id){
            return response()->json(['success'=>true,'response' => $designations], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'designations Not Created Successfully!'], $this->failStatus);
        }
    }

    public function designationEdit(Request $request){

        $validator = Validator::make($request->all(), [
            'designation_id'=> 'required',
            'name' => 'required|unique:designations,name,'.$request->designation_id,
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

        $check_exists_designation = DB::table("designations")->where('id',$request->designation_id)->pluck('id')->first();
        if($check_exists_designation == null){
            return response()->json(['success'=>false,'response'=>'No Designation Found!'], $this->failStatus);
        }

        $designation = Designation::find($request->designation_id);
        $designation->name = $request->name;
        $designation->status = $request->status;
        $update_designation = $designation->save();

        if($update_designation){
            return response()->json(['success'=>true,'response' => $designation], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'Designation Not Created Successfully!'], $this->failStatus);
        }
    }

    public function designationDelete(Request $request){
        $check_exists_designation = DB::table("designations")->where('id',$request->designation_id)->pluck('id')->first();
        if($check_exists_designation == null){
            return response()->json(['success'=>false,'response'=>'No Designation Found!'], $this->failStatus);
        }

        $soft_delete_designation = Designation::find($request->designation_id);
        $soft_delete_designation->status=0;
        $affected_row = $soft_delete_designation->update();
        if($affected_row)
        {
            return response()->json(['success'=>true,'response' => 'Designation Successfully Soft Deleted!'], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Designation Deleted!'], $this->failStatus);
        }
    }

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
        $weekend->year = $year;
        $weekend->month = $month;
        $weekend->day = $day;
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

    // leave category
    public function leaveCategoryList(){
        $leave_categories = DB::table('leave_categories')->select('id','name','limit','duration','status')->orderBy('id','desc')->get();

        if($leave_categories)
        {
            $success['leave_categories'] =  $leave_categories;
            return response()->json(['success'=>true,'response' => $success], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Leave Category List Found!'], $this->failStatus);
        }
    }

    public function leaveCategoryCreate(Request $request){

        $validator = Validator::make($request->all(), [
            'name' => 'required|unique:leave_categories,name',
            'limit'=> 'required',
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


        $leave_category = new LeaveCategory();
        $leave_category->name = $request->name;
        $leave_category->limit = $request->limit;
        $leave_category->duration = $request->duration;
        $leave_category->status = $request->status;
        $leave_category->save();
        $insert_id = $leave_category->id;

        if($insert_id){
            return response()->json(['success'=>true,'response' => $leave_category], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'Leave Category Not Created Successfully!'], $this->failStatus);
        }
    }

    public function leaveCategoryEdit(Request $request){

        $validator = Validator::make($request->all(), [
            'leave_category_id'=> 'required',
            'name' => 'required|unique:leave_categories,name,'.$request->leave_category_id,
            'limit'=> 'required',
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

        $check_exists_leave_categories = DB::table("leave_categories")->where('id',$request->leave_category_id)->pluck('id')->first();
        if($check_exists_leave_categories == null){
            return response()->json(['success'=>false,'response'=>'No Leave Category Found!'], $this->failStatus);
        }

        $leave_category = LeaveCategory::find($request->leave_category_id);
        $leave_category->name = $request->name;
        $leave_category->limit = $request->limit;
        $leave_category->duration = $request->duration;
        $leave_category->status = $request->status;
        $update_leave_category = $leave_category->save();

        if($update_leave_category){
            return response()->json(['success'=>true,'response' => $leave_category], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'Leave Category Not Created Successfully!'], $this->failStatus);
        }
    }

    public function leaveCategoryDelete(Request $request){
        $check_exists_leave_category = DB::table("holidays")->where('id',$request->leave_category_id)->pluck('id')->first();
        if($check_exists_leave_category == null){
            return response()->json(['success'=>false,'response'=>'No Holiday Found!'], $this->failStatus);
        }

        $soft_delete_leave_category = LeaveCategory::find($request->leave_category_id);
        $soft_delete_leave_category->status=0;
        $affected_row = $soft_delete_leave_category->update();
        if($affected_row)
        {
            return response()->json(['success'=>true,'response' => 'Leave Category Successfully Soft Deleted!'], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Leave Category Deleted!'], $this->failStatus);
        }
    }

    // Employee
    public function employeeList(){
        $employees = DB::table('employees')->select('id','name','email','phone','gender','date_of_birth','blood_group','national_id','marital_status','present_address','permanent_address','status')->orderBy('id','desc')->get();

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
            'name' => 'required|unique:employees,name',
            'email'=> 'required',
            'phone'=> 'required',
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


        $employee = new Employee();
        $employee->name = $request->name;
        $employee->email = $request->email;
        $employee->phone = $request->phone;
        $employee->gender = $request->gender;
        $employee->date_of_birth = $request->date_of_birth;
        $employee->marital_status = $request->marital_status;
        $employee->present_address = $request->present_address;
        $employee->permanent_address = $request->permanent_address;
        $employee->status = $request->status;
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
            'name' => 'required|unique:employees,name,'.$request->employee_id,
            'email'=> 'required',
            'phone'=> 'required',
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
        $employee->marital_status = $request->marital_status;
        $employee->present_address = $request->present_address;
        $employee->permanent_address = $request->permanent_address;
        $employee->status = $request->status;
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

    public function employeeImage(Request $request)
    {
        $employee=Employee::find($request->employee_id);
        $base64_image_propic = $request->employee_img;
        //return response()->json(['response' => $base64_image_propic], $this-> successStatus);

        $data = $request->employee_img;
        $pos = strpos($data, ';');
        $type = explode(':', substr($data, 0, $pos))[1];
        $type1 = explode('/', $type)[1];

        if (preg_match('/^data:image\/(\w+);base64,/', $base64_image_propic)) {
            $data = substr($base64_image_propic, strpos($base64_image_propic, ',') + 1);
            $data = base64_decode($data);

            $currentDate = Carbon::now()->toDateString();
            $imagename = $currentDate . '-' . uniqid() . 'employee_pic.'.$type1 ;

            // delete old image.....
            if(Storage::disk('public')->exists('uploads/employees/'.$employee->image))
            {
                Storage::disk('public')->delete('uploads/employees/'.$employee->image);

            }

            // resize image for service category and upload
            //$data = Image::make($data)->resize(100, 100)->save($data->getClientOriginalExtension());

            // store image
            Storage::disk('public')->put("uploads/employees/". $imagename, $data);


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

    // Leave Application
    public function leaveApplicationList(){
        $leave_applications = DB::table('leave_applications')
            ->join('employees','leave_applications.employee_id','=','employees.id')
            ->join('leave_categories','leave_applications.leave_category_id','=','leave_categories.id')
            ->select('leave_applications.id','leave_applications.start_date','leave_applications.end_date','leave_applications.duration','leave_applications.reason','leave_applications.approval_status','leave_applications.status','leave_applications.id as employee_id','employees.name as employee_name','leave_categories.id as leave_category_id','leave_categories.name as leave_category_name')
            ->orderBy('id','desc')
            ->get();

        if($leave_applications)
        {
            $success['leave_applications'] =  $leave_applications;
            return response()->json(['success'=>true,'response' => $success], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Leave Application List Found!'], $this->failStatus);
        }
    }

    public function leaveApplicationCreate(Request $request){

        $validator = Validator::make($request->all(), [
            'employee_id'=> 'required',
            'leave_category_id'=> 'required',
            'start_date'=> 'required',
            'end_date'=> 'required',
            'duration'=> 'required',
        ]);

        if ($validator->fails()) {
            $response = [
                'success' => false,
                'data' => 'Validation Error.',
                'message' => $validator->errors()
            ];

            return response()->json($response, $this-> validationStatus);
        }


        $leave_application = new LeaveApplication();
        $leave_application->employee_id = $request->employee_id;
        $leave_application->leave_category_id = $request->leave_category_id;
        $leave_application->start_date = $request->start_date;
        $leave_application->end_date = $request->end_date;
        $leave_application->duration = $request->duration;
        $leave_application->reason = $request->reason;
        //$leave_application->approval_status = $request->approval_status;
        $leave_application->status = $request->status;
        $leave_application->save();
        $insert_id = $leave_application->id;

        if($insert_id){
            return response()->json(['success'=>true,'response' => $leave_application], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'Leave Application Not Created Successfully!'], $this->failStatus);
        }
    }

    public function leaveApplicationEdit(Request $request){

        $validator = Validator::make($request->all(), [
            'leave_application_id'=> 'required',
            'employee_id'=> 'required',
            'leave_category_id'=> 'required',
            'start_date'=> 'required',
            'end_date'=> 'required',
            'duration'=> 'required',
        ]);

        if ($validator->fails()) {
            $response = [
                'success' => false,
                'data' => 'Validation Error.',
                'message' => $validator->errors()
            ];

            return response()->json($response, $this->validationStatus);
        }

        $check_exists_leave_applications = DB::table("leave_applications")->where('id',$request->leave_application_id)->pluck('id')->first();
        if($check_exists_leave_applications == null){
            return response()->json(['success'=>false,'response'=>'No Leave Application Found!'], $this->failStatus);
        }

        $leave_application = LeaveApplication::find($request->leave_application_id);
        $leave_application->employee_id = $request->employee_id;
        $leave_application->leave_category_id = $request->leave_category_id;
        $leave_application->start_date = $request->start_date;
        $leave_application->end_date = $request->end_date;
        $leave_application->duration = $request->duration;
        $leave_application->reason = $request->reason;
        //$leave_application->approval_status = $request->approval_status;
        $leave_application->status = $request->status;
        $update_leave_application = $leave_application->save();

        if($update_leave_application){
            return response()->json(['success'=>true,'response' => $leave_application], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'Leave Application Not Created Successfully!'], $this->failStatus);
        }
    }

    public function leaveApplicationDelete(Request $request){
        $check_exists_leave_applications = DB::table("leave_applications")->where('id',$request->leave_application_id)->pluck('id')->first();
        if($check_exists_leave_applications == null){
            return response()->json(['success'=>false,'response'=>'No Leave Application Found!'], $this->failStatus);
        }

        $soft_delete_leave_application = LeaveApplication::find($request->leave_application_id);
        $soft_delete_leave_application->status=0;
        $affected_row = $soft_delete_leave_application->update();
        if($affected_row)
        {
            return response()->json(['success'=>true,'response' => 'Leave Application Successfully Soft Deleted!'], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Leave Application Deleted!'], $this->failStatus);
        }
    }

    // Attendance
    public function attendanceList(){
        $attendances = DB::table('attendances')
            ->join('employees','attendances.employee_id','=','employees.id')
            ->select('attendances.id','attendances.card_no','attendances.employee_name','attendances.date','attendances.year','attendances.month','attendances.on_duty','attendances.off_duty','attendances.clock_in','attendances.clock_out','attendances.late','attendances.early','attendances.absent','attendances.work_time','attendances.att_time','attendances.note','attendances.id as employee_id','employees.name as employee_name')
            ->orderBy('id','desc')
            ->get();

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
//        $attendance->note = $request->note;
//        $attendance->save();
//        $insert_id = $attendance->id;
//
//        if($insert_id){
//            return response()->json(['success'=>true,'response' => $attendance], $this->successStatus);
//        }else{
//            return response()->json(['success'=>false,'response'=>'Attendance Not Created Successfully!'], $this->failStatus);
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

        $success_insert_flag = true;


        foreach ($request->attendances as $data) {

            $date =  $data['date'];

            $year = date('Y', strtotime($date));
            //$month = date('F', strtotime($date));
            $month = date('m', strtotime($date));
            $day = date('d', strtotime($date));

            $employee_info = DB::table('employees')
                ->join('employee_office_informations','employees.id','=','employee_office_informations.employee_id')
                ->where('employee_office_informations.card_no',$data['card_no'])
                ->select('employees.id','employees.name','employee_office_informations.card_no')
                ->first();

            $attendance = new Attendance();
            $attendance->employee_id = $employee_info->id;
            $attendance->card_no = $data['card_no'];
            $attendance->employee_name = $employee_info->name;
            $attendance->date = $date;
            $attendance->year = $year;
            $attendance->month = $month;
            $attendance->day = $day;
            $attendance->on_duty = isset($data['on_duty']) ? $data['on_duty'] : '';
            $attendance->off_duty = isset($data['off_duty']) ? $data['off_duty'] : '';
            $attendance->clock_in = isset($data['clock_in']) ? $data['clock_in'] : '';
            $attendance->clock_out = isset($data['clock_out']) ? $data['clock_out'] : '';
            $attendance->late = isset($data['late']) ? $data['late'] : '';
            $attendance->early = isset($data['early']) ? $data['early'] : '';
            $attendance->absent = isset($data['absent']) ? $data['absent'] : '';
            $attendance->work_time = isset($data['work_time']) ? $data['work_time'] : '';
            $attendance->att_time = isset($data['att_time']) ? $data['att_time'] : '';
            $attendance->note = isset($data['note']) ? $data['note'] : '';
            $attendance->save();
            $insert_id = $attendance->id;
            if($insert_id == ''){
                $success_insert_flag = false;
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
//        $attendance->note = $request->note;
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
        $weekend->note = $request->note;
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

    // voucher type
    public function voucherTypeList(){
        $voucher_types = DB::table('voucher_types')->select('id','name','status')->orderBy('id','desc')->get();

        if($voucher_types)
        {
            $success['voucher_type'] =  $voucher_types;
            return response()->json(['success'=>true,'response' => $success], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Voucher Type List Found!'], $this->failStatus);
        }
    }

    public function voucherTypeCreate(Request $request){

        $validator = Validator::make($request->all(), [
            'name' => 'required|unique:voucher_types,name',
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


        $voucherType = new VoucherType();
        $voucherType->name = $request->name;
        $voucherType->status = $request->status;
        $voucherType->save();
        $insert_id = $voucherType->id;

        if($insert_id){
            return response()->json(['success'=>true,'response' => $voucherType], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'Voucher Type Not Created Successfully!'], $this->failStatus);
        }
    }

    public function voucherTypeEdit(Request $request){

        $validator = Validator::make($request->all(), [
            'voucher_type_id'=> 'required',
            'name' => 'required|unique:voucher_types,name,'.$request->voucher_type_id,
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

        $check_exists_voucher_type = DB::table("voucher_types")->where('id',$request->voucher_type_id)->pluck('id')->first();
        if($check_exists_voucher_type == null){
            return response()->json(['success'=>false,'response'=>'No Voucher Type Found!'], $this->failStatus);
        }

        $voucher_types = VoucherType::find($request->voucher_type_id);
        $voucher_types->name = $request->name;
        $voucher_types->status = $request->status;
        $update_voucher_type = $voucher_types->save();

        if($update_voucher_type){
            return response()->json(['success'=>true,'response' => $voucher_types], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'Voucher Type Not Created Successfully!'], $this->failStatus);
        }
    }

    public function voucherTypeDelete(Request $request){
        $check_exists_voucher_type = DB::table("voucher_types")->where('id',$request->voucher_type_id)->pluck('id')->first();
        if($check_exists_voucher_type == null){
            return response()->json(['success'=>false,'response'=>'No Voucher Type Found!'], $this->failStatus);
        }

        //$delete_party = DB::table("voucher_types")->where('id',$request->voucher_type_id)->delete();
        $soft_delete_voucher_type = VoucherType::find($request->voucher_type_id);
        $soft_delete_voucher_type->status=0;
        $affected_row = $soft_delete_voucher_type->update();
        if($affected_row)
        {
            return response()->json(['success'=>true,'response' => 'Voucher Type Successfully Soft Deleted!'], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Voucher Type Deleted!'], $this->failStatus);
        }
    }

    // tangible asset
    public function tangibleAssetList(){
        $tangible_assets = DB::table('tangible_assets')->select('id','name','unique_id','location','date','description','status')->orderBy('id','desc')->get();

        if($tangible_assets)
        {
            $success['tangible_asset'] =  $tangible_assets;
            return response()->json(['success'=>true,'response' => $success], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Tangible Asset List Found!'], $this->failStatus);
        }
    }

    public function tangibleAssetCreate(Request $request){

        $validator = Validator::make($request->all(), [
            'name' => 'required|unique:tangible_assets,name',
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


        $tangibleAsset = new TangibleAssets();
        $tangibleAsset->name = $request->name;
        $tangibleAsset->unique_id = $request->unique_id;
        $tangibleAsset->location = $request->location;
        $tangibleAsset->date = $request->date;
        $tangibleAsset->description = $request->description;
        $tangibleAsset->status = $request->status;
        $tangibleAsset->save();
        $insert_id = $tangibleAsset->id;

        if($insert_id){
            return response()->json(['success'=>true,'response' => $tangibleAsset], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'Tangible Asset Not Created Successfully!'], $this->failStatus);
        }
    }

    public function tangibleAssetEdit(Request $request){

        $validator = Validator::make($request->all(), [
            'tangible_asset_id'=> 'required',
            'name' => 'required|unique:tangible_assets,name,'.$request->tangible_asset_id,
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

        $check_exists_tangible_asset = DB::table("tangible_assets")->where('id',$request->tangible_asset_id)->pluck('id')->first();
        if($check_exists_tangible_asset == null){
            return response()->json(['success'=>false,'response'=>'No Tangible Asset Found!'], $this->failStatus);
        }

        $tangible_asset = TangibleAssets::find($request->tangible_asset_id);
        $tangible_asset->name = $request->name;
        $tangible_asset->unique_id = $request->unique_id;
        $tangible_asset->location = $request->location;
        $tangible_asset->date = $request->date;
        $tangible_asset->description = $request->description;
        $tangible_asset->status = $request->status;
        $update_tangible_asset = $tangible_asset->save();

        if($update_tangible_asset){
            return response()->json(['success'=>true,'response' => $tangible_asset], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'Tangible Asset Not Created Successfully!'], $this->failStatus);
        }
    }

    public function tangibleAssetDelete(Request $request){
        $check_exists_tangible_asset = DB::table("tangible_assets")->where('id',$request->tangible_asset_id)->pluck('id')->first();
        if($check_exists_tangible_asset == null){
            return response()->json(['success'=>false,'response'=>'No tangible asset Found!'], $this->failStatus);
        }

        //$delete_party = DB::table("tangible_assets")->where('id',$request->tangible_asset_id)->delete();
        $soft_delete_tangible_asset = TangibleAssets::find($request->tangible_asset_id);
        $soft_delete_tangible_asset->status=0;
        $affected_row = $soft_delete_tangible_asset->update();
        if($affected_row)
        {
            return response()->json(['success'=>true,'response' => 'Tangible Asset Successfully Soft Deleted!'], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Tangible Asset Deleted!'], $this->failStatus);
        }
    }

}
