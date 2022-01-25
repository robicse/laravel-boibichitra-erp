<?php

namespace App\Http\Controllers\API;


use App\ChartOfAccount;
use App\ChartOfAccountTransaction;
use App\ChartOfAccountTransactionDetail;
use App\Helpers\APIHelpers;
use App\Helpers\UserInfo;
use App\Http\Controllers\Controller;
use App\Party;
use App\Store;
use App\User;
use App\VoucherType;
use Illuminate\Database\Eloquent\Model;
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
            ->select('id','type','customer_type','name','phone','email','address','virtual_balance','status','initial_due')
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
                    //->where('transaction_type','whole_sale')
                    //->orWhere('transaction_type','pos_sale')
                    ->where(function ($query) {
                        $query->where('transaction_type','whole_sale')
                        ->orWhere('transaction_type','pos_sale');
                    })
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
                $nested_data['initial_due'] = $party_customer->initial_due;

                array_push($party_customer_arr,$nested_data);
            }

            $success['party_customers'] =  $party_customer_arr;
            return response()->json(['success'=>true,'response' => $success], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Party Customer List Found!'], $this->failStatus);
        }
    }


    // supplier
    public function partySupplierList(){
        try {
            $party_suppliers = DB::table('parties')
                ->select('id','type','customer_type','name','phone','email','address','virtual_balance','status')
                ->where('type','supplier')
                ->orderBy('id','desc')
                ->get();

            if($party_suppliers === null){
                $response = APIHelpers::createAPIResponse(true,404,'No Suppliers Found.',null);
                return response()->json($response,404);
            }

            $party_supplier_arr = [];
            foreach ($party_suppliers as $party_supplier) {

                $purchase_total_amount = 0;

                $total_amount = DB::table('transactions')
                    ->select(DB::raw('SUM(amount) as sum_total_amount'))
                    ->where('party_id', $party_supplier->id)
                    //->where('transaction_type','whole_purchase')
                    //->orWhere('transaction_type','pos_purchase')
                    ->where(function ($query) {
                        $query->where('transaction_type', 'whole_purchase')
                            ->orWhere('transaction_type', 'pos_purchase');
                    })
                    ->first();

                if (!empty($total_amount)) {
                    $purchase_total_amount = $total_amount->sum_total_amount;
                }

                $nested_data['id'] = $party_supplier->id;
                $nested_data['type'] = $party_supplier->type;
                $nested_data['customer_type'] = $party_supplier->customer_type;
                $nested_data['name'] = $party_supplier->name;
                $nested_data['phone'] = $party_supplier->phone;
                $nested_data['email'] = $party_supplier->email;
                $nested_data['address'] = $party_supplier->address;
                $nested_data['purchase_total_amount'] = $purchase_total_amount;
                $nested_data['virtual_balance'] = $party_supplier->virtual_balance;
                $nested_data['status'] = $party_supplier->status;

                array_push($party_supplier_arr, $nested_data);
            }

            $response = APIHelpers::createAPIResponse(false,200,'',$party_supplier_arr);
            return response()->json($response,200);

        } catch (\Exception $e) {
            //return $e->getMessage();
            $response = APIHelpers::createAPIResponse(false,500,'Internal Server Error.',null);
            return response()->json($response,500);
        }
    }

    public function supplierCreate(Request $request){

        try {
            // required
            $validator = Validator::make($request->all(), [
                'name' => 'required',
                'phone' => 'required',
                'status'=> 'required',
            ]);

            if ($validator->fails()) {
                $response = APIHelpers::createAPIResponse(true,400,$validator->errors(),null);
                return response()->json($response,400);
            }

            $parties = new Party();
            $parties->type = 'supplier';
            $parties->customer_type = NULL;
            $parties->name = $request->name;
            $parties->slug = Str::slug($request->name);
            $parties->phone = $request->phone;
            $parties->email = $request->email;
            $parties->address = $request->address;
            $parties->status = $request->status;
            $parties->save();
            $insert_id = $parties->id;

            if($insert_id){
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
                $head_name = $request->name.'('.$request->phone.')';

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

                $response = APIHelpers::createAPIResponse(false,201,'Supplier Added Successfully.',null);
                return response()->json($response,201);
            }else{
                $response = APIHelpers::createAPIResponse(false,500,'Internal Server Error.',null);
                return response()->json($response,500);
            }
        } catch (\Exception $e) {
            //return $e->getMessage();
            $response = APIHelpers::createAPIResponse(false,500,'Internal Server Error.',null);
            return response()->json($response,500);
        }
    }

    public function supplierDetails(Request $request){
        try {
            $check_exists_party = DB::table("parties")->where('id',$request->party_id)->pluck('id')->first();
            if($check_exists_party == null){
                $response = APIHelpers::createAPIResponse(true,404,'No Supplier Found.',null);
                return response()->json($response,404);
            }

            $party = DB::table("parties")->where('id',$request->party_id)->latest()->first();
            $response = APIHelpers::createAPIResponse(false,200,'',$party);
            return response()->json($response,200);
        } catch (\Exception $e) {
            //return $e->getMessage();
            $response = APIHelpers::createAPIResponse(false,500,'Internal Server Error.',null);
            return response()->json($response,500);
        }
    }

    public function supplierUpdate(Request $request){
        try {
            $check_exists_party = DB::table("parties")->where('id',$request->party_id)->pluck('id')->first();
            if($check_exists_party == null){
                $response = APIHelpers::createAPIResponse(true,404,'No Warehouse Found.',null);
                return response()->json($response,404);
            }

            // required
            $validator = Validator::make($request->all(), [
                'party_id' => 'required',
                'name' => 'required',
                'phone' => 'required',
                'status'=> 'required',
            ]);

            if ($validator->fails()) {
                $response = APIHelpers::createAPIResponse(true,400,$validator->errors(),null);
                return response()->json($response,400);
            }

            $parties = Party::find($request->party_id);
            $parties->name = $request->name;
            $parties->slug = Str::slug($request->name);
            $parties->phone = $request->phone;
            $parties->email = $request->email;
            $parties->address = $request->address;
            $parties->status = $request->status;
            $update_party = $parties->save();

            if($update_party){
                $response = APIHelpers::createAPIResponse(false,200,'Supplier Updated Successfully.',null);
                return response()->json($response,200);
            }else{
                $response = APIHelpers::createAPIResponse(true,400,'Supplier Updated Failed.',null);
                return response()->json($response,400);
            }
        } catch (\Exception $e) {
            //return $e->getMessage();
            $response = APIHelpers::createAPIResponse(false,500,'Internal Server Error.',null);
            return response()->json($response,500);
        }
    }

    public function supplierDelete(Request $request){
        try {
            $check_exists_party = DB::table("parties")->where('id',$request->party_id)->pluck('id')->first();
            if($check_exists_party == null){
                $response = APIHelpers::createAPIResponse(true,404,'No Supplier Found.',null);
                return response()->json($response,404);
            }

            $delete_party = DB::table("parties")->where('id',$request->party_id)->delete();
            if($delete_party)
            {
                $response = APIHelpers::createAPIResponse(false,200,'Supplier Successfully Soft Deleted.',null);
                return response()->json($response,200);
            }else{
                $response = APIHelpers::createAPIResponse(true,400,'Supplier Updated Failed.',null);
                return response()->json($response,400);
            }
        } catch (\Exception $e) {
            //return $e->getMessage();
            $response = APIHelpers::createAPIResponse(false,500,'Internal Server Error.',null);
            return response()->json($response,500);
        }
    }





    // pos customer
    public function posCustomerCreate(Request $request){
        try {
            // required
            $validator = Validator::make($request->all(), [
                'name' => 'required',
                'phone' => 'unique:parties,phone',
                'status'=> 'required',
            ]);

            if ($validator->fails()) {
                $response = APIHelpers::createAPIResponse(true,400,$validator->errors(),null);
                return response()->json($response,400);
            }

            $parties = new Party();
            $parties->type = 'customer';
            $parties->customer_type = 'POS Sale';
            $parties->name = $request->name;
            $parties->slug = Str::slug($request->name);
            $parties->phone = $request->phone;
            $parties->email = $request->email;
            $parties->address = $request->address;
            $parties->status = $request->status;
            $parties->save();
            $insert_id = $parties->id;

            if($insert_id){
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

                $response = APIHelpers::createAPIResponse(false,201,'POS Sale Customer Added Successfully.',null);
                return response()->json($response,201);
            }else{
                $response = APIHelpers::createAPIResponse(true,400,'POS Sale Customer Updated Failed.',null);
                return response()->json($response,400);
            }
        } catch (\Exception $e) {
            //return $e->getMessage();
            $response = APIHelpers::createAPIResponse(false,500,'Internal Server Error.',null);
            return response()->json($response,500);
        }
    }

    public function customerDetails(Request $request){
        try {
            $check_exists_party = DB::table("parties")->where('id',$request->party_id)->pluck('id')->first();
            if($check_exists_party == null){
                $response = APIHelpers::createAPIResponse(true,404,'No Warehouse Found.',null);
                return response()->json($response,404);
            }

            $party = DB::table("parties")->where('id',$request->party_id)->latest()->first();
            if($party === null)
            {
                $response = APIHelpers::createAPIResponse(true,404,'No Customer Found.',null);
                return response()->json($response,404);
            }else{
                $response = APIHelpers::createAPIResponse(false,200,'',$party);
                return response()->json($response,200);
            }
        } catch (\Exception $e) {
            //return $e->getMessage();
            $response = APIHelpers::createAPIResponse(false,500,'Internal Server Error.',null);
            return response()->json($response,500);
        }
    }

    public function customerUpdate(Request $request){
        try {
            $check_exists_party = DB::table("parties")->where('id',$request->party_id)->pluck('id')->first();
            if($check_exists_party == null){
                $response = APIHelpers::createAPIResponse(true,404,'No Customer Found.',null);
                return response()->json($response,404);
            }
            // required
            $validator = Validator::make($request->all(), [
                'party_id'=> 'required',
                'name' => 'required',
                'phone' => 'required',
                'status'=> 'required',
            ]);

            if ($validator->fails()) {
                $response = APIHelpers::createAPIResponse(true,400,$validator->errors(),null);
                return response()->json($response,400);
            }

            $parties = Party::find($request->party_id);
            //$parties->customer_type = $request->customer_type;
            $parties->name = $request->name;
            $parties->slug = Str::slug($request->name);
            $parties->phone = $request->phone;
            $parties->email = $request->email;
            $parties->address = $request->address;
            $parties->status = $request->status;
            $parties->initial_due = $request->initial_due;
            $update_party = $parties->save();

            if($update_party){

                // whole customer initial due
                if(($request->initial_due > 0) && ($parties->customer_type == 'Whole Sale')){
                    $coa = ChartOfAccount::where('head_name',$parties->name)->first();

                    $get_voucher_name = VoucherType::where('id',2)->pluck('name')->first();
                    $get_voucher_no = ChartOfAccountTransaction::where('voucher_type_id',2)->latest()->pluck('voucher_no')->first();
                    if(!empty($get_voucher_no)){
                        $get_voucher_name_str = $get_voucher_name."-";
                        $get_voucher = str_replace($get_voucher_name_str,"",$get_voucher_no);
                        $voucher_no = $get_voucher+1;
                    }else{
                        $voucher_no = 8000;
                    }
                    $final_voucher_no = $get_voucher_name.'-'.$voucher_no;

                    $date = date('Y-m-d');
                    $year = date('Y');
                    $month = date('m');
                    $date_time = date('Y-m-d H:i:s');
                    $user_id = Auth::user()->id;
                    //$store_id = $request->store_id;
                    //$warehouse_id = Store::where('id',$store_id)->pluck('warehouse_id')->first();
                    $warehouse_id = 6;

                    $check_exists_posting = ChartOfAccountTransaction::where('ref_id',$parties->id)
                        ->where('transaction_type','Initial Due')->first();

                    if(empty($check_exists_posting)){
                        $chart_of_account_transactions = new ChartOfAccountTransaction();
                        $chart_of_account_transactions->ref_id = $parties->id;
                        $chart_of_account_transactions->transaction_type = 'Initial Due';
                        $chart_of_account_transactions->user_id = $user_id;
                        $chart_of_account_transactions->warehouse_id = $warehouse_id;
                        $chart_of_account_transactions->store_id = NULL;
                        $chart_of_account_transactions->voucher_type_id = 2;
                        $chart_of_account_transactions->voucher_no = $final_voucher_no;
                        $chart_of_account_transactions->is_approved = 'approved';
                        $chart_of_account_transactions->transaction_date = $date;
                        $chart_of_account_transactions->transaction_date_time = $date_time;
                        $chart_of_account_transactions->save();
                        $chart_of_account_transactions_insert_id = $chart_of_account_transactions->id;

                        if($chart_of_account_transactions_insert_id) {

                            // customer account
                            $chart_of_account_transaction_details = new ChartOfAccountTransactionDetail();
                            $chart_of_account_transaction_details->warehouse_id = $warehouse_id;
                            $chart_of_account_transaction_details->store_id = NULL;
                            $chart_of_account_transaction_details->chart_of_account_transaction_id = $chart_of_account_transactions_insert_id;
                            $chart_of_account_transaction_details->chart_of_account_id = $coa->id;
                            $chart_of_account_transaction_details->chart_of_account_number = $coa->head_code;
                            $chart_of_account_transaction_details->chart_of_account_name = $coa->head_name;
                            $chart_of_account_transaction_details->chart_of_account_parent_name = $coa->parent_head_name;
                            $chart_of_account_transaction_details->chart_of_account_type = $coa->head_type;
                            $chart_of_account_transaction_details->debit = $request->initial_due;
                            $chart_of_account_transaction_details->credit = NULL;
                            $chart_of_account_transaction_details->description = 'Initial Due';
                            $chart_of_account_transaction_details->year = $year;
                            $chart_of_account_transaction_details->month = $month;
                            $chart_of_account_transaction_details->transaction_date = $date;
                            $chart_of_account_transaction_details->transaction_date_time = $date_time;
                            $chart_of_account_transaction_details->save();

                            // Account Receivable
                            $cash_chart_of_account_info = ChartOfAccount::where('head_name', 'Account Receivable')->first();
                            $chart_of_account_transaction_details = new ChartOfAccountTransactionDetail();
                            $chart_of_account_transaction_details->warehouse_id = $warehouse_id;
                            $chart_of_account_transaction_details->store_id = NULL;
                            $chart_of_account_transaction_details->chart_of_account_transaction_id = $chart_of_account_transactions_insert_id;
                            $chart_of_account_transaction_details->chart_of_account_id = $cash_chart_of_account_info->id;
                            $chart_of_account_transaction_details->chart_of_account_number = $cash_chart_of_account_info->head_code;
                            $chart_of_account_transaction_details->chart_of_account_name = 'Account Receivable';
                            $chart_of_account_transaction_details->chart_of_account_parent_name = $cash_chart_of_account_info->parent_head_name;
                            $chart_of_account_transaction_details->chart_of_account_type = $cash_chart_of_account_info->head_type;
                            $chart_of_account_transaction_details->debit = NULL;
                            $chart_of_account_transaction_details->credit = $request->initial_due;
                            $chart_of_account_transaction_details->description = 'Initial Due';
                            $chart_of_account_transaction_details->year = $year;
                            $chart_of_account_transaction_details->month = $month;
                            $chart_of_account_transaction_details->transaction_date = $date;
                            $chart_of_account_transaction_details->transaction_date_time = $date_time;
                            $chart_of_account_transaction_details->save();

                        }
                    }else{
                        $check_exists_posting->transaction_date = $date;
                        $check_exists_posting->transaction_date_time = $date_time;
                        $check_exists_posting->save();



                        // customer account
                        $chart_of_account_transaction_details = ChartOfAccountTransactionDetail::where('chart_of_account_name',$check_exists_posting->head_name)
                            ->where('chart_of_account_id',$check_exists_posting->id)->first();

                        $chart_of_account_transaction_details->debit = $request->initial_due;
                        $chart_of_account_transaction_details->transaction_date = $date;
                        $chart_of_account_transaction_details->transaction_date_time = $date_time;
                        $chart_of_account_transaction_details->save();

                        // Account Receivable
                        $chart_of_account_transaction_details = ChartOfAccountTransactionDetail::where('chart_of_account_name','Account Receivable')
                            ->where('chart_of_account_transaction_id',$check_exists_posting->id)->first();
                        $chart_of_account_transaction_details->credit = $request->initial_due;
                        $chart_of_account_transaction_details->transaction_date = $date;
                        $chart_of_account_transaction_details->transaction_date_time = $date_time;
                        $chart_of_account_transaction_details->save();


                    }

                }
                $response = APIHelpers::createAPIResponse(false,200,'Customer Updated Successfully.',null);
                return response()->json($response,200);
            }else{
                $response = APIHelpers::createAPIResponse(true,400,'Customer Updated Failed.',null);
                return response()->json($response,400);
            }
        } catch (\Exception $e) {
            //return $e->getMessage();
            $response = APIHelpers::createAPIResponse(false,500,'Internal Server Error.',null);
            return response()->json($response,500);
        }
    }

    public function customerDelete(Request $request){
        try {
            $check_exists_party = DB::table("parties")->where('id',$request->party_id)->pluck('id')->first();
            if($check_exists_party == null){
                $response = APIHelpers::createAPIResponse(true,404,'No Customer Found.',null);
                return response()->json($response,404);
            }

            //$delete_party = DB::table("parties")->where('id',$request->party_id)->delete();
            $soft_delete_party = Party::find($request->party_id);
            $soft_delete_party->status=0;
            $affected_row = $soft_delete_party->update();
            if($affected_row)
            {
                $response = APIHelpers::createAPIResponse(false,200,'Customer Successfully Soft Deleted.',null);
                return response()->json($response,200);
            }else{
                $response = APIHelpers::createAPIResponse(true,400,'Customer Updated Failed.',null);
                return response()->json($response,400);
            }
        } catch (\Exception $e) {
            //return $e->getMessage();
            $response = APIHelpers::createAPIResponse(false,500,'Internal Server Error.',null);
            return response()->json($response,500);
        }
    }



    // whole customer
    // customer
    public function wholeCustomerCreate(Request $request){

        try {
            // required
            $validator = Validator::make($request->all(), [
                'name' => 'required',
                'phone' => 'unique:parties,phone',
                'status'=> 'required',
            ]);

            if ($validator->fails()) {
                $response = APIHelpers::createAPIResponse(true,400,$validator->errors(),null);
                return response()->json($response,400);
            }

            $parties = new Party();
            $parties->type = 'customer';
            $parties->customer_type = 'Whole Sale';
            $parties->name = $request->name;
            $parties->slug = Str::slug($request->name);
            $parties->phone = $request->phone;
            $parties->email = $request->email;
            $parties->address = $request->address;
            $parties->status = $request->status;
            $parties->initial_due = $request->initial_due;
            $parties->save();
            $insert_id = $parties->id;

            if($insert_id){
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
                $head_name = $request->name.'('.$request->phone.')';

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



                // whole customer initial due
                if($request->initial_due > 0){
                    $get_voucher_name = VoucherType::where('id',2)->pluck('name')->first();
                    $get_voucher_no = ChartOfAccountTransaction::where('voucher_type_id',2)->latest()->pluck('voucher_no')->first();
                    if(!empty($get_voucher_no)){
                        $get_voucher_name_str = $get_voucher_name."-";
                        $get_voucher = str_replace($get_voucher_name_str,"",$get_voucher_no);
                        $voucher_no = $get_voucher+1;
                    }else{
                        $voucher_no = 8000;
                    }
                    $final_voucher_no = $get_voucher_name.'-'.$voucher_no;

                    $date = date('Y-m-d');
                    $year = date('Y');
                    $month = date('m');
                    $date_time = date('Y-m-d h:i:s');
                    $user_id = Auth::user()->id;
                    //$store_id = $request->store_id;
                    //$warehouse_id = Store::where('id',$store_id)->pluck('warehouse_id')->first();
                    $warehouse_id = 6;

                    $chart_of_account_transactions = new ChartOfAccountTransaction();
                    $chart_of_account_transactions->ref_id = $insert_id;
                    $chart_of_account_transactions->transaction_type = 'Initial Due';
                    $chart_of_account_transactions->user_id = $user_id;
                    $chart_of_account_transactions->warehouse_id = $warehouse_id;
                    $chart_of_account_transactions->store_id = NULL;
                    $chart_of_account_transactions->voucher_type_id = 2;
                    $chart_of_account_transactions->voucher_no = $final_voucher_no;
                    $chart_of_account_transactions->is_approved = 'approved';
                    $chart_of_account_transactions->transaction_date = $date;
                    $chart_of_account_transactions->transaction_date_time = $date_time;
                    $chart_of_account_transactions->save();
                    $chart_of_account_transactions_insert_id = $chart_of_account_transactions->id;

                    if($chart_of_account_transactions_insert_id) {

                        // customer account
                        $chart_of_account_transaction_details = new ChartOfAccountTransactionDetail();
                        $chart_of_account_transaction_details->warehouse_id = $warehouse_id;
                        $chart_of_account_transaction_details->store_id = NULL;
                        $chart_of_account_transaction_details->chart_of_account_transaction_id = $chart_of_account_transactions_insert_id;
                        $chart_of_account_transaction_details->chart_of_account_id = $coa->id;
                        $chart_of_account_transaction_details->chart_of_account_number = $coa->head_code;
                        $chart_of_account_transaction_details->chart_of_account_name = $coa->head_name;
                        $chart_of_account_transaction_details->chart_of_account_parent_name = $coa->parent_head_name;
                        $chart_of_account_transaction_details->chart_of_account_type = $coa->head_type;
                        $chart_of_account_transaction_details->debit = $request->initial_due;
                        $chart_of_account_transaction_details->credit = NULL;
                        $chart_of_account_transaction_details->description = 'Initial Due';
                        $chart_of_account_transaction_details->year = $year;
                        $chart_of_account_transaction_details->month = $month;
                        $chart_of_account_transaction_details->transaction_date = $date;
                        $chart_of_account_transaction_details->transaction_date_time = $date_time;
                        $chart_of_account_transaction_details->save();

                        // Account Receivable
                        $cash_chart_of_account_info = ChartOfAccount::where('head_name', 'Account Receivable')->first();
                        $chart_of_account_transaction_details = new ChartOfAccountTransactionDetail();
                        $chart_of_account_transaction_details->warehouse_id = $warehouse_id;
                        $chart_of_account_transaction_details->store_id = NULL;
                        $chart_of_account_transaction_details->chart_of_account_transaction_id = $chart_of_account_transactions_insert_id;
                        $chart_of_account_transaction_details->chart_of_account_id = $cash_chart_of_account_info->id;
                        $chart_of_account_transaction_details->chart_of_account_number = $cash_chart_of_account_info->head_code;
                        $chart_of_account_transaction_details->chart_of_account_name = 'Account Receivable';
                        $chart_of_account_transaction_details->chart_of_account_parent_name = $cash_chart_of_account_info->parent_head_name;
                        $chart_of_account_transaction_details->chart_of_account_type = $cash_chart_of_account_info->head_type;
                        $chart_of_account_transaction_details->debit = NULL;
                        $chart_of_account_transaction_details->credit = $request->initial_due;
                        $chart_of_account_transaction_details->description = 'Initial Due';
                        $chart_of_account_transaction_details->year = $year;
                        $chart_of_account_transaction_details->month = $month;
                        $chart_of_account_transaction_details->transaction_date = $date;
                        $chart_of_account_transaction_details->transaction_date_time = $date_time;
                        $chart_of_account_transaction_details->save();

                    }
                }
                $response = APIHelpers::createAPIResponse(false,201,'Warehouse Added Successfully.',null);
                return response()->json($response,201);
            }else{
                $response = APIHelpers::createAPIResponse(true,400,'Warehouse Updated Failed.',null);
                return response()->json($response,400);
            }


        } catch (\Exception $e) {
            //return $e->getMessage();
            $response = APIHelpers::createAPIResponse(false,500,'Internal Server Error.',null);
            return response()->json($response,500);
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
            ->paginate(12);
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
