<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Party;
use App\PaymentPaid;
use App\Product;
use App\ProductBrand;
use App\ProductPurchase;
use App\ProductPurchaseDetail;
use App\ProductUnit;
use App\Stock;
use App\Store;
use App\Transaction;
use App\User;
use App\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
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

        $delete_warehouse = DB::table("warehouses")->where('id',$request->warehouse_id)->delete();
        if($delete_warehouse)
        {
            return response()->json(['success'=>true,'response' => 'Warehouse Successfully Deleted!'], $this->successStatus);
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

        $delete_store = DB::table("stores")->where('id',$request->store_id)->delete();
        if($delete_store)
        {
            return response()->json(['success'=>true,'response' => 'Store Successfully Deleted!'], $this->successStatus);
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

        $delete_user = DB::table("users")->where('id',$request->user_id)->delete();
        if($delete_user)
        {
            return response()->json(['success'=>true,'response' => 'User Successfully Deleted!'], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No User Deleted!'], $this->failStatus);
        }
    }

    public function partyList(){
        $parties = DB::table('parties')->select('id','type','name','phone','address','status')->get();

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

    // product brand
    public function productBrandList(){
        $product_brands = DB::table('product_brands')->select('id','name','status')->get();

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

        $delete_party = DB::table("product_brands")->where('id',$request->product_brand_id)->delete();
        if($delete_party)
        {
            return response()->json(['success'=>true,'response' => 'Product Brand Successfully Deleted!'], $this->successStatus);
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

        $delete_product_unit = DB::table("product_units")->where('id',$request->product_unit_id)->delete();
        if($delete_product_unit)
        {
            return response()->json(['success'=>true,'response' => 'Product Unit Successfully Deleted!'], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Product Unit Deleted!'], $this->failStatus);
        }
    }

    public function productList(){
        $products = DB::table('products')
            ->leftJoin('product_units','products.product_unit_id','product_units.id')
            ->leftJoin('product_brands','products.product_brand_id','product_brands.id')
            ->select('products.id','products.name as product_name','product_units.id as unit_id','product_units.name as unit_name','products.item_code','products.barcode','products.self_no','products.low_inventory_alert','product_brands.id as brand_id','product_brands.name as brand_name','products.purchase_price','products.selling_price','products.note','products.date','products.status')
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

        $validator = Validator::make($request->all(), [
            'name' => 'required|unique:products,name',
            'product_unit_id'=> 'required',
            //'barcode'=> 'required',
            'barcode' => 'required|unique:products,barcode',
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
        $product->barcode = $request->barcode;
        $product->self_no = $request->self_no ? $request->self_no : NULL;
        $product->low_inventory_alert = $request->low_inventory_alert ? $request->low_inventory_alert : NULL;
        $product->product_brand_id = $request->product_brand_id ? $request->product_brand_id : NULL;
        $product->purchase_price = $request->purchase_price;
        $product->selling_price = $request->selling_price;
        $product->note = $request->note ? $request->note : NULL;
        $product->date = $request->date;
        $product->status = $request->status;
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
            'barcode' => 'required|unique:products,barcode,'.$request->product_id,
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

        $delete_product = DB::table("products")->where('id',$request->product_id)->delete();
        if($delete_product)
        {
            return response()->json(['success'=>true,'response' => 'Product Successfully Deleted!'], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Product Deleted!'], $this->failStatus);
        }
    }

    public function productUnitAndBrand(Request $request){
        $product_brand_and_unit = DB::table('products')
            ->leftJoin('product_units','products.product_unit_id','product_units.id')
            ->leftJoin('product_brands','products.product_brand_id','product_brands.id')
            ->where('products.id',$request->product_id)
            ->select('product_units.id as product_unit_id','product_units.name as product_unit_name','product_brands.id as product_brand_id','product_brands.name as product_brand_name')
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
        $product_purchases = DB::table('product_purchases')
            ->leftJoin('users','product_purchases.user_id','users.id')
            ->leftJoin('parties','product_purchases.party_id','parties.id')
            ->select('product_purchases.id','product_purchases.invoice_no','product_purchases.total_amount','product_purchases.paid_amount','product_purchases.due_amount','product_purchases.purchase_date_time','users.name as user_name','parties.name as supplier_name')
            ->get();

        if($product_purchases)
        {
            $success['product_whole_purchases'] =  $product_purchases;
            return response()->json(['success'=>true,'response' => $success], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Product List Found!'], $this->failStatus);
        }
    }

    public function productWholePurchaseCreate(Request $request){
        //dd($request->all());
        $this->validate($request, [
            'user_id'=> 'required',
            'party_id'=> 'required',
            'warehouse_id'=> 'required',
            'paid_amount'=> 'required',
            'due_amount'=> 'required',
            'total_amount'=> 'required',
            'product_unit_id'=> 'required',
            'product_id'=> 'required',
            'qty'=> 'required',
            'price'=> 'required',
            'mrp_price'=> 'required',
            'payment_type'=> 'required',
        ]);

        $row_count = count($request->product_id);
        $total_amount = 0;
        for($i=0; $i<$row_count;$i++)
        {
            $total_amount += $request->qty[$i]*$request->price[$i];
        }

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

        // product purchase
        $productPurchase = new ProductPurchase();
        $productPurchase ->invoice_no = $final_invoice;
        $productPurchase ->user_id = $request->user_id;
        $productPurchase ->party_id = $request->party_id;
        $productPurchase ->warehouse_id = $request->warehouse_id;
        $productPurchase ->paid_amount = $request->paid_amount;
        $productPurchase ->due_amount = $request->due_amount;
        $productPurchase ->total_amount = $total_amount;
        $productPurchase ->purchase_date = $date;
        $productPurchase ->purchase_date_time = $date_time;
        $productPurchase->save();
        $insert_id = $productPurchase->id;
        if($insert_id)
        {
            for($i=0; $i<$row_count;$i++)
            {
                $product_id = $request->product_id[$i];
                $barcode = Product::where('id',$product_id)->pluck('barcode')->first();

                // product purchase detail
                $purchase_purchase_detail = new ProductPurchaseDetail();
                $purchase_purchase_detail->product_purchase_id = $insert_id;
                $purchase_purchase_detail->product_unit_id = $request->product_unit_id[$i];
                $purchase_purchase_detail->product_brand_id = $request->product_brand_id[$i] ? $request->product_brand_id[$i] : NULL;
                $purchase_purchase_detail->product_id = $request->product_id[$i];
                $purchase_purchase_detail->qty = $request->qty[$i];
                $purchase_purchase_detail->price = $request->price[$i];
                $purchase_purchase_detail->mrp_price = $request->mrp_price[$i];
                $purchase_purchase_detail->sub_total = $request->qty[$i]*$request->price[$i];
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
                $stock->user_id = $request->user_id;
                $stock->warehouse_id = $request->warehouse_id;
                $stock->product_id = $request->product_id[$i];
                $stock->product_unit_id = $request->product_unit_id[$i];
                $stock->product_brand_id = $request->product_brand_id[$i] ? $request->product_brand_id[$i] : NULL;
                $stock->stock_type = 'whole-purchase';
                $stock->previous_stock = $previous_stock;
                $stock->stock_in = $request->qty[$i];
                $stock->stock_out = 0;
                $stock->current_stock = $previous_stock + $request->qty[$i];
                $stock->stock_date = $date;
                $stock->stock_date_time = $date_time;
                $stock->save();
            }

            // transaction
            $transaction = new Transaction();
            $transaction->ref_id = $insert_id;
            $transaction->invoice_no = $final_invoice;
            $transaction->user_id = $request->user_id;
            $transaction->warehouse_id = $request->warehouse_id;
            $transaction->party_id = $request->party_id;
            $transaction->transaction_type = 'whole-purchase';
            $transaction->payment_type = $request->payment_type;
            $transaction->amount = $request->paid_amount;
            $transaction->transaction_date = $date;
            $transaction->transaction_date_time = $date_time;
            $transaction->save();

            // payment paid
            $payment_paid = new PaymentPaid();
            $payment_paid->invoice_no = $final_invoice;
            $payment_paid->product_purchase_id = $insert_id;
            $payment_paid->user_id = $request->user_id;
            $payment_paid->party_id = $request->party_id;
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


}
