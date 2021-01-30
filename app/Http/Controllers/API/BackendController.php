<?php

namespace App\Http\Controllers\API;

use App\Helpers\UserInfo;
use App\Http\Controllers\Controller;
use App\Party;
use App\PaymentCollection;
use App\PaymentPaid;
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
use App\Stock;
use App\Store;
use App\Transaction;
use App\User;
use App\Warehouse;
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
        $parties = DB::table('parties')->select('id','type','name','phone','address','virtual_balance','status')->get();

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

    public function productList(){
        $products = DB::table('products')
            ->leftJoin('product_units','products.product_unit_id','product_units.id')
            ->leftJoin('product_brands','products.product_brand_id','product_brands.id')
            ->select('products.id','products.name as product_name','products.image','product_units.id as unit_id','product_units.name as unit_name','products.item_code','products.barcode','products.self_no','products.low_inventory_alert','product_brands.id as brand_id','product_brands.name as brand_name','products.purchase_price','products.selling_price','products.note','products.date','products.status')
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

    public function allActiveProductList(){
        $products = DB::table('products')
            ->leftJoin('product_units','products.product_unit_id','product_units.id')
            ->leftJoin('product_brands','products.product_brand_id','product_brands.id')
            ->where('products.status',1)
            ->select('products.id','products.name as product_name','products.image','product_units.id as unit_id','product_units.name as unit_name','products.item_code','products.barcode','products.self_no','products.low_inventory_alert','product_brands.id as brand_id','product_brands.name as brand_name','products.purchase_price','products.selling_price','products.note','products.date','products.status')
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

            return response()->json($response, $this-> validationStatus);
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
        $product->selling_price = $request->selling_price;
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

        $product = Product::find($request->product_id);
        $product->name = $request->name;
        $product->product_unit_id = $request->product_unit_id;
        $product->item_code = $request->item_code ? $request->item_code : NULL;
        $product->barcode = $request->barcode;
        $product->self_no = $request->self_no ? $request->self_no : NULL;
        $product->low_inventory_alert = $request->low_inventory_alert ? $request->low_inventory_alert : NULL;
        $product->product_brand_id = $request->product_brand_id ? $request->product_brand_id : NULL;
        $product->purchase_price = $request->purchase_price;
        $product->selling_price = $request->selling_price;
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
            //->select('product_purchases.id','product_purchases.invoice_no','product_purchases.total_amount','product_purchases.paid_amount','product_purchases.due_amount','product_purchases.purchase_date_time','users.name as user_name','parties.name as supplier_name')
            ->select('product_purchases.id','product_purchases.invoice_no','product_purchases.discount_type','product_purchases.discount_amount','product_purchases.total_amount','product_purchases.paid_amount','product_purchases.due_amount','product_purchases.purchase_date_time','users.name as user_name','parties.id as supplier_id','parties.name as supplier_name','warehouses.id as warehouse_id','warehouses.name as warehouse_name')
            ->get();

        if($product_whole_purchases)
        {
            $success['product_whole_purchases'] =  $product_whole_purchases;
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

                if($stock_row->stock_in != $data['qty']){

                    if($data['qty'] > $stock_row->stock_in){
                        $add_or_minus_stock_in = $data['qty'] - $stock_row->stock_in;
                        $update_stock_in = $stock_row->stock_in + $add_or_minus_stock_in;
                        $update_current_stock = $stock_row->current_stock + $add_or_minus_stock_in;
                    }else{
                        $add_or_minus_stock_in =  $stock_row->stock_in - $data['qty'];
                        $update_stock_in = $stock_row->stock_in - $add_or_minus_stock_in;
                        $update_current_stock = $stock_row->current_stock - $add_or_minus_stock_in;
                    }

                    $stock_row->user_id = $user_id;
                    $stock_row->stock_in = $update_stock_in;
                    $stock_row->current_stock = $update_current_stock;
                    $stock_row->update();
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
            ->get();

        if($product_pos_purchases)
        {
            $success['product_pos_purchases'] =  $product_pos_purchases;
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

                if($stock_row->stock_in != $data['qty']){

                    if($data['qty'] > $stock_row->stock_in){
                        $add_or_minus_stock_in = $data['qty'] - $stock_row->stock_in;
                        $update_stock_in = $stock_row->stock_in + $add_or_minus_stock_in;
                        $update_current_stock = $stock_row->current_stock + $add_or_minus_stock_in;
                    }else{
                        $add_or_minus_stock_in =  $stock_row->stock_in - $data['qty'];
                        $update_stock_in = $stock_row->stock_in - $add_or_minus_stock_in;
                        $update_current_stock = $stock_row->current_stock - $add_or_minus_stock_in;
                    }

                    $stock_row->user_id = $user_id;
                    $stock_row->stock_in = $update_stock_in;
                    $stock_row->current_stock = $update_current_stock;
                    $stock_row->update();
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

    public function warehouseCurrentStockList(Request $request){
        $warehouse_stock_product_list = Stock::where('warehouse_id',$request->warehouse_id)
            ->select('product_id')
            ->groupBy('product_id')
            ->latest('id')
            ->get();

        $warehouse_stock_product = [];
        foreach($warehouse_stock_product_list as $data){

            $stock_row = DB::table('stocks')
                ->join('warehouses','stocks.warehouse_id','warehouses.id')
                ->leftJoin('products','stocks.product_id','products.id')
                ->leftJoin('product_units','stocks.product_unit_id','product_units.id')
                ->leftJoin('product_brands','stocks.product_brand_id','product_brands.id')
                ->where('stocks.stock_where','warehouse')
                ->where('stocks.product_id',$data->product_id)
                ->where('stocks.warehouse_id',$request->warehouse_id)
                ->select('stocks.*','warehouses.name as warehouse_name','products.name as product_name','products.purchase_price','products.selling_price','products.item_code','products.barcode','products.image','product_units.name as product_unit_name','product_brands.name as product_brand_name')
                ->latest('id','desc')
                ->first();

            if($stock_row){
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
        }

        if($warehouse_stock_product)
        {
            $success['warehouse_current_stock_list'] =  $warehouse_stock_product;
            return response()->json(['success'=>true,'response' => $success], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Warehouse Current Stock List Found!'], $this->failStatus);
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


    public function warehouseToStoreStockCreate(Request $request){
        $this->validate($request, [
            'warehouse_id'=> 'required',
        ]);

        $date = date('Y-m-d');
        $date_time = date('Y-m-d h:i:s');

        $user_id = Auth::user()->id;
        $warehouse_id = $request->warehouse_id;
        $store_id = $request->store_id;

        $insert_id = false;

        foreach ($request->products as $data) {
            $product_id = $data['product_id'];


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
            $stock->ref_id = NULL;
            $stock->user_id = $user_id;
            $stock->warehouse_id = $warehouse_id;
            $stock->store_id = $store_id;
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
            $stock->ref_id = NULL;
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
        }


        if($insert_id){
            return response()->json(['success'=>true,'response' => 'Warehouse To Store Stock Successfully Inserted.'], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Warehouse To Store Stock Successfully Inserted.!'], $this->failStatus);
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

    public function storeCurrentStockList(Request $request){
        $store_stock_product_list = Stock::where('store_id',$request->store_id)
            ->select('product_id')
            ->groupBy('product_id')
            ->latest('id')
            ->get();

        $store_stock_product = [];
        foreach($store_stock_product_list as $data){

            $stock_row = DB::table('stocks')
                ->join('warehouses','stocks.warehouse_id','warehouses.id')
                ->leftJoin('products','stocks.product_id','products.id')
                ->leftJoin('product_units','stocks.product_unit_id','product_units.id')
                ->leftJoin('product_brands','stocks.product_brand_id','product_brands.id')
                ->where('stocks.stock_where','store')
                ->where('stocks.product_id',$data->product_id)
                ->where('stocks.store_id',$request->store_id)
                ->select('stocks.*','warehouses.name as warehouse_name','products.name as product_name','products.purchase_price','products.selling_price','products.item_code','products.barcode','products.image','product_units.name as product_unit_name','product_brands.name as product_brand_name')
                ->orderBy('stocks.id','desc')
                ->first();

            if($stock_row){
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

                array_push($store_stock_product,$nested_data);
            }
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
            //->select('product_purchases.id','product_purchases.invoice_no','product_purchases.total_amount','product_purchases.paid_amount','product_purchases.due_amount','product_purchases.purchase_date_time','users.name as user_name','parties.name as supplier_name')
            ->select('product_sales.id','product_sales.invoice_no','product_sales.discount_type','product_sales.discount_amount','product_sales.total_amount','product_sales.paid_amount','product_sales.due_amount','product_sales.sale_date_time','users.name as user_name','parties.id as customer_id','parties.name as customer_name','warehouses.id as warehouse_id','warehouses.name as warehouse_name','stores.id as store_id','stores.name as store_name')
            ->get();

        if($product_whole_sales)
        {
            $success['product_whole_sales'] =  $product_whole_sales;
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
            ->select('products.id as product_id','products.name as product_name','product_units.id as product_unit_id','product_units.name as product_unit_name','product_brands.id as product_brand_id','product_brands.name as product_brand_name','product_sale_details.qty','product_sale_details.id as product_sale_detail_id','product_sale_details.price as mrp_price')
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
        $productSale->paid_amount = $request->paid_amount;
        $productSale->due_amount = $request->due_amount;
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
                $product_sale_detail->sub_total = $data['qty']*$data['mrp_price'];
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


            return response()->json(['success'=>true,'response' => 'Inserted Successfully.'], $this->successStatus);
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
        $productSale ->user_id = $user_id;
        $productSale ->party_id = $request->party_id;
        $productSale ->warehouse_id = $warehouse_id;
        $productSale ->store_id = $store_id;
        $productSale ->discount_type = $request->discount_type ? $request->discount_type : NULL;
        $productSale ->discount_amount = $request->discount_amount ? $request->discount_amount : 0;
        $productSale ->paid_amount = $request->paid_amount;
        $productSale ->due_amount = $request->due_amount;
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
                $product_sale_detail = ProductSaleDetail::find($product_sale_detail_id);
                $product_sale_detail->product_unit_id = $data['product_unit_id'];
                $product_sale_detail->product_brand_id = $data['product_brand_id'] ? $data['product_brand_id'] : NULL;
                $product_sale_detail->product_id = $product_id;
                $product_sale_detail->qty = $data['qty'];
                $product_sale_detail->price = $data['mrp_price'];
                $product_sale_detail->sub_total = $data['qty']*$data['mrp_price'];
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
            //->select('product_purchases.id','product_purchases.invoice_no','product_purchases.total_amount','product_purchases.paid_amount','product_purchases.due_amount','product_purchases.purchase_date_time','users.name as user_name','parties.name as supplier_name')
            ->select('product_sales.id','product_sales.invoice_no','product_sales.discount_type','product_sales.discount_amount','product_sales.total_amount','product_sales.paid_amount','product_sales.due_amount','product_sales.sale_date_time','users.name as user_name','parties.id as customer_id','parties.name as customer_name','warehouses.id as warehouse_id','warehouses.name as warehouse_name','stores.id as store_id','stores.name as store_name')
            ->get();

        if($product_pos_sales)
        {
            $success['product_pos_sales'] =  $product_pos_sales;
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
            ->select('products.id as product_id','products.name as product_name','product_units.id as product_unit_id','product_units.name as product_unit_name','product_brands.id as product_brand_id','product_brands.name as product_brand_name','product_sale_details.qty','product_sale_details.id as product_sale_detail_id','product_sale_details.price as mrp_price')
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
                $product_sale_detail->sub_total = $data['qty']*$data['mrp_price'];
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
                return response()->json(['success'=>true,'transaction_id' => $transaction_id], $this->successStatus);
            }else{
                return response()->json(['success'=>true,'response' => 'Inserted Successfully.'], $this->successStatus);
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
                $purchase_sale_detail->sub_total = $data['qty']*$data['mrp_price'];
                $purchase_sale_detail->barcode = $barcode;
                $purchase_sale_detail->update();


                // product stock
                $stock_row = Stock::where('ref_id',$request->product_sale_id)->where('stock_type','pos_sale')->where('product_id',$product_id)->first();

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
}
