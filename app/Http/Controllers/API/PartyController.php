<?php

namespace App\Http\Controllers\API;


use App\ChartOfAccount;
use App\Helpers\UserInfo;
use App\Http\Controllers\Controller;
use App\Party;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class PartyController extends Controller
{
    public $successStatus = 200;
    public $authStatus = 401;
    public $failStatus = 402;
    public $ExistsStatus = 403;
    public $validationStatus = 404;

    public function partyList(){
        $parties = DB::table('parties')
            ->select('id','type','customer_type','name','phone','address','virtual_balance','status')
            ->orderBy('id','desc')
            ->get();

        if($parties)
        {
            $success['parties'] =  $parties;
            return response()->json(['success'=>true,'response' => $success], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Party List Found!'], $this->failStatus);
        }
    }

    public function partyCustomerList(){
        $party_customers = DB::table('parties')
            ->select('id','type','customer_type','name','phone','address','virtual_balance','status')
            ->where('type','customer')
            ->orderBy('id','desc')
            ->get();

        if($party_customers)
        {
            $party_customer_arr = [];
            foreach($party_customers as $party_customer){

                $sale_total_amount = 0;

                $total_amount = DB::table('transactions')
                    ->select(DB::raw('SUM(amount) as sum_total_amount'))
                    ->where('party_id',$party_customer->id)
                    ->where('transaction_type','whole_sale')
                    ->orWhere('transaction_type','pos_sale')
                    ->first();

                if(!empty($total_amount)){
                    $sale_total_amount = $total_amount->sum_total_amount;
                }

                $nested_data['id'] = $party_customer->id;
                $nested_data['type'] = $party_customer->type;
                $nested_data['customer_type'] = $party_customer->customer_type;
                $nested_data['name'] = $party_customer->name;
                $nested_data['phone'] = $party_customer->phone;
                $nested_data['address'] = $party_customer->address;
                $nested_data['sale_total_amount'] = $sale_total_amount;
                $nested_data['virtual_balance'] = $party_customer->virtual_balance;
                $nested_data['status'] = $party_customer->status;

                array_push($party_customer_arr,$nested_data);
            }

            $success['party_customers'] =  $party_customer_arr;
            return response()->json(['success'=>true,'response' => $success], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Party Customer List Found!'], $this->failStatus);
        }
    }

    public function partySupplierList(){
        $party_suppliers = DB::table('parties')
            ->select('id','type','customer_type','name','phone','address','virtual_balance','status')
            ->where('type','supplier')
            ->orderBy('id','desc')
            ->get();

        if($party_suppliers)
        {
            $party_supplier_arr = [];
            foreach($party_suppliers as $party_supplier){

                $purchase_total_amount = 0;

                $total_amount = DB::table('transactions')
                    ->select(DB::raw('SUM(amount) as sum_total_amount'))
                    ->where('party_id',$party_supplier->id)
                    ->where('transaction_type','whole_purchase')
                    ->orWhere('transaction_type','pos_purchase')
                    ->first();

                if(!empty($total_amount)){
                    $purchase_total_amount = $total_amount->sum_total_amount;
                }

                $nested_data['id'] = $party_supplier->id;
                $nested_data['type'] = $party_supplier->type;
                $nested_data['customer_type'] = $party_supplier->customer_type;
                $nested_data['name'] = $party_supplier->name;
                $nested_data['phone'] = $party_supplier->phone;
                $nested_data['address'] = $party_supplier->address;
                $nested_data['purchase_total_amount'] = $purchase_total_amount;
                $nested_data['virtual_balance'] = $party_supplier->virtual_balance;
                $nested_data['status'] = $party_supplier->status;

                array_push($party_supplier_arr,$nested_data);
            }

            $success['party_suppliers'] =  $party_supplier_arr;
            return response()->json(['success'=>true,'response' => $success], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Party Supplier List Found!'], $this->failStatus);
        }
    }

    public function partyCreate(Request $request){

        $validator = Validator::make($request->all(), [
            'type'=> 'required',
            'email' => 'unique:parties',
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

        if($request->type == 'customer'){

//            $parties = Party::where('phone',$request->phone)->pluck('id','name')->first();

//            if($parties){
//                $response = [
//                    'success' => false,
//                    'data' => 'Validation Error.',
//                    'message' => ['Phone No Already Exist'],
//                    'exist'=>1
//                ];
//                return response()->json($response, $this-> failStatus);
//            }
            $parties = Party::where('phone',$request->phone)->first();
            if($parties){
                return response()->json(['success'=>true,'response' => $parties,'exist'=>1], $this->successStatus);
            }
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




            if($request->type == 'customer'){
                $account = DB::table('chart_of_accounts')
                    ->where('head_level',3)
                    ->where('head_code', 'like', '10203%')
                    ->Orderby('created_at', 'desc')
                    ->limit(1)
                    ->first();
                //dd($account);
                if(!empty($account)){
                    $head_code=$account->head_code+1;
                    //$p_acc = $headcode ."-".$request->name;
                }else{
                    $head_code="1020300001";
                    //$p_acc = $headcode ."-".$request->name;
                }
                $head_name = $request->name;

                $parent_head_name = 'Account Receivable';
                $head_level = 3;
                $head_type = 'A';


                $coa = new ChartOfAccount();
                $coa->head_code             = $head_code;
                $coa->head_name             = $head_name;
                $coa->parent_head_name      = $parent_head_name;
                $coa->head_type             = $head_type;
                $coa->head_level            = $head_level;
                $coa->is_active             = '1';
                $coa->is_transaction        = '1';
                $coa->is_general_ledger     = '1';
                $coa->ref_id                = $insert_id;
                $coa->user_bank_account_no  = NULL;
                $coa->created_by              = Auth::User()->id;
                $coa->updated_by              = Auth::User()->id;
                $coa->save();
            }else{
                $account = DB::table('chart_of_accounts')
                    ->where('head_level',3)
                    ->where('head_code', 'like', '50101%')
                    ->Orderby('created_at', 'desc')
                    ->limit(1)
                    ->first();
                //dd($account);
                if(!empty($account)){
                    $head_code=$account->head_code+1;
                    //$p_acc = $headcode ."-".$request->name;
                }else{
                    $head_code="5010100001";
                    //$p_acc = $headcode ."-".$request->name;
                }
                $head_name = $request->name;

                $parent_head_name = 'Account Payable';
                $head_level = 3;
                $head_type = 'L';


                $coa = new ChartOfAccount();
                $coa->head_code             = $head_code;
                $coa->head_name             = $head_name;
                $coa->parent_head_name      = $parent_head_name;
                $coa->head_type             = $head_type;
                $coa->head_level            = $head_level;
                $coa->is_active             = '1';
                $coa->is_transaction        = '1';
                $coa->is_general_ledger     = '1';
                $coa->ref_id                = $insert_id;
                $coa->user_bank_account_no  = NULL;
                $coa->created_by              = Auth::User()->id;
                $coa->updated_by              = Auth::User()->id;
                $coa->save();
            }



            return response()->json(['success'=>true,'response' => $parties,'exist'=>0], $this->successStatus);
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

    public function customerSaleByCustomerId(Request $request){


        $product_sales = DB::table('product_sales')
            ->leftJoin('users','product_sales.user_id','users.id')
            ->leftJoin('parties','product_sales.party_id','parties.id')
            ->leftJoin('warehouses','product_sales.warehouse_id','warehouses.id')
            ->leftJoin('stores','product_sales.store_id','stores.id')
            ->where('product_sales.party_id',$request->customer_id)
            ->select('product_sales.id','product_sales.invoice_no','product_sales.discount_type','product_sales.discount_amount','product_sales.total_amount','product_sales.paid_amount','product_sales.due_amount','product_sales.sale_date_time','users.name as user_name','parties.id as customer_id','parties.name as customer_name','warehouses.id as warehouse_id','warehouses.name as warehouse_name','stores.id as store_id','stores.name as store_name')
            ->get();
        if(count($product_sales) > 0){
            $success['product_sales'] = $product_sales;

            return response()->json(['success'=>true,'response' => $success], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Sale Found!'], $this->failStatus);
        }
    }

    public function customerSaleDetailsBySaleId(Request $request){

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
}
