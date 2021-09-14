<?php

namespace App\Http\Controllers\API;

use App\ChartOfAccount;
use App\ChartOfAccountTransaction;
use App\ChartOfAccountTransactionDetail;
use App\ExpenseCategory;
use App\Http\Controllers\Controller;
use App\Party;
use App\StoreExpense;
use App\TangibleAssets;
use App\User;
use App\VoucherType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;


class AccountController extends Controller
{
    public $successStatus = 200;
    public $authStatus = 401;
    public $failStatus = 402;
    public $ExistsStatus = 403;
    public $validationStatus = 404;

    // voucher type
    public function voucherTypeList(){
        $voucher_types = DB::table('voucher_types')->select('id','name','voucher_prefix','status')->orderBy('id','desc')->get();

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
            'voucher_prefix'=> 'required',
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
        $voucherType->voucher_prefix = $request->voucher_prefix;
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
            'voucher_prefix'=> 'required',
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
        $voucher_types->voucher_prefix = $request->voucher_prefix;
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

    // Expense Category
    public function expenseCategoryList(){
        $expense_categories = DB::table('expense_categories')->select('id','name','status')->orderBy('id','desc')->get();

        if($expense_categories)
        {
            $success['expense_category'] =  $expense_categories;
            return response()->json(['success'=>true,'response' => $success], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Expense Category List Found!'], $this->failStatus);
        }
    }

    public function expenseCategoryCreate(Request $request){

        $validator = Validator::make($request->all(), [
            'name' => 'required|unique:expense_categories,name',
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


        $expenseCategory = new ExpenseCategory();
        $expenseCategory->name = $request->name;
        $expenseCategory->status = $request->status;
        $expenseCategory->save();
        $insert_id = $expenseCategory->id;

        if($insert_id){
            return response()->json(['success'=>true,'response' => $expenseCategory], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'Expense Category Not Created Successfully!'], $this->failStatus);
        }
    }

    public function expenseCategoryEdit(Request $request){

        $validator = Validator::make($request->all(), [
            'expense_category_id'=> 'required',
            'name' => 'required|unique:expense_categories,name,'.$request->expense_category_id,
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

        $check_exists_expense_category = DB::table("expense_categories")->where('id',$request->expense_category_id)->pluck('id')->first();
        if($check_exists_expense_category == null){
            return response()->json(['success'=>false,'response'=>'No Expense Category Found!'], $this->failStatus);
        }

        $expense_category = ExpenseCategory::find($request->expense_category_id);
        $expense_category->name = $request->name;
        $expense_category->status = $request->status;
        $update_expense_category = $expense_category->save();

        if($update_expense_category){
            return response()->json(['success'=>true,'response' => $expense_category], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'Expense Category Not Created Successfully!'], $this->failStatus);
        }
    }

    public function expenseCategoryDelete(Request $request){
        $check_exists_expense_category = DB::table("expense_categories")->where('id',$request->expense_category_id)->pluck('id')->first();
        if($check_exists_expense_category == null){
            return response()->json(['success'=>false,'response'=>'No Expense Category Found!'], $this->failStatus);
        }

        //$delete_party = DB::table("expense_categories")->where('id',$request->expense_category_id)->delete();
        $soft_delete_expense_category = ExpenseCategory::find($request->expense_category_id);
        $soft_delete_expense_category->status=0;
        $affected_row = $soft_delete_expense_category->update();
        if($affected_row)
        {
            return response()->json(['success'=>true,'response' => 'Expense Category Successfully Soft Deleted!'], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Expense Category Deleted!'], $this->failStatus);
        }
    }

    // Store Expense
    public function storeExpenseList(){
        $store_expenses = DB::table('store_expenses')
            ->leftJoin('expense_categories','store_expenses.expense_category_id','=','expense_categories.id')
            ->leftJoin('stores','store_expenses.store_id','=','stores.id')
            ->select(
                'store_expenses.id',
                'store_expenses.expense_category_id',
                'expense_categories.name as expense_category_name',
                'store_expenses.store_id',
                'stores.name as store_name',
                'store_expenses.amount',
                'store_expenses.status'
            )
            ->orderBy('id','desc')->get();

        if($store_expenses)
        {
            $success['store_expense'] =  $store_expenses;
            return response()->json(['success'=>true,'response' => $success], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Store Expense List Found!'], $this->failStatus);
        }
    }

    public function storeExpenseCreate(Request $request){

        $validator = Validator::make($request->all(), [
            'expense_category_id' => 'required',
            'store_id' => 'required',
            'amount' => 'required',
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


        $storeExpense = new StoreExpense();
        $storeExpense->expense_category_id = $request->expense_category_id;
        $storeExpense->store_id = $request->store_id;
        $storeExpense->amount = $request->amount;
        $storeExpense->status = $request->status;
        $storeExpense->save();
        $insert_id = $storeExpense->id;

        if($insert_id){
            return response()->json(['success'=>true,'response' => $storeExpense], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'Store Expense Not Created Successfully!'], $this->failStatus);
        }
    }

    public function storeExpenseEdit(Request $request){

        $validator = Validator::make($request->all(), [
            'store_expense_id'=> 'required',
            'expense_category_id' => 'required',
            'store_id' => 'required',
            'amount' => 'required',
        ]);

        if ($validator->fails()) {
            $response = [
                'success' => false,
                'data' => 'Validation Error.',
                'message' => $validator->errors()
            ];

            return response()->json($response, $this->validationStatus);
        }

        $check_exists_expense_category = DB::table("store_expenses")->where('id',$request->expense_category_id)->pluck('id')->first();
        if($check_exists_expense_category == null){
            return response()->json(['success'=>false,'response'=>'No Store Expense Found!'], $this->failStatus);
        }

        $storeExpense = StoreExpense::find($request->store_expense_id);
        $storeExpense->expense_category_id = $request->expense_category_id;
        $storeExpense->store_id = $request->store_id;
        $storeExpense->amount = $request->amount;
        $storeExpense->status = $request->status;
        $update_store_expense = $storeExpense->save();

        if($update_store_expense){
            return response()->json(['success'=>true,'response' => $storeExpense], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'Store Expense Not Updated Successfully!'], $this->failStatus);
        }
    }

    public function storeExpenseDelete(Request $request){
        $check_exists_store_expense = DB::table("store_expenses")->where('id',$request->store_expense_id)->pluck('id')->first();
        if($check_exists_store_expense == null){
            return response()->json(['success'=>false,'response'=>'No Store Expense Found!'], $this->failStatus);
        }

        //$delete_expense = DB::table("store_expenses")->where('id',$request->expense_category_id)->delete();
        $soft_delete_store_expense = StoreExpense::find($request->store_expense_id);
        $soft_delete_store_expense->status=0;
        $affected_row = $soft_delete_store_expense->update();
        if($affected_row)
        {
            return response()->json(['success'=>true,'response' => 'Store Expense Successfully Soft Deleted!'], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Store Expense Deleted!'], $this->failStatus);
        }
    }

    public function chartOfAccountList(){

        $chart_of_accounts = DB::table('chart_of_accounts')
            ->select('id','head_code','head_name','parent_head_name','user_bank_account_no','head_level','is_active','is_transaction','is_general_ledger','head_type')
            ->get();


        if($chart_of_accounts)
        {
            $success['chart_of_accounts'] =  $chart_of_accounts;
            return response()->json(['success'=>true,'response' => $success], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Chart Of Accounts List Found!'], $this->failStatus);
        }
    }

    public function chartOfAccountListByName(Request $request){

        if($request->head_name == ''){
            $chart_of_accounts = DB::table('chart_of_accounts')
                ->select('id','head_code','head_name','parent_head_name','user_bank_account_no','head_level','is_active','is_transaction','is_general_ledger','head_type')
                ->where('head_level',0)
                ->get();
        }else{
            $chart_of_accounts = DB::table('chart_of_accounts')
                ->select('id','head_code','head_name','parent_head_name','user_bank_account_no','head_level','is_active','is_transaction','is_general_ledger','head_type')
                ->where('parent_head_name',$request->head_name)
                ->get();
        }


        if($chart_of_accounts)
        {
            $success['chart_of_accounts'] =  $chart_of_accounts;
            return response()->json(['success'=>true,'response' => $success], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Chart Of Accounts List Found!'], $this->failStatus);
        }
    }

    public function child($head_name){
        $chart_of_accounts = DB::table('chart_of_accounts')
            ->select(
                'id',
                'head_code',
                'head_name',
                'parent_head_name',
                'user_bank_account_no',
                'head_level',
                'is_active',
                'is_transaction',
                'is_general_ledger',
                'head_type'
            )
            ->where('parent_head_name',$head_name)
            //->orderBy('id','desc')
            ->get();
    }

    public function chartOfAccountRecursiveList(Request $request){


        $result = Array();
        $chart_of_accounts = DB::table('chart_of_accounts')
            ->select(
                'id',
                'head_code',
                'head_name',
                'parent_head_name',
                'user_bank_account_no',
                'head_level',
                'is_active',
                'is_transaction',
                'is_general_ledger',
                'head_type'
            )
            ->where('head_level',0)
            //->orderBy('id','desc')
            ->get();
//        foreach ($categories as $category){
//            $subcategories = ServicesSubCategory::where('category_id', $category->id)->get();
//            foreach ($subcategories as $subcategory){
//                $services = ServiceManage::where('sub_category',$subcategory->id)->get();
//                $subcategory->service = (sizeof($services) > 0) ? $services : false;
//            }
//
//            $category->subcategories = (sizeof($subcategories) > 0) ? $subcategories : false;
//            array_push($result, $category);
//        }

        foreach ($chart_of_accounts as $chart_of_account){


            $coa['id'] = $chart_of_account->id;
            $coa['head_code'] = $chart_of_account->head_code;
            $coa['head_name'] = $chart_of_account->head_name;
            $coa['parent_head_name'] = $chart_of_account->parent_head_name;
            $coa['head_type'] = $chart_of_account->head_type;
            $coa['head_level'] = $chart_of_account->head_level;
            $coa['is_active'] = $chart_of_account->is_active;
            $coa['is_transaction'] = $chart_of_account->is_transaction;
            $coa['is_general_ledger'] = $chart_of_account->is_general_ledger;
            $coa['user_bank_account_no'] = $chart_of_account->user_bank_account_no;


            $child = ChartOfAccount::where('parent_head_name',$chart_of_account->head_name)
                //->where('parent_head_name',$chart_of_account->head_code)
                ->get();

            if(count($child) > 0){
                $this->child($chart_of_account->head_name);
            }

            array_push($result, $coa);
        }

        //$success['question'] =
        return response()->json(['success'=> $result], $this-> successStatus);
    }






    public function chartOfAccountActiveList(){
        $chart_of_accounts = DB::table('chart_of_accounts')
            ->select('id','head_code','head_name','parent_head_name','head_type','head_level','is_active','is_transaction','is_general_ledger')
            ->where('is_active',1)
            //->orderBy('id','desc')
            ->get();

        if($chart_of_accounts)
        {
            $success['chart_of_accounts'] =  $chart_of_accounts;
            return response()->json(['success'=>true,'response' => $success], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Chart Of Accounts List Found!'], $this->failStatus);
        }
    }

    public function chartOfAccountIsTransactionList(){
        $chart_of_accounts = DB::table('chart_of_accounts')
            ->select('id','head_code','head_name','parent_head_name','head_type','head_level','is_active','is_transaction','is_general_ledger')
            ->where('is_transaction',1)
            //->orderBy('id','desc')
            ->get();

        if($chart_of_accounts)
        {
            $success['chart_of_accounts'] =  $chart_of_accounts;
            return response()->json(['success'=>true,'response' => $success], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Chart Of Accounts List Found!'], $this->failStatus);
        }
    }

    public function chartOfAccountIsCashBookList(){
        $chart_of_accounts = DB::table('chart_of_accounts')
            ->select('id','head_code','head_name','parent_head_name','head_type','head_level','is_active','is_transaction','is_general_ledger')
            ->where('is_general_ledger',1)
            //->orderBy('id','desc')
            ->get();

        if($chart_of_accounts)
        {
            $success['chart_of_accounts'] =  $chart_of_accounts;
            return response()->json(['success'=>true,'response' => $success], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Chart Of Accounts List Found!'], $this->failStatus);
        }
    }

    public function chartOfAccountIsGeneralLedgerList(){
        $chart_of_accounts = DB::table('chart_of_accounts')
            ->select('id','head_code','head_name','parent_head_name','head_type','head_level','is_active','is_transaction','is_general_ledger')
            ->where('is_general_ledger',1)
            //->orderBy('id','desc')
            ->get();

        if($chart_of_accounts)
        {
            $success['chart_of_accounts'] =  $chart_of_accounts;
            return response()->json(['success'=>true,'response' => $success], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Chart Of Accounts List Found!'], $this->failStatus);
        }
    }

    public function chartOfAccountDetails(Request $request){
        $validator = Validator::make($request->all(), [
            'head_name'=> 'required',
        ]);

        if ($validator->fails()) {
            $response = [
                'success' => false,
                'data' => 'Validation Error.',
                'message' => $validator->errors()
            ];

            return response()->json($response, $this-> validationStatus);
        }

        $chart_of_account_details = DB::table('chart_of_accounts')
            ->select('id','head_code','head_name','parent_head_name','head_type','head_level','is_active','is_transaction','is_general_ledger')
            ->where('head_name',$request->head_name)
            ->orderBy('id','desc')
            ->get();

        if($chart_of_account_details)
        {
            $success['chart_of_account__details'] =  $chart_of_account_details;
            return response()->json(['success'=>true,'response' => $success], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Chart Of Accounts Details Found!'], $this->failStatus);
        }
    }

    public function chartOfAccountGenerateHeadCode(Request $request){
        $validator = Validator::make($request->all(), [
            'head_name'=> 'required',
        ]);

        if ($validator->fails()) {
            $response = [
                'success' => false,
                'data' => 'Validation Error.',
                'message' => $validator->errors()
            ];

            return response()->json($response, $this-> validationStatus);
        }

        $n = 1;
        $chart_of_account_head_code = DB::table('chart_of_accounts')
            ->where('parent_head_name',$request->head_name)
            ->latest('id')
            ->pluck('head_code')
            ->first();

        if($chart_of_account_head_code != NULL){
            $head_code = $chart_of_account_head_code + 1;
        }else{
            $current_head_code = DB::table('chart_of_accounts')
                ->where('head_name',$request->head_name)
                ->latest('id')
                ->pluck('head_code')
                ->first();

            if($current_head_code){
                $head_code = $current_head_code . "0" . $n;
            }
        }

        if($head_code)
        {

            $success['head_code'] =  $head_code;
            return response()->json(['success'=>true,'response' => $success], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Head Code Found!'], $this->failStatus);
        }
    }

    public function chartOfAccountParentHeadDetails(Request $request){
        $validator = Validator::make($request->all(), [
            'parent_head_name'=> 'required',
        ]);

        if ($validator->fails()) {
            $response = [
                'success' => false,
                'data' => 'Validation Error.',
                'message' => $validator->errors()
            ];

            return response()->json($response, $this-> validationStatus);
        }

        $chart_of_account_parent_head_details = DB::table('chart_of_accounts')
            ->select('id','head_code','head_name','parent_head_name','head_type','head_level','is_active','is_transaction','is_general_ledger')
            ->where('head_name',$request->parent_head_name)
            ->orderBy('id','desc')
            ->get();

        if($chart_of_account_parent_head_details)
        {
            $success['chart_of_account_parent_head_details'] =  $chart_of_account_parent_head_details;
            return response()->json(['success'=>true,'response' => $success], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Chart Of Accounts Parent Head Details Found!'], $this->failStatus);
        }
    }

    public function chartOfAccountCreate(Request $request){

        $validator = Validator::make($request->all(), [
            'head_code'=> 'required',
            'head_name' => 'required|unique:chart_of_accounts,head_name',
            'parent_head_name'=> 'required',
            'head_type'=> 'required',
            'head_level'=> 'required',
            'is_active'=> 'required',
            'is_transaction'=> 'required',
            'is_general_ledger'=> 'required',
        ]);

        if ($validator->fails()) {
            $response = [
                'success' => false,
                'data' => 'Validation Error.',
                'message' => $validator->errors()
            ];

            return response()->json($response, $this-> validationStatus);
        }

        $n = 1;
        $chart_of_account_head_code = DB::table('chart_of_accounts')
            ->where('parent_head_name',$request->parent_head_name)
            ->latest('id')
            ->pluck('head_code')
            ->first();

        if($chart_of_account_head_code != NULL){
            $head_code = $chart_of_account_head_code + 1;
        }else{
            $current_head_code = DB::table('chart_of_accounts')
                ->where('head_name',$request->parent_head_name)
                ->latest('id')
                ->pluck('head_code')
                ->first();

            if($current_head_code){
                $head_code = $current_head_code . "0" . $n;
            }
        }


        $ref_id = NULL;
        if($request->parent_head_name == 'Account Receivable'){
            $parties = new Party();
            $parties->type = 'customer';
            $parties->customer_type = 'POS Sale';
            $parties->name = $request->name;
            $parties->slug = Str::slug($request->name);
            $parties->phone = '01700000000';
            $parties->email = NULL;
            $parties->address = NULL;
            $parties->status = 1;
            $parties->save();
            $insert_id = $parties->id;

            if($insert_id){
                $ref_id = $insert_id;
                if($request->type == 'customer'){
                    $user_data['name'] = $request->name;
                    $user_data['email'] = NULL;
                    $user_data['phone'] = '01700000000';
                    $user_data['password'] = Hash::make(123456);
                    $user_data['party_id'] = $insert_id;
                    $user = User::create($user_data);
                    // first create customer role, then bellow code enable
                    $user->assignRole('customer');
                }
            }
        }

        if($request->parent_head_name == 'Account Payable'){
            $parties = new Party();
            $parties->type = 'supplier';
            $parties->customer_type = NULL;
            $parties->name = $request->head_name;
            $parties->slug = Str::slug($request->head_name);
            $parties->phone = '01700000000';
            $parties->email = NULL;
            $parties->address = NULL;
            $parties->status = 1;
            $parties->save();
            $insert_id = $parties->id;

            if($insert_id){
                $ref_id = $insert_id;
            }
        }


        $chart_of_accounts = new ChartOfAccount();
        $chart_of_accounts->head_code = $head_code;
        $chart_of_accounts->head_name = $request->head_name;
        $chart_of_accounts->parent_head_name = $request->parent_head_name;
        $chart_of_accounts->head_type = $request->head_type;
        $chart_of_accounts->head_level = $request->head_level;
        $chart_of_accounts->is_active = $request->is_active;
        $chart_of_accounts->is_transaction = $request->is_transaction;
        $chart_of_accounts->is_general_ledger = $request->is_general_ledger;
        $chart_of_accounts->ref_id = $ref_id;
        $chart_of_accounts->user_bank_account_no = NULL;
        $chart_of_accounts->save();
        $insert_id = $chart_of_accounts->id;

        if($insert_id){
            return response()->json(['success'=>true,'response' => $chart_of_accounts], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'Chart Of Accounts Not Created Successfully!'], $this->failStatus);
        }
    }

    public function chartOfAccountEdit(Request $request){

        $validator = Validator::make($request->all(), [
            'chart_of_account_id'=> 'required',
            //'head_code'=> 'required',
            //'head_name' => 'required|unique:chart_of_accounts,head_name,'.$request->chart_of_account_id,
            //'parent_head_name'=> 'required',
            //'head_type'=> 'required',
            //'head_level'=> 'required',
            'is_active'=> 'required',
            'is_transaction'=> 'required',
            'is_general_ledger'=> 'required',
        ]);

        if ($validator->fails()) {
            $response = [
                'success' => false,
                'data' => 'Validation Error.',
                'message' => $validator->errors()
            ];

            return response()->json($response, $this->validationStatus);
        }

        $check_exists_chart_of_account = DB::table("chart_of_accounts")->where('id',$request->chart_of_account_id)->pluck('id')->first();
        if($check_exists_chart_of_account == null){
            return response()->json(['success'=>false,'response'=>'No Chart Of Account Found!'], $this->failStatus);
        }

        $chart_of_accounts = ChartOfAccount::find($request->chart_of_account_id);
        //$chart_of_accounts->head_code = $request->head_code;
        //$chart_of_accounts->head_name = $request->head_name;
        //$chart_of_accounts->parent_head_name = $request->parent_head_name;
        //$chart_of_accounts->head_type = $request->head_type;
        //$chart_of_accounts->head_level = $request->head_level;
        $chart_of_accounts->is_active = $request->is_active;
        $chart_of_accounts->is_transaction = $request->is_transaction;
        $chart_of_accounts->is_general_ledger = $request->is_general_ledger;
        $update_chart_of_account = $chart_of_accounts->save();

        if($update_chart_of_account){
            return response()->json(['success'=>true,'response' => $chart_of_accounts], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'Chart Of Account Not Created Successfully!'], $this->failStatus);
        }
    }

    public function chartOfAccountDelete(Request $request){
        $check_exists_chart_of_account = DB::table("chart_of_accounts")->where('id',$request->chart_of_account_id)->pluck('id')->first();
        if($check_exists_chart_of_account == null){
            return response()->json(['success'=>false,'response'=>'No chart_of_account Found!'], $this->failStatus);
        }

        //$delete_party = DB::table("product_brands")->where('id',$request->product_brand_id)->delete();
        $soft_delete_chart_of_account = ChartOfAccount::find($request->chart_of_account_id);
        $soft_delete_chart_of_account->is_active=0;
        $affected_row = $soft_delete_chart_of_account->update();
        if($affected_row)
        {
            return response()->json(['success'=>true,'response' => 'Chart Of Account Successfully Soft Deleted!'], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Chart Of Account Deleted!'], $this->failStatus);
        }
    }

    public function chartOfAccountTransactionList(){
        $user = User::find(Auth::id());
        $user_role = $user->getRoleNames()[0];
        $warehouse_id = $user->warehouse_id;
        $store_id = $user->store_id;

        //return response()->json(['success'=>true,'response' => $store_id], $this->successStatus);
        if($user_role == 'admin'){
            $chart_of_account_transactions = DB::table('chart_of_account_transactions')
                ->join('voucher_types','chart_of_account_transactions.voucher_type_id','voucher_types.id')
                ->select(
                    'chart_of_account_transactions.id',
                    'chart_of_account_transactions.voucher_type_id',
                    'voucher_types.name as voucher_type_name',
                    'chart_of_account_transactions.voucher_no',
                    'chart_of_account_transactions.is_approved',
                    'chart_of_account_transactions.transaction_date',
                    'chart_of_account_transactions.transaction_date_time'
                )
                ->orderBy('id','desc')
                ->get();
        }elseif($store_id != null){
            $chart_of_account_transactions = DB::table('chart_of_account_transactions')
                ->join('voucher_types','chart_of_account_transactions.voucher_type_id','voucher_types.id')
                ->where('chart_of_account_transactions.store_id',$store_id)
                ->select(
                    'chart_of_account_transactions.id',
                    'chart_of_account_transactions.voucher_type_id',
                    'voucher_types.name as voucher_type_name',
                    'chart_of_account_transactions.voucher_no',
                    'chart_of_account_transactions.is_approved',
                    'chart_of_account_transactions.transaction_date',
                    'chart_of_account_transactions.transaction_date_time'
                )
                ->orderBy('id','desc')
                ->get();
        }elseif($warehouse_id != null){
            $chart_of_account_transactions = DB::table('chart_of_account_transactions')
                ->join('voucher_types','chart_of_account_transactions.voucher_type_id','voucher_types.id')
                ->where('chart_of_account_transactions.warehouse_id',$warehouse_id)
                ->select(
                    'chart_of_account_transactions.id',
                    'chart_of_account_transactions.voucher_type_id',
                    'voucher_types.name as voucher_type_name',
                    'chart_of_account_transactions.voucher_no',
                    'chart_of_account_transactions.is_approved',
                    'chart_of_account_transactions.transaction_date',
                    'chart_of_account_transactions.transaction_date_time'
                )
                ->orderBy('id','desc')
                ->get();
        }else{
            return response()->json(['success'=>false,'response'=>'Something Went Wrong!'], $this->failStatus);
        }


        if($chart_of_account_transactions)
        {
            $success['chart_of_account_transactions'] =  $chart_of_account_transactions;
            return response()->json(['success'=>true,'response' => $success], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Chart Of Account Transactions List Found!'], $this->failStatus);
        }
    }

    public function chartOfAccountTransactionDetails(Request $request){
        $validator = Validator::make($request->all(), [
            'chart_of_account_transaction_id'=> 'required',
        ]);

        if ($validator->fails()) {
            $response = [
                'success' => false,
                'data' => 'Validation Error.',
                'message' => $validator->errors()
            ];

            return response()->json($response, $this-> validationStatus);
        }

        $chart_of_account_transaction_details = DB::table('chart_of_account_transaction_details')
            ->select(
                'chart_of_account_transaction_details.id',
                'chart_of_account_transaction_details.chart_of_account_transaction_id',
                'chart_of_account_transaction_details.chart_of_account_id',
                'chart_of_account_transaction_details.chart_of_account_number',
                'chart_of_account_transaction_details.chart_of_account_name',
                'chart_of_account_transaction_details.chart_of_account_parent_name',
                'chart_of_account_transaction_details.chart_of_account_type',
                'chart_of_account_transaction_details.debit',
                'chart_of_account_transaction_details.credit',
                'chart_of_account_transaction_details.description',
                'chart_of_account_transaction_details.transaction_date',
                'chart_of_account_transaction_details.transaction_date_time'
            )
            ->where('chart_of_account_transaction_details.chart_of_account_transaction_id',$request->chart_of_account_transaction_id)
            ->orderBy('chart_of_account_transaction_details.id','desc')
            ->get();

        if($chart_of_account_transaction_details)
        {
            $success['chart_of_account_transaction_details'] =  $chart_of_account_transaction_details;
            return response()->json(['success'=>true,'response' => $success], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Chart Of Accounts Transaction Details Found!'], $this->failStatus);
        }
    }

    public function chartOfAccountTransactionCreate(Request $request){

        $validator = Validator::make($request->all(), [
            'voucher_type_id'=> 'required',
            'date'=> 'required',
            //'chart_of_account_name'=> 'required',
            //'debit'=> 'required',
            //'credit'=> 'required',
            //'description'=> 'required',
        ]);

        if ($validator->fails()) {
            $response = [
                'success' => false,
                'data' => 'Validation Error.',
                'message' => $validator->errors()
            ];

            return response()->json($response, $this-> validationStatus);
        }

        $user_id = Auth::user()->id;
        //$transaction_date = date('Y-m-d');
        //$year = date('Y');
        //$month = date('m');
        $transaction_date = $request->date;
        $month = date('m', strtotime($request->date));
        $year = date('Y', strtotime($request->date));
        $transaction_date_time = date('Y-m-d H:i:s');

        $get_voucher_name = VoucherType::where('id',$request->voucher_type_id)->pluck('name')->first();
        $get_voucher_no = ChartOfAccountTransaction::where('voucher_type_id',$request->voucher_type_id)->latest()->pluck('voucher_no')->first();
        if(!empty($get_voucher_no)){
            $get_voucher_name_str = $get_voucher_name."-";
            $get_voucher = str_replace($get_voucher_name_str,"",$get_voucher_no);
            $voucher_no = $get_voucher+1;
        }else{
            $voucher_no = 8000;
        }
        $final_voucher_no = $get_voucher_name.'-'.$voucher_no;

        if($request->store_id != 0){
            $store_id = $request->store_id;
        }else{
            $store_id = NULL;
        }


        $chart_of_account_transactions = new ChartOfAccountTransaction();
        $chart_of_account_transactions->user_id = $user_id;
        //$chart_of_account_transactions->store_id = isset($request->store_id) ? $request->store_id : NULL;
        $chart_of_account_transactions->warehouse_id = 6;
        $chart_of_account_transactions->store_id = $store_id;
        $chart_of_account_transactions->voucher_type_id = $request->voucher_type_id;
        $chart_of_account_transactions->voucher_no = $final_voucher_no;
        $chart_of_account_transactions->is_approved = 'approved';
        $chart_of_account_transactions->transaction_date = $transaction_date;
        $chart_of_account_transactions->transaction_date_time = $transaction_date_time;
        $chart_of_account_transactions->save();
        $insert_id = $chart_of_account_transactions->id;

        if($insert_id){
            foreach ($request->transactions as $data){
                $debit = NULL;
                $credit = NULL;
                $debit_or_credit = $data['debit_or_credit'];
                if($debit_or_credit == 'debit'){
                    $debit = $data['amount'];
                }
                if($debit_or_credit == 'credit'){
                    $credit = $data['amount'];
                }

                $chart_of_account_info = ChartOfAccount::where('head_name',$data['chart_of_account_name']['head_name'])->first();

                $chart_of_account_transaction_details = new ChartOfAccountTransactionDetail();
                $chart_of_account_transaction_details->warehouse_id = 6;
                $chart_of_account_transaction_details->store_id = $store_id;
                $chart_of_account_transaction_details->chart_of_account_transaction_id = $insert_id;
                $chart_of_account_transaction_details->chart_of_account_id = $chart_of_account_info->id;
                $chart_of_account_transaction_details->chart_of_account_number = $chart_of_account_info->head_code;
                $chart_of_account_transaction_details->chart_of_account_name = $data['chart_of_account_name']['head_name'];
                $chart_of_account_transaction_details->chart_of_account_parent_name = $chart_of_account_info->parent_head_name;
                $chart_of_account_transaction_details->chart_of_account_type = $chart_of_account_info->head_type;
                $chart_of_account_transaction_details->debit = $debit;
                $chart_of_account_transaction_details->credit = $credit;
                $chart_of_account_transaction_details->description = $data['description'];
                $chart_of_account_transaction_details->year = $year;
                $chart_of_account_transaction_details->month = $month;
                $chart_of_account_transaction_details->transaction_date = $transaction_date;
                $chart_of_account_transaction_details->transaction_date_time = $transaction_date_time;
                $chart_of_account_transaction_details->save();
            }
            return response()->json(['success'=>true,'response' => $chart_of_account_transactions], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'Chart Of Account Transactions Not Created Successfully!'], $this->failStatus);
        }
    }

    public function chartOfAccountTransactionEdit(Request $request){

        $validator = Validator::make($request->all(), [
            'chart_of_account_transaction_id'=> 'required',
            'voucher_type_id'=> 'required',
            //'chart_of_account_name'=> 'required',
            //'debit'=> 'required',
            //'credit'=> 'required',
            //'description'=> 'required',
            //'chart_of_account_transaction_detail_id'=> 'required',
        ]);

        if ($validator->fails()) {
            $response = [
                'success' => false,
                'data' => 'Validation Error.',
                'message' => $validator->errors()
            ];

            return response()->json($response, $this-> validationStatus);
        }

        $user_id = Auth::user()->id;

        $transaction_date = $request->date;
        $month = date('m', strtotime($request->date));
        $year = date('Y', strtotime($request->date));
        $transaction_date_time = date('Y-m-d H:i:s');

//        $get_voucher_name = VoucherType::where('id',$request->voucher_type_id)->pluck('name')->first();
//        $get_voucher_no = ChartOfAccountTransaction::where('voucher_type_id',$request->voucher_type_id)->latest()->pluck('voucher_no')->first();
//        if(!empty($get_voucher_no)){
//            $get_voucher_name_str = $get_voucher_name."-";
//            $get_voucher = str_replace($get_voucher_name_str,"",$get_voucher_no);
//            $voucher_no = $get_voucher+1;
//        }else{
//            $voucher_no = 8000;
//        }
//        $final_voucher_no = $get_voucher_name.'-'.$voucher_no;

        if($request->store_id != 0){
            $store_id = $request->store_id;
        }else{
            $store_id = NULL;
        }


        $chart_of_account_transactions = ChartOfAccountTransaction::find($request->chart_of_account_transaction_id);
        $chart_of_account_transactions->user_id = $user_id;
        $chart_of_account_transactions->store_id = $store_id;
        $chart_of_account_transactions->voucher_type_id = $request->voucher_type_id;
        //$chart_of_account_transactions->voucher_no = $final_voucher_no;
        $chart_of_account_transactions->transaction_date = $transaction_date;
        $chart_of_account_transactions->transaction_date_time = $transaction_date_time;
        $chart_of_account_transactions->save();
        $insert_id = $chart_of_account_transactions->id;

        if($insert_id){
            foreach ($request->transactions as $data){

                $debit = NULL;
                $credit = NULL;
                $debit_or_credit = $data['debit_or_credit'];
                if($debit_or_credit == 'debit'){
                    $debit = $data['amount'];
                }
                if($debit_or_credit == 'credit'){
                    $credit = $data['amount'];
                }

                $chart_of_account_info = ChartOfAccount::where('head_name',$data['chart_of_account_name'])->first();

                $chart_of_account_transaction_details = ChartOfAccountTransactionDetail::find($data['chart_of_account_transaction_detail_id']);
                $chart_of_account_transaction_details->chart_of_account_id = $chart_of_account_info->id;
                $chart_of_account_transaction_details->chart_of_account_number = $chart_of_account_info->head_code;
                $chart_of_account_transaction_details->chart_of_account_name = $data['chart_of_account_name'];
                $chart_of_account_transaction_details->chart_of_account_parent_name = $chart_of_account_info->parent_head_name;
                $chart_of_account_transaction_details->chart_of_account_type = $chart_of_account_info->head_type;
                $chart_of_account_transaction_details->debit = $debit;
                $chart_of_account_transaction_details->credit = $credit;
                $chart_of_account_transaction_details->description = $data['description'];
                $chart_of_account_transaction_details->year = $year;
                $chart_of_account_transaction_details->month = $month;
                $chart_of_account_transaction_details->transaction_date = $transaction_date;
                $chart_of_account_transaction_details->transaction_date_time = $transaction_date_time;
                $chart_of_account_transaction_details->save();
            }
            return response()->json(['success'=>true,'response' => $chart_of_account_transactions], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'Chart Of Account Transactions Not Updated Successfully!'], $this->failStatus);
        }
    }

    public function chartOfAccountTransactionDelete(Request $request){
        $check_exists_chart_of_account = DB::table("chart_of_account_transactions")->where('id',$request->chart_of_account_transaction_id)->pluck('id')->first();
        if($check_exists_chart_of_account == null){
            return response()->json(['success'=>false,'response'=>'No Chart Of Account Transaction Found!'], $this->failStatus);
        }

        $delete_chart_of_account_transaction = DB::table("chart_of_account_transactions")->where('id',$request->chart_of_account_transaction_id)->delete();
        DB::table("chart_of_account_transaction_details")->where('chart_of_account_transaction_id',$request->chart_of_account_transaction_id)->delete();

        if($delete_chart_of_account_transaction)
        {
            return response()->json(['success'=>true,'response' => 'Chart Of Account Transaction Successfully Soft Deleted!'], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Chart Of Account Transaction Deleted!'], $this->failStatus);
        }
    }

    public function ledger(Request $request){
        $validator = Validator::make($request->all(), [
            'chart_of_account_name'=> 'required',
            'from_date'=> 'required',
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


//        $gl_pre_valance_data = DB::table('chart_of_account_transaction_details')
//            ->select('chart_of_account_name', DB::raw('SUM(debit) as debit, SUM(credit) as credit'))
//            ->where('transaction_date', '<',$request->from_date)
//            ->where('chart_of_account_name',$request->chart_of_account_name)
//            ->groupBy('chart_of_account_name')
//            ->first();
//        return response()->json(['success'=>true,'response' => $gl_pre_valance_data], $this->successStatus);





        $store_id = $request->store_id;
        $chart_of_account_name = $request->chart_of_account_name;
        $from_date = $request->from_date;
        $to_date = $request->to_date;


        if($store_id != 0){
            $gl_pre_valance_data = DB::table('chart_of_account_transaction_details')
                ->join('chart_of_account_transactions','chart_of_account_transaction_details.chart_of_account_transaction_id','=','chart_of_account_transactions.id')
                ->select('chart_of_account_transaction_details.chart_of_account_name', DB::raw('SUM(chart_of_account_transaction_details.debit) as debit, SUM(chart_of_account_transaction_details.credit) as credit'))
                ->where('chart_of_account_transaction_details.transaction_date', '<',$from_date)
                ->where('chart_of_account_transaction_details.chart_of_account_name',$chart_of_account_name)
                ->where('chart_of_account_transactions.store_id',$store_id)
                ->groupBy('chart_of_account_transaction_details.chart_of_account_name')
                ->first();
        }else{
            $gl_pre_valance_data = DB::table('chart_of_account_transaction_details')
                ->select('chart_of_account_name', DB::raw('SUM(debit) as debit, SUM(credit) as credit'))
                ->where('transaction_date', '<',$request->from_date)
                ->where('chart_of_account_name',$request->chart_of_account_name)
                ->groupBy('chart_of_account_name')
                ->first();

        }

//        dd($gl_pre_valance_data);

        $PreBalance=0;
        $preDebCre = 'De/Cr';
        $pre_debit = 0;
        $pre_credit = 0;
        if(!empty($gl_pre_valance_data))
        {
            //echo 'ok';exit;
            $pre_debit = $gl_pre_valance_data->debit == NULL ? 0 : $gl_pre_valance_data->debit;
            $pre_credit = $gl_pre_valance_data->credit == NULL ? 0 : $gl_pre_valance_data->credit;
            if($pre_debit > $pre_credit)
            {
                $PreBalance = $pre_debit - $pre_credit;
                $preDebCre = 'De';
            }else{
                $PreBalance = $pre_credit - $pre_debit;
                $preDebCre = 'Cr';
            }
        }








        if($store_id != 0){
            if($request->from_date && $request->to_date){
                $chart_of_account_transaction = DB::table("chart_of_account_transaction_details")
                    ->join('chart_of_account_transactions','chart_of_account_transaction_details.chart_of_account_transaction_id','=','chart_of_account_transactions.id')
                    ->leftJoin('voucher_types','chart_of_account_transactions.voucher_type_id','=','voucher_types.id')
                    ->where('chart_of_account_transaction_details.chart_of_account_name',$request->chart_of_account_name)
                    ->where('chart_of_account_transaction_details.transaction_date','>=',$request->from_date)
                    ->where('chart_of_account_transaction_details.transaction_date','<=',$request->to_date)
                    ->where('chart_of_account_transactions.store_id','=',$request->store_id)
                    ->select(
                        'voucher_types.name as voucher_type_name',
                        'chart_of_account_transactions.voucher_no',
                        'chart_of_account_transaction_details.debit',
                        'chart_of_account_transaction_details.credit',
                        'chart_of_account_transaction_details.description',
                        'chart_of_account_transaction_details.transaction_date_time'
                    )
                    ->get();
                //return response()->json(['success'=>true,'response' => $chart_of_account_transaction], $this->successStatus);
            }else{
                $chart_of_account_transaction = DB::table("chart_of_account_transaction_details")
                    ->join('chart_of_account_transactions','chart_of_account_transaction_details.chart_of_account_transaction_id','=','chart_of_account_transactions.id')
                    ->leftJoin('voucher_types','chart_of_account_transactions.voucher_type_id','=','voucher_types.id')
                    ->where('chart_of_account_transaction_details.chart_of_account_name',$request->chart_of_account_name)
                    ->where('chart_of_account_transactions.store_id','=',$request->store_id)
                    ->select(
                        'voucher_types.name as voucher_type_name',
                        'chart_of_account_transactions.voucher_no',
                        'chart_of_account_transaction_details.debit',
                        'chart_of_account_transaction_details.debit',
                        'chart_of_account_transaction_details.credit',
                        'chart_of_account_transaction_details.description',
                        'chart_of_account_transaction_details.transaction_date_time'
                    )
                    ->get();
            }
        }else{
            if($request->from_date && $request->to_date){
                $chart_of_account_transaction = DB::table("chart_of_account_transaction_details")
                    ->join('chart_of_account_transactions','chart_of_account_transaction_details.chart_of_account_transaction_id','=','chart_of_account_transactions.id')
                    ->leftJoin('voucher_types','chart_of_account_transactions.voucher_type_id','=','voucher_types.id')
                    ->where('chart_of_account_transaction_details.chart_of_account_name',$request->chart_of_account_name)
                    ->where('chart_of_account_transaction_details.transaction_date','>=',$request->from_date)
                    ->where('chart_of_account_transaction_details.transaction_date','<=',$request->to_date)
                    ->select(
                        'voucher_types.name as voucher_type_name',
                        'chart_of_account_transactions.voucher_no',
                        'chart_of_account_transaction_details.debit',
                        'chart_of_account_transaction_details.credit',
                        'chart_of_account_transaction_details.description',
                        'chart_of_account_transaction_details.transaction_date_time'
                    )
                    ->get();
            }else{
                $chart_of_account_transaction = DB::table("chart_of_account_transaction_details")
                    ->join('chart_of_account_transactions','chart_of_account_transaction_details.chart_of_account_transaction_id','=','chart_of_account_transactions.id')
                    ->leftJoin('voucher_types','chart_of_account_transactions.voucher_type_id','=','voucher_types.id')
                    ->where('chart_of_account_transaction_details.chart_of_account_name',$request->chart_of_account_name)
                    ->select(
                        'voucher_types.name as voucher_type_name',
                        'chart_of_account_transactions.voucher_no',
                        'chart_of_account_transaction_details.debit',
                        'chart_of_account_transaction_details.debit',
                        'chart_of_account_transaction_details.credit',
                        'chart_of_account_transaction_details.description',
                        'chart_of_account_transaction_details.transaction_date_time'
                    )
                    ->get();
            }
        }

        if($chart_of_account_transaction)
        {
            $ledger_data = [
                'chart_of_account_transaction' => $chart_of_account_transaction,
                'PreBalance' => $PreBalance,
                'preDebCre' => $preDebCre,
                'pre_debit' => $pre_debit,
                'pre_credit' => $pre_credit,
                'chart_of_account_name' => $chart_of_account_name,
                'from_date' => $from_date,
                'to_date' => $to_date,
            ];
            return response()->json(['success'=>true,'response' => $ledger_data], $this->successStatus);

            //return response()->json(['success'=>true,'response' => $chart_of_account_transaction], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Chart Of Account Transaction Found!'], $this->failStatus);
        }
    }

    public function cashBookReport(Request $request){
        $validator = Validator::make($request->all(), [
            'chart_of_account_name'=> 'required',
            'from_date'=> 'required',
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





        $store_id = $request->store_id;
        $chart_of_account_name = $request->chart_of_account_name;
        $from_date = $request->from_date;
        $to_date = $request->to_date;


        if($store_id != 0){
            $gl_pre_valance_data = DB::table('chart_of_account_transaction_details')
                ->join('chart_of_account_transactions','chart_of_account_transaction_details.chart_of_account_transaction_id','=','chart_of_account_transactions.id')
                ->select('chart_of_account_transaction_details.chart_of_account_name', DB::raw('SUM(chart_of_account_transaction_details.debit) as debit, SUM(chart_of_account_transaction_details.credit) as credit'))
                ->where('chart_of_account_transaction_details.transaction_date', '<',$from_date)
                ->where('chart_of_account_transaction_details.chart_of_account_name',$chart_of_account_name)
                ->where('chart_of_account_transactions.store_id',$store_id)
                ->groupBy('chart_of_account_transaction_details.chart_of_account_name')
                ->first();
        }else{
            $gl_pre_valance_data = DB::table('chart_of_account_transaction_details')
                ->select('chart_of_account_name', DB::raw('SUM(debit) as debit, SUM(credit) as credit'))
                ->where('transaction_date', '<',$request->from_date)
                ->where('chart_of_account_name',$request->chart_of_account_name)
                ->groupBy('chart_of_account_name')
                ->first();

        }

//        dd($gl_pre_valance_data);

        $PreBalance=0;
        $preDebCre = 'De/Cr';
        $pre_debit = 0;
        $pre_credit = 0;
        if(!empty($gl_pre_valance_data))
        {
            //echo 'ok';exit;
            $pre_debit = $gl_pre_valance_data->debit == NULL ? 0 : $gl_pre_valance_data->debit;
            $pre_credit = $gl_pre_valance_data->credit == NULL ? 0 : $gl_pre_valance_data->credit;
            if($pre_debit > $pre_credit)
            {
                $PreBalance = $pre_debit - $pre_credit;
                $preDebCre = 'De';
            }else{
                $PreBalance = $pre_credit - $pre_debit;
                $preDebCre = 'Cr';
            }
        }







        // sales
        if($store_id != 0){
            if($request->from_date && $request->to_date){
                $sale_info = DB::table("chart_of_account_transaction_details")
                    ->join('chart_of_account_transactions','chart_of_account_transaction_details.chart_of_account_transaction_id','=','chart_of_account_transactions.id')
                    ->leftJoin('voucher_types','chart_of_account_transactions.voucher_type_id','=','voucher_types.id')
                    ->where('chart_of_account_transaction_details.chart_of_account_name',$request->chart_of_account_name)
                    ->where('chart_of_account_transaction_details.transaction_date','>=',$request->from_date)
                    ->where('chart_of_account_transaction_details.transaction_date','<=',$request->to_date)
                    ->where('chart_of_account_transactions.store_id','=',$request->store_id)
                    ->where('chart_of_account_transactions.transaction_type','=','Sales')
                    ->select(DB::raw('SUM(chart_of_account_transaction_details.debit) as debit'))
                    ->first();
                //return response()->json(['success'=>true,'response' => $chart_of_account_transaction], $this->successStatus);
            }else{
                $sale_info = DB::table("chart_of_account_transaction_details")
                    ->join('chart_of_account_transactions','chart_of_account_transaction_details.chart_of_account_transaction_id','=','chart_of_account_transactions.id')
                    ->leftJoin('voucher_types','chart_of_account_transactions.voucher_type_id','=','voucher_types.id')
                    ->where('chart_of_account_transaction_details.chart_of_account_name',$request->chart_of_account_name)
                    ->where('chart_of_account_transactions.store_id','=',$request->store_id)
                    ->where('chart_of_account_transactions.transaction_type','=','Sales')
                    ->select(DB::raw('SUM(chart_of_account_transaction_details.debit) as debit'))
                    ->first();
            }
        }else{
            if($request->from_date && $request->to_date){
                $sale_info = DB::table("chart_of_account_transaction_details")
                    ->join('chart_of_account_transactions','chart_of_account_transaction_details.chart_of_account_transaction_id','=','chart_of_account_transactions.id')
                    ->leftJoin('voucher_types','chart_of_account_transactions.voucher_type_id','=','voucher_types.id')
                    ->where('chart_of_account_transaction_details.chart_of_account_name',$request->chart_of_account_name)
                    ->where('chart_of_account_transaction_details.transaction_date','>=',$request->from_date)
                    ->where('chart_of_account_transaction_details.transaction_date','<=',$request->to_date)
                    ->where('chart_of_account_transactions.transaction_type','=','Sales')
                    ->select(DB::raw('SUM(chart_of_account_transaction_details.debit) as debit'))
                    ->first();
            }else{
                $sale_info = DB::table("chart_of_account_transaction_details")
                    ->join('chart_of_account_transactions','chart_of_account_transaction_details.chart_of_account_transaction_id','=','chart_of_account_transactions.id')
                    ->leftJoin('voucher_types','chart_of_account_transactions.voucher_type_id','=','voucher_types.id')
                    ->where('chart_of_account_transaction_details.chart_of_account_name',$request->chart_of_account_name)
                    ->where('chart_of_account_transactions.transaction_type','=','Sales')
                    ->select(DB::raw('SUM(chart_of_account_transaction_details.debit) as debit'))
                    ->first();
            }
        }

        // sales return info
        if($store_id != 0){
            if($request->from_date && $request->to_date){
                $sale_return_info = DB::table("chart_of_account_transaction_details")
                    ->join('chart_of_account_transactions','chart_of_account_transaction_details.chart_of_account_transaction_id','=','chart_of_account_transactions.id')
                    ->leftJoin('voucher_types','chart_of_account_transactions.voucher_type_id','=','voucher_types.id')
                    ->where('chart_of_account_transaction_details.chart_of_account_name',$request->chart_of_account_name)
                    ->where('chart_of_account_transaction_details.transaction_date','>=',$request->from_date)
                    ->where('chart_of_account_transaction_details.transaction_date','<=',$request->to_date)
                    ->where('chart_of_account_transactions.store_id','=',$request->store_id)
                    ->where('chart_of_account_transactions.transaction_type','=','Sales Return')
                    ->select(DB::raw('SUM(chart_of_account_transaction_details.credit) as credit'))
                    ->first();
            }else{
                $sale_return_info = DB::table("chart_of_account_transaction_details")
                    ->join('chart_of_account_transactions','chart_of_account_transaction_details.chart_of_account_transaction_id','=','chart_of_account_transactions.id')
                    ->leftJoin('voucher_types','chart_of_account_transactions.voucher_type_id','=','voucher_types.id')
                    ->where('chart_of_account_transaction_details.chart_of_account_name',$request->chart_of_account_name)
                    ->where('chart_of_account_transactions.store_id','=',$request->store_id)
                    ->where('chart_of_account_transactions.transaction_type','=','Sales Return')
                    ->select(DB::raw('SUM(chart_of_account_transaction_details.credit) as credit'))
                    ->first();
            }
        }else{
            if($request->from_date && $request->to_date){
                $sale_return_info = DB::table("chart_of_account_transaction_details")
                    ->join('chart_of_account_transactions','chart_of_account_transaction_details.chart_of_account_transaction_id','=','chart_of_account_transactions.id')
                    ->leftJoin('voucher_types','chart_of_account_transactions.voucher_type_id','=','voucher_types.id')
                    ->where('chart_of_account_transaction_details.chart_of_account_name',$request->chart_of_account_name)
                    ->where('chart_of_account_transaction_details.transaction_date','>=',$request->from_date)
                    ->where('chart_of_account_transaction_details.transaction_date','<=',$request->to_date)
                    ->where('chart_of_account_transactions.transaction_type','=','Sales Return')
                    ->select(DB::raw('SUM(chart_of_account_transaction_details.credit) as credit'))
                    ->first();
            }else{
                $sale_return_info = DB::table("chart_of_account_transaction_details")
                    ->join('chart_of_account_transactions','chart_of_account_transaction_details.chart_of_account_transaction_id','=','chart_of_account_transactions.id')
                    ->leftJoin('voucher_types','chart_of_account_transactions.voucher_type_id','=','voucher_types.id')
                    ->where('chart_of_account_transaction_details.chart_of_account_name',$request->chart_of_account_name)
                    ->where('chart_of_account_transactions.transaction_type','=','Sales Return')
                    ->select(DB::raw('SUM(chart_of_account_transaction_details.credit) as credit'))
                    ->first();
            }
        }

        // bkash
        if($store_id != 0){
            if($request->from_date && $request->to_date){
                $bkash_credit_info = DB::table("chart_of_account_transaction_details")
                    ->join('chart_of_account_transactions','chart_of_account_transaction_details.chart_of_account_transaction_id','=','chart_of_account_transactions.id')
                    ->leftJoin('voucher_types','chart_of_account_transactions.voucher_type_id','=','voucher_types.id')
                    //->where('chart_of_account_transaction_details.chart_of_account_name',$request->chart_of_account_name)
                    ->where('chart_of_account_transaction_details.transaction_date','>=',$request->from_date)
                    ->where('chart_of_account_transaction_details.transaction_date','<=',$request->to_date)
                    ->where('chart_of_account_transactions.store_id','=',$request->store_id)
                    ->where('chart_of_account_transaction_details.chart_of_account_name','=','Bkash')
                    ->select(DB::raw('SUM(chart_of_account_transaction_details.credit) as credit'))
                    ->first();
            }else{
                $bkash_credit_info = DB::table("chart_of_account_transaction_details")
                    ->join('chart_of_account_transactions','chart_of_account_transaction_details.chart_of_account_transaction_id','=','chart_of_account_transactions.id')
                    ->leftJoin('voucher_types','chart_of_account_transactions.voucher_type_id','=','voucher_types.id')
                    //->where('chart_of_account_transaction_details.chart_of_account_name',$request->chart_of_account_name)
                    ->where('chart_of_account_transactions.store_id','=',$request->store_id)
                    ->where('chart_of_account_transaction_details.chart_of_account_name','=','Bkash')
                    ->select(DB::raw('SUM(chart_of_account_transaction_details.credit) as credit'))
                    ->first();
            }
        }else{
            if($request->from_date && $request->to_date){
                $bkash_credit_info = DB::table("chart_of_account_transaction_details")
                    ->join('chart_of_account_transactions','chart_of_account_transaction_details.chart_of_account_transaction_id','=','chart_of_account_transactions.id')
                    ->leftJoin('voucher_types','chart_of_account_transactions.voucher_type_id','=','voucher_types.id')
                    //->where('chart_of_account_transaction_details.chart_of_account_name',$request->chart_of_account_name)
                    ->where('chart_of_account_transaction_details.transaction_date','>=',$request->from_date)
                    ->where('chart_of_account_transaction_details.transaction_date','<=',$request->to_date)
                    ->where('chart_of_account_transaction_details.chart_of_account_name','=','Bkash')
                    ->select(DB::raw('SUM(chart_of_account_transaction_details.credit) as credit'))
                    ->first();
            }else{
                $bkash_credit_info = DB::table("chart_of_account_transaction_details")
                    ->join('chart_of_account_transactions','chart_of_account_transaction_details.chart_of_account_transaction_id','=','chart_of_account_transactions.id')
                    ->leftJoin('voucher_types','chart_of_account_transactions.voucher_type_id','=','voucher_types.id')
                    //->where('chart_of_account_transaction_details.chart_of_account_name',$request->chart_of_account_name)
                    ->where('chart_of_account_transaction_details.chart_of_account_name','=','Bkash')
                    ->select(DB::raw('SUM(chart_of_account_transaction_details.credit) as credit'))
                    ->first();
            }
        }

        // Card
        if($store_id != 0){
            if($request->from_date && $request->to_date){
                $card_credit_info = DB::table("chart_of_account_transaction_details")
                    ->join('chart_of_account_transactions','chart_of_account_transaction_details.chart_of_account_transaction_id','=','chart_of_account_transactions.id')
                    ->leftJoin('voucher_types','chart_of_account_transactions.voucher_type_id','=','voucher_types.id')
                    //->where('chart_of_account_transaction_details.chart_of_account_name',$request->chart_of_account_name)
                    ->where('chart_of_account_transaction_details.transaction_date','>=',$request->from_date)
                    ->where('chart_of_account_transaction_details.transaction_date','<=',$request->to_date)
                    ->where('chart_of_account_transactions.store_id','=',$request->store_id)
                    ->where('chart_of_account_transaction_details.chart_of_account_name','=','Card')
                    ->select(DB::raw('SUM(chart_of_account_transaction_details.credit) as credit'))
                    ->first();
            }else{
                $card_credit_info = DB::table("chart_of_account_transaction_details")
                    ->join('chart_of_account_transactions','chart_of_account_transaction_details.chart_of_account_transaction_id','=','chart_of_account_transactions.id')
                    ->leftJoin('voucher_types','chart_of_account_transactions.voucher_type_id','=','voucher_types.id')
                    //->where('chart_of_account_transaction_details.chart_of_account_name',$request->chart_of_account_name)
                    ->where('chart_of_account_transactions.store_id','=',$request->store_id)
                    ->where('chart_of_account_transaction_details.chart_of_account_name','=','Card')
                    ->select(DB::raw('SUM(chart_of_account_transaction_details.credit) as credit'))
                    ->first();
            }
        }else{
            if($request->from_date && $request->to_date){
                $card_credit_info = DB::table("chart_of_account_transaction_details")
                    ->join('chart_of_account_transactions','chart_of_account_transaction_details.chart_of_account_transaction_id','=','chart_of_account_transactions.id')
                    ->leftJoin('voucher_types','chart_of_account_transactions.voucher_type_id','=','voucher_types.id')
                    //->where('chart_of_account_transaction_details.chart_of_account_name',$request->chart_of_account_name)
                    ->where('chart_of_account_transaction_details.transaction_date','>=',$request->from_date)
                    ->where('chart_of_account_transaction_details.transaction_date','<=',$request->to_date)
                    ->where('chart_of_account_transaction_details.chart_of_account_name','=','Card')
                    ->select(DB::raw('SUM(chart_of_account_transaction_details.credit) as credit'))
                    ->first();
            }else{
                $card_credit_info = DB::table("chart_of_account_transaction_details")
                    ->join('chart_of_account_transactions','chart_of_account_transaction_details.chart_of_account_transaction_id','=','chart_of_account_transactions.id')
                    ->leftJoin('voucher_types','chart_of_account_transactions.voucher_type_id','=','voucher_types.id')
                    //->where('chart_of_account_transaction_details.chart_of_account_name',$request->chart_of_account_name)
                    ->where('chart_of_account_transaction_details.chart_of_account_name','=','Card')
                    ->select(DB::raw('SUM(chart_of_account_transaction_details.credit) as credit'))
                    ->first();
            }
        }

        // Rocket
        if($store_id != 0){
            if($request->from_date && $request->to_date){
                $rocket_credit_info = DB::table("chart_of_account_transaction_details")
                    ->join('chart_of_account_transactions','chart_of_account_transaction_details.chart_of_account_transaction_id','=','chart_of_account_transactions.id')
                    ->leftJoin('voucher_types','chart_of_account_transactions.voucher_type_id','=','voucher_types.id')
                    //->where('chart_of_account_transaction_details.chart_of_account_name',$request->chart_of_account_name)
                    ->where('chart_of_account_transaction_details.transaction_date','>=',$request->from_date)
                    ->where('chart_of_account_transaction_details.transaction_date','<=',$request->to_date)
                    ->where('chart_of_account_transactions.store_id','=',$request->store_id)
                    ->where('chart_of_account_transaction_details.chart_of_account_name','=','Rocket')
                    ->select(DB::raw('SUM(chart_of_account_transaction_details.credit) as credit'))
                    ->first();
            }else{
                $rocket_credit_info = DB::table("chart_of_account_transaction_details")
                    ->join('chart_of_account_transactions','chart_of_account_transaction_details.chart_of_account_transaction_id','=','chart_of_account_transactions.id')
                    ->leftJoin('voucher_types','chart_of_account_transactions.voucher_type_id','=','voucher_types.id')
                    //->where('chart_of_account_transaction_details.chart_of_account_name',$request->chart_of_account_name)
                    ->where('chart_of_account_transactions.store_id','=',$request->store_id)
                    ->where('chart_of_account_transaction_details.chart_of_account_name','=','Rocket')
                    ->select(DB::raw('SUM(chart_of_account_transaction_details.credit) as credit'))
                    ->first();
            }
        }else{
            if($request->from_date && $request->to_date){
                $rocket_credit_info = DB::table("chart_of_account_transaction_details")
                    ->join('chart_of_account_transactions','chart_of_account_transaction_details.chart_of_account_transaction_id','=','chart_of_account_transactions.id')
                    ->leftJoin('voucher_types','chart_of_account_transactions.voucher_type_id','=','voucher_types.id')
                    //->where('chart_of_account_transaction_details.chart_of_account_name',$request->chart_of_account_name)
                    ->where('chart_of_account_transaction_details.transaction_date','>=',$request->from_date)
                    ->where('chart_of_account_transaction_details.transaction_date','<=',$request->to_date)
                    ->where('chart_of_account_transaction_details.chart_of_account_name','=','Rocket')
                    ->select(DB::raw('SUM(chart_of_account_transaction_details.credit) as credit'))
                    ->first();
            }else{
                $rocket_credit_info = DB::table("chart_of_account_transaction_details")
                    ->join('chart_of_account_transactions','chart_of_account_transaction_details.chart_of_account_transaction_id','=','chart_of_account_transactions.id')
                    ->leftJoin('voucher_types','chart_of_account_transactions.voucher_type_id','=','voucher_types.id')
                    //->where('chart_of_account_transaction_details.chart_of_account_name',$request->chart_of_account_name)
                    ->where('chart_of_account_transaction_details.chart_of_account_name','=','Rocket')
                    ->select(DB::raw('SUM(chart_of_account_transaction_details.credit) as credit'))
                    ->first();
            }
        }

        // Upay
        if($store_id != 0){
            if($request->from_date && $request->to_date){
                $upay_credit_info = DB::table("chart_of_account_transaction_details")
                    ->join('chart_of_account_transactions','chart_of_account_transaction_details.chart_of_account_transaction_id','=','chart_of_account_transactions.id')
                    ->leftJoin('voucher_types','chart_of_account_transactions.voucher_type_id','=','voucher_types.id')
                    //->where('chart_of_account_transaction_details.chart_of_account_name',$request->chart_of_account_name)
                    ->where('chart_of_account_transaction_details.transaction_date','>=',$request->from_date)
                    ->where('chart_of_account_transaction_details.transaction_date','<=',$request->to_date)
                    ->where('chart_of_account_transactions.store_id','=',$request->store_id)
                    ->where('chart_of_account_transaction_details.chart_of_account_name','=','Upay')
                    ->select(DB::raw('SUM(chart_of_account_transaction_details.credit) as credit'))
                    ->first();
            }else{
                $upay_credit_info = DB::table("chart_of_account_transaction_details")
                    ->join('chart_of_account_transactions','chart_of_account_transaction_details.chart_of_account_transaction_id','=','chart_of_account_transactions.id')
                    ->leftJoin('voucher_types','chart_of_account_transactions.voucher_type_id','=','voucher_types.id')
                    //->where('chart_of_account_transaction_details.chart_of_account_name',$request->chart_of_account_name)
                    ->where('chart_of_account_transactions.store_id','=',$request->store_id)
                    ->where('chart_of_account_transaction_details.chart_of_account_name','=','Upay')
                    ->select(DB::raw('SUM(chart_of_account_transaction_details.credit) as credit'))
                    ->first();
            }
        }else{
            if($request->from_date && $request->to_date){
                $upay_credit_info = DB::table("chart_of_account_transaction_details")
                    ->join('chart_of_account_transactions','chart_of_account_transaction_details.chart_of_account_transaction_id','=','chart_of_account_transactions.id')
                    ->leftJoin('voucher_types','chart_of_account_transactions.voucher_type_id','=','voucher_types.id')
                    //->where('chart_of_account_transaction_details.chart_of_account_name',$request->chart_of_account_name)
                    ->where('chart_of_account_transaction_details.transaction_date','>=',$request->from_date)
                    ->where('chart_of_account_transaction_details.transaction_date','<=',$request->to_date)
                    ->where('chart_of_account_transaction_details.chart_of_account_name','=','Upay')
                    ->select(DB::raw('SUM(chart_of_account_transaction_details.credit) as credit'))
                    ->first();
            }else{
                $upay_credit_info = DB::table("chart_of_account_transaction_details")
                    ->join('chart_of_account_transactions','chart_of_account_transaction_details.chart_of_account_transaction_id','=','chart_of_account_transactions.id')
                    ->leftJoin('voucher_types','chart_of_account_transactions.voucher_type_id','=','voucher_types.id')
                    //->where('chart_of_account_transaction_details.chart_of_account_name',$request->chart_of_account_name)
                    ->where('chart_of_account_transaction_details.chart_of_account_name','=','Upay')
                    ->select(DB::raw('SUM(chart_of_account_transaction_details.credit) as credit'))
                    ->first();
            }
        }

        // Employee Salary
        if($store_id != 0){
            if($request->from_date && $request->to_date){
                $employee_salary_credit_info = DB::table("chart_of_account_transaction_details")
                    ->join('chart_of_account_transactions','chart_of_account_transaction_details.chart_of_account_transaction_id','=','chart_of_account_transactions.id')
                    ->leftJoin('voucher_types','chart_of_account_transactions.voucher_type_id','=','voucher_types.id')
                    //->where('chart_of_account_transaction_details.chart_of_account_name',$request->chart_of_account_name)
                    ->where('chart_of_account_transaction_details.transaction_date','>=',$request->from_date)
                    ->where('chart_of_account_transaction_details.transaction_date','<=',$request->to_date)
                    ->where('chart_of_account_transactions.store_id','=',$request->store_id)
                    ->where('chart_of_account_transaction_details.chart_of_account_name','=','Employee Salary')
                    ->select(DB::raw('SUM(chart_of_account_transaction_details.credit) as credit'))
                    ->first();
            }else{
                $employee_salary_credit_info = DB::table("chart_of_account_transaction_details")
                    ->join('chart_of_account_transactions','chart_of_account_transaction_details.chart_of_account_transaction_id','=','chart_of_account_transactions.id')
                    ->leftJoin('voucher_types','chart_of_account_transactions.voucher_type_id','=','voucher_types.id')
                    //->where('chart_of_account_transaction_details.chart_of_account_name',$request->chart_of_account_name)
                    ->where('chart_of_account_transactions.store_id','=',$request->store_id)
                    ->where('chart_of_account_transaction_details.chart_of_account_name','=','Employee Salary')
                    ->select(DB::raw('SUM(chart_of_account_transaction_details.credit) as credit'))
                    ->first();
            }
        }else{
            if($request->from_date && $request->to_date){
                $employee_salary_credit_info = DB::table("chart_of_account_transaction_details")
                    ->join('chart_of_account_transactions','chart_of_account_transaction_details.chart_of_account_transaction_id','=','chart_of_account_transactions.id')
                    ->leftJoin('voucher_types','chart_of_account_transactions.voucher_type_id','=','voucher_types.id')
                    //->where('chart_of_account_transaction_details.chart_of_account_name',$request->chart_of_account_name)
                    ->where('chart_of_account_transaction_details.transaction_date','>=',$request->from_date)
                    ->where('chart_of_account_transaction_details.transaction_date','<=',$request->to_date)
                    ->where('chart_of_account_transaction_details.chart_of_account_name','=','Employee Salary')
                    ->select(DB::raw('SUM(chart_of_account_transaction_details.credit) as credit'))
                    ->first();
            }else{
                $employee_salary_credit_info = DB::table("chart_of_account_transaction_details")
                    ->join('chart_of_account_transactions','chart_of_account_transaction_details.chart_of_account_transaction_id','=','chart_of_account_transactions.id')
                    ->leftJoin('voucher_types','chart_of_account_transactions.voucher_type_id','=','voucher_types.id')
                    //->where('chart_of_account_transaction_details.chart_of_account_name',$request->chart_of_account_name)
                    ->where('chart_of_account_transaction_details.chart_of_account_name','=','Employee Salary')
                    ->select(DB::raw('SUM(chart_of_account_transaction_details.credit) as credit'))
                    ->first();
            }
        }

        // Boss
        if($store_id != 0){
            if($request->from_date && $request->to_date){
                $boss_credit_info = DB::table("chart_of_account_transaction_details")
                    ->join('chart_of_account_transactions','chart_of_account_transaction_details.chart_of_account_transaction_id','=','chart_of_account_transactions.id')
                    ->leftJoin('voucher_types','chart_of_account_transactions.voucher_type_id','=','voucher_types.id')
                    //->where('chart_of_account_transaction_details.chart_of_account_name',$request->chart_of_account_name)
                    ->where('chart_of_account_transaction_details.transaction_date','>=',$request->from_date)
                    ->where('chart_of_account_transaction_details.transaction_date','<=',$request->to_date)
                    ->where('chart_of_account_transactions.store_id','=',$request->store_id)
                    ->where('chart_of_account_transaction_details.chart_of_account_name','=','Boss')
                    ->select(DB::raw('SUM(chart_of_account_transaction_details.credit) as credit'))
                    ->first();
            }else{
                $boss_credit_info = DB::table("chart_of_account_transaction_details")
                    ->join('chart_of_account_transactions','chart_of_account_transaction_details.chart_of_account_transaction_id','=','chart_of_account_transactions.id')
                    ->leftJoin('voucher_types','chart_of_account_transactions.voucher_type_id','=','voucher_types.id')
                    //->where('chart_of_account_transaction_details.chart_of_account_name',$request->chart_of_account_name)
                    ->where('chart_of_account_transactions.store_id','=',$request->store_id)
                    ->where('chart_of_account_transaction_details.chart_of_account_name','=','Boss')
                    ->select(DB::raw('SUM(chart_of_account_transaction_details.credit) as credit'))
                    ->first();
            }
        }else{
            if($request->from_date && $request->to_date){
                $boss_credit_info = DB::table("chart_of_account_transaction_details")
                    ->join('chart_of_account_transactions','chart_of_account_transaction_details.chart_of_account_transaction_id','=','chart_of_account_transactions.id')
                    ->leftJoin('voucher_types','chart_of_account_transactions.voucher_type_id','=','voucher_types.id')
                    //->where('chart_of_account_transaction_details.chart_of_account_name',$request->chart_of_account_name)
                    ->where('chart_of_account_transaction_details.transaction_date','>=',$request->from_date)
                    ->where('chart_of_account_transaction_details.transaction_date','<=',$request->to_date)
                    ->where('chart_of_account_transaction_details.chart_of_account_name','=','Boss')
                    ->select(DB::raw('SUM(chart_of_account_transaction_details.credit) as credit'))
                    ->first();
            }else{
                $boss_credit_info = DB::table("chart_of_account_transaction_details")
                    ->join('chart_of_account_transactions','chart_of_account_transaction_details.chart_of_account_transaction_id','=','chart_of_account_transactions.id')
                    ->leftJoin('voucher_types','chart_of_account_transactions.voucher_type_id','=','voucher_types.id')
                    //->where('chart_of_account_transaction_details.chart_of_account_name',$request->chart_of_account_name)
                    ->where('chart_of_account_transaction_details.chart_of_account_name','=','Boss')
                    ->select(DB::raw('SUM(chart_of_account_transaction_details.credit) as credit'))
                    ->first();
            }
        }


//        $ledger_data = [
//            'sale_info' => $sale_info,
//            'sale_return_info' => $sale_return_info,
//            'bkash_credit_info' => $bkash_credit_info,
//            'card_credit_info' => $card_credit_info,
//            'rocket_credit_info' => $rocket_credit_info,
//            'upay_credit_info' => $upay_credit_info,
//            'employee_salary_credit_info' => $employee_salary_credit_info,
//            'boss_credit_info' => $boss_credit_info,
//            'PreBalance' => $PreBalance,
//            'preDebCre' => $preDebCre,
//            'pre_debit' => $pre_debit,
//            'pre_credit' => $pre_credit,
//            'chart_of_account_name' => $chart_of_account_name,
//            'from_date' => $from_date,
//            'to_date' => $to_date,
//        ];

        $ledger_data = [
            'sale_info' => [
                'debit' => $sale_info->debit,
                'credit' => 0
            ],
            'sale_return_info' => [
                'debit' => 0,
                'credit' => $sale_return_info->credit ? $sale_return_info->credit : 0
            ],
            'bkash_credit_info' => [
                'debit' => 0,
                'credit' => $bkash_credit_info->credit ? $bkash_credit_info->credit :0
            ],
            'card_credit_info' => [
                'debit' => 0,
                'credit' => $card_credit_info->credit ? $card_credit_info->credit : 0
            ],
            'rocket_credit_info' => [
                'debit' => 0,
                'credit' => $rocket_credit_info->credit ? $rocket_credit_info->credit : 0
            ],
            'upay_credit_info' => [
                'debit' => 0,
                'credit' => $upay_credit_info->credit ? $upay_credit_info->credit : 0
            ],
            'employee_salary_credit_info' => [
                'debit' => 0,
                'credit' => $employee_salary_credit_info->credit ? $employee_salary_credit_info->credit : 0
            ],
            'boss_credit_info' => [
                'debit' => 0,
                'credit' => $boss_credit_info->credit ? $boss_credit_info->credit : 0
            ],
            'PreBalance' => $PreBalance,
            'preDebCre' => $preDebCre,
            'pre_debit' => $pre_debit,
            'pre_credit' => $pre_credit,
            'chart_of_account_name' => $chart_of_account_name,
            'from_date' => $from_date,
            'to_date' => $to_date,
        ];
        return response()->json(['success'=>true,'response' => $ledger_data], $this->successStatus);


    }

    public function ledgerReport(Request $request){
        $validator = Validator::make($request->all(), [
            'chart_of_account_name'=> 'required',
            'from_date'=> 'required',
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





        $store_id = $request->store_id;
        $chart_of_account_name = $request->chart_of_account_name;
        $from_date = $request->from_date;
        $to_date = $request->to_date;


        if($store_id != 0){
            $gl_pre_valance_data = DB::table('chart_of_account_transaction_details')
                ->join('chart_of_account_transactions','chart_of_account_transaction_details.chart_of_account_transaction_id','=','chart_of_account_transactions.id')
                ->select('chart_of_account_transaction_details.chart_of_account_name', DB::raw('SUM(chart_of_account_transaction_details.debit) as debit, SUM(chart_of_account_transaction_details.credit) as credit'))
                ->where('chart_of_account_transaction_details.transaction_date', '<',$from_date)
                ->where('chart_of_account_transaction_details.chart_of_account_name',$chart_of_account_name)
                ->where('chart_of_account_transactions.store_id',$store_id)
                ->groupBy('chart_of_account_transaction_details.chart_of_account_name')
                ->first();
        }else{
            $gl_pre_valance_data = DB::table('chart_of_account_transaction_details')
                ->select('chart_of_account_name', DB::raw('SUM(debit) as debit, SUM(credit) as credit'))
                ->where('transaction_date', '<',$request->from_date)
                ->where('chart_of_account_name',$request->chart_of_account_name)
                ->groupBy('chart_of_account_name')
                ->first();

        }

//        dd($gl_pre_valance_data);

        $PreBalance=0;
        $preDebCre = 'De/Cr';
        $pre_debit = 0;
        $pre_credit = 0;
        if(!empty($gl_pre_valance_data))
        {
            //echo 'ok';exit;
            $pre_debit = $gl_pre_valance_data->debit == NULL ? 0 : $gl_pre_valance_data->debit;
            $pre_credit = $gl_pre_valance_data->credit == NULL ? 0 : $gl_pre_valance_data->credit;
            if($pre_debit > $pre_credit)
            {
                $PreBalance = $pre_debit - $pre_credit;
                $preDebCre = 'De';
            }else{
                $PreBalance = $pre_credit - $pre_debit;
                $preDebCre = 'Cr';
            }
        }







        // sales
        if($store_id != 0){
            if($request->from_date && $request->to_date){
                $sale_info = DB::table("chart_of_account_transaction_details")
                    ->join('chart_of_account_transactions','chart_of_account_transaction_details.chart_of_account_transaction_id','=','chart_of_account_transactions.id')
                    ->leftJoin('voucher_types','chart_of_account_transactions.voucher_type_id','=','voucher_types.id')
                    ->where('chart_of_account_transaction_details.chart_of_account_name',$request->chart_of_account_name)
                    ->where('chart_of_account_transaction_details.transaction_date','>=',$request->from_date)
                    ->where('chart_of_account_transaction_details.transaction_date','<=',$request->to_date)
                    ->where('chart_of_account_transactions.store_id','=',$request->store_id)
                    ->where('chart_of_account_transactions.transaction_type','=','Sales')
                    ->select(DB::raw('SUM(chart_of_account_transaction_details.debit) as debit'))
                    ->first();
                //return response()->json(['success'=>true,'response' => $chart_of_account_transaction], $this->successStatus);
            }else{
                $sale_info = DB::table("chart_of_account_transaction_details")
                    ->join('chart_of_account_transactions','chart_of_account_transaction_details.chart_of_account_transaction_id','=','chart_of_account_transactions.id')
                    ->leftJoin('voucher_types','chart_of_account_transactions.voucher_type_id','=','voucher_types.id')
                    ->where('chart_of_account_transaction_details.chart_of_account_name',$request->chart_of_account_name)
                    ->where('chart_of_account_transactions.store_id','=',$request->store_id)
                    ->where('chart_of_account_transactions.transaction_type','=','Sales')
                    ->select(DB::raw('SUM(chart_of_account_transaction_details.debit) as debit'))
                    ->first();
            }
        }else{
            if($request->from_date && $request->to_date){
                $sale_info = DB::table("chart_of_account_transaction_details")
                    ->join('chart_of_account_transactions','chart_of_account_transaction_details.chart_of_account_transaction_id','=','chart_of_account_transactions.id')
                    ->leftJoin('voucher_types','chart_of_account_transactions.voucher_type_id','=','voucher_types.id')
                    ->where('chart_of_account_transaction_details.chart_of_account_name',$request->chart_of_account_name)
                    ->where('chart_of_account_transaction_details.transaction_date','>=',$request->from_date)
                    ->where('chart_of_account_transaction_details.transaction_date','<=',$request->to_date)
                    ->where('chart_of_account_transactions.transaction_type','=','Sales')
                    ->select(DB::raw('SUM(chart_of_account_transaction_details.debit) as debit'))
                    ->first();
            }else{
                $sale_info = DB::table("chart_of_account_transaction_details")
                    ->join('chart_of_account_transactions','chart_of_account_transaction_details.chart_of_account_transaction_id','=','chart_of_account_transactions.id')
                    ->leftJoin('voucher_types','chart_of_account_transactions.voucher_type_id','=','voucher_types.id')
                    ->where('chart_of_account_transaction_details.chart_of_account_name',$request->chart_of_account_name)
                    ->where('chart_of_account_transactions.transaction_type','=','Sales')
                    ->select(DB::raw('SUM(chart_of_account_transaction_details.debit) as debit'))
                    ->first();
            }
        }

        // sales return info
        if($store_id != 0){
            if($request->from_date && $request->to_date){
                $sale_return_info = DB::table("chart_of_account_transaction_details")
                    ->join('chart_of_account_transactions','chart_of_account_transaction_details.chart_of_account_transaction_id','=','chart_of_account_transactions.id')
                    ->leftJoin('voucher_types','chart_of_account_transactions.voucher_type_id','=','voucher_types.id')
                    ->where('chart_of_account_transaction_details.chart_of_account_name',$request->chart_of_account_name)
                    ->where('chart_of_account_transaction_details.transaction_date','>=',$request->from_date)
                    ->where('chart_of_account_transaction_details.transaction_date','<=',$request->to_date)
                    ->where('chart_of_account_transactions.store_id','=',$request->store_id)
                    ->where('chart_of_account_transactions.transaction_type','=','Sales Return')
                    ->select(DB::raw('SUM(chart_of_account_transaction_details.credit) as credit'))
                    ->first();
            }else{
                $sale_return_info = DB::table("chart_of_account_transaction_details")
                    ->join('chart_of_account_transactions','chart_of_account_transaction_details.chart_of_account_transaction_id','=','chart_of_account_transactions.id')
                    ->leftJoin('voucher_types','chart_of_account_transactions.voucher_type_id','=','voucher_types.id')
                    ->where('chart_of_account_transaction_details.chart_of_account_name',$request->chart_of_account_name)
                    ->where('chart_of_account_transactions.store_id','=',$request->store_id)
                    ->where('chart_of_account_transactions.transaction_type','=','Sales Return')
                    ->select(DB::raw('SUM(chart_of_account_transaction_details.credit) as credit'))
                    ->first();
            }
        }else{
            if($request->from_date && $request->to_date){
                $sale_return_info = DB::table("chart_of_account_transaction_details")
                    ->join('chart_of_account_transactions','chart_of_account_transaction_details.chart_of_account_transaction_id','=','chart_of_account_transactions.id')
                    ->leftJoin('voucher_types','chart_of_account_transactions.voucher_type_id','=','voucher_types.id')
                    ->where('chart_of_account_transaction_details.chart_of_account_name',$request->chart_of_account_name)
                    ->where('chart_of_account_transaction_details.transaction_date','>=',$request->from_date)
                    ->where('chart_of_account_transaction_details.transaction_date','<=',$request->to_date)
                    ->where('chart_of_account_transactions.transaction_type','=','Sales Return')
                    ->select(DB::raw('SUM(chart_of_account_transaction_details.credit) as credit'))
                    ->first();
            }else{
                $sale_return_info = DB::table("chart_of_account_transaction_details")
                    ->join('chart_of_account_transactions','chart_of_account_transaction_details.chart_of_account_transaction_id','=','chart_of_account_transactions.id')
                    ->leftJoin('voucher_types','chart_of_account_transactions.voucher_type_id','=','voucher_types.id')
                    ->where('chart_of_account_transaction_details.chart_of_account_name',$request->chart_of_account_name)
                    ->where('chart_of_account_transactions.transaction_type','=','Sales Return')
                    ->select(DB::raw('SUM(chart_of_account_transaction_details.credit) as credit'))
                    ->first();
            }
        }

        // bkash
        if($store_id != 0){
            if($request->from_date && $request->to_date){
                $bkash_credit_info = DB::table("chart_of_account_transaction_details")
                    ->join('chart_of_account_transactions','chart_of_account_transaction_details.chart_of_account_transaction_id','=','chart_of_account_transactions.id')
                    ->leftJoin('voucher_types','chart_of_account_transactions.voucher_type_id','=','voucher_types.id')
                    //->where('chart_of_account_transaction_details.chart_of_account_name',$request->chart_of_account_name)
                    ->where('chart_of_account_transaction_details.transaction_date','>=',$request->from_date)
                    ->where('chart_of_account_transaction_details.transaction_date','<=',$request->to_date)
                    ->where('chart_of_account_transactions.store_id','=',$request->store_id)
                    ->where('chart_of_account_transaction_details.chart_of_account_name','=','Bkash')
                    ->select(DB::raw('SUM(chart_of_account_transaction_details.credit) as credit'))
                    ->first();
            }else{
                $bkash_credit_info = DB::table("chart_of_account_transaction_details")
                    ->join('chart_of_account_transactions','chart_of_account_transaction_details.chart_of_account_transaction_id','=','chart_of_account_transactions.id')
                    ->leftJoin('voucher_types','chart_of_account_transactions.voucher_type_id','=','voucher_types.id')
                    //->where('chart_of_account_transaction_details.chart_of_account_name',$request->chart_of_account_name)
                    ->where('chart_of_account_transactions.store_id','=',$request->store_id)
                    ->where('chart_of_account_transaction_details.chart_of_account_name','=','Bkash')
                    ->select(DB::raw('SUM(chart_of_account_transaction_details.credit) as credit'))
                    ->first();
            }
        }else{
            if($request->from_date && $request->to_date){
                $bkash_credit_info = DB::table("chart_of_account_transaction_details")
                    ->join('chart_of_account_transactions','chart_of_account_transaction_details.chart_of_account_transaction_id','=','chart_of_account_transactions.id')
                    ->leftJoin('voucher_types','chart_of_account_transactions.voucher_type_id','=','voucher_types.id')
                    //->where('chart_of_account_transaction_details.chart_of_account_name',$request->chart_of_account_name)
                    ->where('chart_of_account_transaction_details.transaction_date','>=',$request->from_date)
                    ->where('chart_of_account_transaction_details.transaction_date','<=',$request->to_date)
                    ->where('chart_of_account_transaction_details.chart_of_account_name','=','Bkash')
                    ->select(DB::raw('SUM(chart_of_account_transaction_details.credit) as credit'))
                    ->first();
            }else{
                $bkash_credit_info = DB::table("chart_of_account_transaction_details")
                    ->join('chart_of_account_transactions','chart_of_account_transaction_details.chart_of_account_transaction_id','=','chart_of_account_transactions.id')
                    ->leftJoin('voucher_types','chart_of_account_transactions.voucher_type_id','=','voucher_types.id')
                    //->where('chart_of_account_transaction_details.chart_of_account_name',$request->chart_of_account_name)
                    ->where('chart_of_account_transaction_details.chart_of_account_name','=','Bkash')
                    ->select(DB::raw('SUM(chart_of_account_transaction_details.credit) as credit'))
                    ->first();
            }
        }

        // Card
        if($store_id != 0){
            if($request->from_date && $request->to_date){
                $card_credit_info = DB::table("chart_of_account_transaction_details")
                    ->join('chart_of_account_transactions','chart_of_account_transaction_details.chart_of_account_transaction_id','=','chart_of_account_transactions.id')
                    ->leftJoin('voucher_types','chart_of_account_transactions.voucher_type_id','=','voucher_types.id')
                    //->where('chart_of_account_transaction_details.chart_of_account_name',$request->chart_of_account_name)
                    ->where('chart_of_account_transaction_details.transaction_date','>=',$request->from_date)
                    ->where('chart_of_account_transaction_details.transaction_date','<=',$request->to_date)
                    ->where('chart_of_account_transactions.store_id','=',$request->store_id)
                    ->where('chart_of_account_transaction_details.chart_of_account_name','=','Card')
                    ->select(DB::raw('SUM(chart_of_account_transaction_details.credit) as credit'))
                    ->first();
            }else{
                $card_credit_info = DB::table("chart_of_account_transaction_details")
                    ->join('chart_of_account_transactions','chart_of_account_transaction_details.chart_of_account_transaction_id','=','chart_of_account_transactions.id')
                    ->leftJoin('voucher_types','chart_of_account_transactions.voucher_type_id','=','voucher_types.id')
                    //->where('chart_of_account_transaction_details.chart_of_account_name',$request->chart_of_account_name)
                    ->where('chart_of_account_transactions.store_id','=',$request->store_id)
                    ->where('chart_of_account_transaction_details.chart_of_account_name','=','Card')
                    ->select(DB::raw('SUM(chart_of_account_transaction_details.credit) as credit'))
                    ->first();
            }
        }else{
            if($request->from_date && $request->to_date){
                $card_credit_info = DB::table("chart_of_account_transaction_details")
                    ->join('chart_of_account_transactions','chart_of_account_transaction_details.chart_of_account_transaction_id','=','chart_of_account_transactions.id')
                    ->leftJoin('voucher_types','chart_of_account_transactions.voucher_type_id','=','voucher_types.id')
                    //->where('chart_of_account_transaction_details.chart_of_account_name',$request->chart_of_account_name)
                    ->where('chart_of_account_transaction_details.transaction_date','>=',$request->from_date)
                    ->where('chart_of_account_transaction_details.transaction_date','<=',$request->to_date)
                    ->where('chart_of_account_transaction_details.chart_of_account_name','=','Card')
                    ->select(DB::raw('SUM(chart_of_account_transaction_details.credit) as credit'))
                    ->first();
            }else{
                $card_credit_info = DB::table("chart_of_account_transaction_details")
                    ->join('chart_of_account_transactions','chart_of_account_transaction_details.chart_of_account_transaction_id','=','chart_of_account_transactions.id')
                    ->leftJoin('voucher_types','chart_of_account_transactions.voucher_type_id','=','voucher_types.id')
                    //->where('chart_of_account_transaction_details.chart_of_account_name',$request->chart_of_account_name)
                    ->where('chart_of_account_transaction_details.chart_of_account_name','=','Card')
                    ->select(DB::raw('SUM(chart_of_account_transaction_details.credit) as credit'))
                    ->first();
            }
        }

        // Rocket
        if($store_id != 0){
            if($request->from_date && $request->to_date){
                $rocket_credit_info = DB::table("chart_of_account_transaction_details")
                    ->join('chart_of_account_transactions','chart_of_account_transaction_details.chart_of_account_transaction_id','=','chart_of_account_transactions.id')
                    ->leftJoin('voucher_types','chart_of_account_transactions.voucher_type_id','=','voucher_types.id')
                    //->where('chart_of_account_transaction_details.chart_of_account_name',$request->chart_of_account_name)
                    ->where('chart_of_account_transaction_details.transaction_date','>=',$request->from_date)
                    ->where('chart_of_account_transaction_details.transaction_date','<=',$request->to_date)
                    ->where('chart_of_account_transactions.store_id','=',$request->store_id)
                    ->where('chart_of_account_transaction_details.chart_of_account_name','=','Rocket')
                    ->select(DB::raw('SUM(chart_of_account_transaction_details.credit) as credit'))
                    ->first();
            }else{
                $rocket_credit_info = DB::table("chart_of_account_transaction_details")
                    ->join('chart_of_account_transactions','chart_of_account_transaction_details.chart_of_account_transaction_id','=','chart_of_account_transactions.id')
                    ->leftJoin('voucher_types','chart_of_account_transactions.voucher_type_id','=','voucher_types.id')
                    //->where('chart_of_account_transaction_details.chart_of_account_name',$request->chart_of_account_name)
                    ->where('chart_of_account_transactions.store_id','=',$request->store_id)
                    ->where('chart_of_account_transaction_details.chart_of_account_name','=','Rocket')
                    ->select(DB::raw('SUM(chart_of_account_transaction_details.credit) as credit'))
                    ->first();
            }
        }else{
            if($request->from_date && $request->to_date){
                $rocket_credit_info = DB::table("chart_of_account_transaction_details")
                    ->join('chart_of_account_transactions','chart_of_account_transaction_details.chart_of_account_transaction_id','=','chart_of_account_transactions.id')
                    ->leftJoin('voucher_types','chart_of_account_transactions.voucher_type_id','=','voucher_types.id')
                    //->where('chart_of_account_transaction_details.chart_of_account_name',$request->chart_of_account_name)
                    ->where('chart_of_account_transaction_details.transaction_date','>=',$request->from_date)
                    ->where('chart_of_account_transaction_details.transaction_date','<=',$request->to_date)
                    ->where('chart_of_account_transaction_details.chart_of_account_name','=','Rocket')
                    ->select(DB::raw('SUM(chart_of_account_transaction_details.credit) as credit'))
                    ->first();
            }else{
                $rocket_credit_info = DB::table("chart_of_account_transaction_details")
                    ->join('chart_of_account_transactions','chart_of_account_transaction_details.chart_of_account_transaction_id','=','chart_of_account_transactions.id')
                    ->leftJoin('voucher_types','chart_of_account_transactions.voucher_type_id','=','voucher_types.id')
                    //->where('chart_of_account_transaction_details.chart_of_account_name',$request->chart_of_account_name)
                    ->where('chart_of_account_transaction_details.chart_of_account_name','=','Rocket')
                    ->select(DB::raw('SUM(chart_of_account_transaction_details.credit) as credit'))
                    ->first();
            }
        }

        // Upay
        if($store_id != 0){
            if($request->from_date && $request->to_date){
                $upay_credit_info = DB::table("chart_of_account_transaction_details")
                    ->join('chart_of_account_transactions','chart_of_account_transaction_details.chart_of_account_transaction_id','=','chart_of_account_transactions.id')
                    ->leftJoin('voucher_types','chart_of_account_transactions.voucher_type_id','=','voucher_types.id')
                    //->where('chart_of_account_transaction_details.chart_of_account_name',$request->chart_of_account_name)
                    ->where('chart_of_account_transaction_details.transaction_date','>=',$request->from_date)
                    ->where('chart_of_account_transaction_details.transaction_date','<=',$request->to_date)
                    ->where('chart_of_account_transactions.store_id','=',$request->store_id)
                    ->where('chart_of_account_transaction_details.chart_of_account_name','=','Upay')
                    ->select(DB::raw('SUM(chart_of_account_transaction_details.credit) as credit'))
                    ->first();
            }else{
                $upay_credit_info = DB::table("chart_of_account_transaction_details")
                    ->join('chart_of_account_transactions','chart_of_account_transaction_details.chart_of_account_transaction_id','=','chart_of_account_transactions.id')
                    ->leftJoin('voucher_types','chart_of_account_transactions.voucher_type_id','=','voucher_types.id')
                    //->where('chart_of_account_transaction_details.chart_of_account_name',$request->chart_of_account_name)
                    ->where('chart_of_account_transactions.store_id','=',$request->store_id)
                    ->where('chart_of_account_transaction_details.chart_of_account_name','=','Upay')
                    ->select(DB::raw('SUM(chart_of_account_transaction_details.credit) as credit'))
                    ->first();
            }
        }else{
            if($request->from_date && $request->to_date){
                $upay_credit_info = DB::table("chart_of_account_transaction_details")
                    ->join('chart_of_account_transactions','chart_of_account_transaction_details.chart_of_account_transaction_id','=','chart_of_account_transactions.id')
                    ->leftJoin('voucher_types','chart_of_account_transactions.voucher_type_id','=','voucher_types.id')
                    //->where('chart_of_account_transaction_details.chart_of_account_name',$request->chart_of_account_name)
                    ->where('chart_of_account_transaction_details.transaction_date','>=',$request->from_date)
                    ->where('chart_of_account_transaction_details.transaction_date','<=',$request->to_date)
                    ->where('chart_of_account_transaction_details.chart_of_account_name','=','Upay')
                    ->select(DB::raw('SUM(chart_of_account_transaction_details.credit) as credit'))
                    ->first();
            }else{
                $upay_credit_info = DB::table("chart_of_account_transaction_details")
                    ->join('chart_of_account_transactions','chart_of_account_transaction_details.chart_of_account_transaction_id','=','chart_of_account_transactions.id')
                    ->leftJoin('voucher_types','chart_of_account_transactions.voucher_type_id','=','voucher_types.id')
                    //->where('chart_of_account_transaction_details.chart_of_account_name',$request->chart_of_account_name)
                    ->where('chart_of_account_transaction_details.chart_of_account_name','=','Upay')
                    ->select(DB::raw('SUM(chart_of_account_transaction_details.credit) as credit'))
                    ->first();
            }
        }

        // Employee Salary
        if($store_id != 0){
            if($request->from_date && $request->to_date){
                $employee_salary_credit_info = DB::table("chart_of_account_transaction_details")
                    ->join('chart_of_account_transactions','chart_of_account_transaction_details.chart_of_account_transaction_id','=','chart_of_account_transactions.id')
                    ->leftJoin('voucher_types','chart_of_account_transactions.voucher_type_id','=','voucher_types.id')
                    //->where('chart_of_account_transaction_details.chart_of_account_name',$request->chart_of_account_name)
                    ->where('chart_of_account_transaction_details.transaction_date','>=',$request->from_date)
                    ->where('chart_of_account_transaction_details.transaction_date','<=',$request->to_date)
                    ->where('chart_of_account_transactions.store_id','=',$request->store_id)
                    ->where('chart_of_account_transaction_details.chart_of_account_name','=','Employee Salary')
                    ->select(DB::raw('SUM(chart_of_account_transaction_details.credit) as credit'))
                    ->first();
            }else{
                $employee_salary_credit_info = DB::table("chart_of_account_transaction_details")
                    ->join('chart_of_account_transactions','chart_of_account_transaction_details.chart_of_account_transaction_id','=','chart_of_account_transactions.id')
                    ->leftJoin('voucher_types','chart_of_account_transactions.voucher_type_id','=','voucher_types.id')
                    //->where('chart_of_account_transaction_details.chart_of_account_name',$request->chart_of_account_name)
                    ->where('chart_of_account_transactions.store_id','=',$request->store_id)
                    ->where('chart_of_account_transaction_details.chart_of_account_name','=','Employee Salary')
                    ->select(DB::raw('SUM(chart_of_account_transaction_details.credit) as credit'))
                    ->first();
            }
        }else{
            if($request->from_date && $request->to_date){
                $employee_salary_credit_info = DB::table("chart_of_account_transaction_details")
                    ->join('chart_of_account_transactions','chart_of_account_transaction_details.chart_of_account_transaction_id','=','chart_of_account_transactions.id')
                    ->leftJoin('voucher_types','chart_of_account_transactions.voucher_type_id','=','voucher_types.id')
                    //->where('chart_of_account_transaction_details.chart_of_account_name',$request->chart_of_account_name)
                    ->where('chart_of_account_transaction_details.transaction_date','>=',$request->from_date)
                    ->where('chart_of_account_transaction_details.transaction_date','<=',$request->to_date)
                    ->where('chart_of_account_transaction_details.chart_of_account_name','=','Employee Salary')
                    ->select(DB::raw('SUM(chart_of_account_transaction_details.credit) as credit'))
                    ->first();
            }else{
                $employee_salary_credit_info = DB::table("chart_of_account_transaction_details")
                    ->join('chart_of_account_transactions','chart_of_account_transaction_details.chart_of_account_transaction_id','=','chart_of_account_transactions.id')
                    ->leftJoin('voucher_types','chart_of_account_transactions.voucher_type_id','=','voucher_types.id')
                    //->where('chart_of_account_transaction_details.chart_of_account_name',$request->chart_of_account_name)
                    ->where('chart_of_account_transaction_details.chart_of_account_name','=','Employee Salary')
                    ->select(DB::raw('SUM(chart_of_account_transaction_details.credit) as credit'))
                    ->first();
            }
        }

        // Boss
        if($store_id != 0){
            if($request->from_date && $request->to_date){
                $boss_credit_info = DB::table("chart_of_account_transaction_details")
                    ->join('chart_of_account_transactions','chart_of_account_transaction_details.chart_of_account_transaction_id','=','chart_of_account_transactions.id')
                    ->leftJoin('voucher_types','chart_of_account_transactions.voucher_type_id','=','voucher_types.id')
                    //->where('chart_of_account_transaction_details.chart_of_account_name',$request->chart_of_account_name)
                    ->where('chart_of_account_transaction_details.transaction_date','>=',$request->from_date)
                    ->where('chart_of_account_transaction_details.transaction_date','<=',$request->to_date)
                    ->where('chart_of_account_transactions.store_id','=',$request->store_id)
                    ->where('chart_of_account_transaction_details.chart_of_account_name','=','Boss')
                    ->select(DB::raw('SUM(chart_of_account_transaction_details.credit) as credit'))
                    ->first();
            }else{
                $boss_credit_info = DB::table("chart_of_account_transaction_details")
                    ->join('chart_of_account_transactions','chart_of_account_transaction_details.chart_of_account_transaction_id','=','chart_of_account_transactions.id')
                    ->leftJoin('voucher_types','chart_of_account_transactions.voucher_type_id','=','voucher_types.id')
                    //->where('chart_of_account_transaction_details.chart_of_account_name',$request->chart_of_account_name)
                    ->where('chart_of_account_transactions.store_id','=',$request->store_id)
                    ->where('chart_of_account_transaction_details.chart_of_account_name','=','Boss')
                    ->select(DB::raw('SUM(chart_of_account_transaction_details.credit) as credit'))
                    ->first();
            }
        }else{
            if($request->from_date && $request->to_date){
                $boss_credit_info = DB::table("chart_of_account_transaction_details")
                    ->join('chart_of_account_transactions','chart_of_account_transaction_details.chart_of_account_transaction_id','=','chart_of_account_transactions.id')
                    ->leftJoin('voucher_types','chart_of_account_transactions.voucher_type_id','=','voucher_types.id')
                    //->where('chart_of_account_transaction_details.chart_of_account_name',$request->chart_of_account_name)
                    ->where('chart_of_account_transaction_details.transaction_date','>=',$request->from_date)
                    ->where('chart_of_account_transaction_details.transaction_date','<=',$request->to_date)
                    ->where('chart_of_account_transaction_details.chart_of_account_name','=','Boss')
                    ->select(DB::raw('SUM(chart_of_account_transaction_details.credit) as credit'))
                    ->first();
            }else{
                $boss_credit_info = DB::table("chart_of_account_transaction_details")
                    ->join('chart_of_account_transactions','chart_of_account_transaction_details.chart_of_account_transaction_id','=','chart_of_account_transactions.id')
                    ->leftJoin('voucher_types','chart_of_account_transactions.voucher_type_id','=','voucher_types.id')
                    //->where('chart_of_account_transaction_details.chart_of_account_name',$request->chart_of_account_name)
                    ->where('chart_of_account_transaction_details.chart_of_account_name','=','Boss')
                    ->select(DB::raw('SUM(chart_of_account_transaction_details.credit) as credit'))
                    ->first();
            }
        }


//        $ledger_data = [
//            'sale_info' => $sale_info,
//            'sale_return_info' => $sale_return_info,
//            'bkash_credit_info' => $bkash_credit_info,
//            'card_credit_info' => $card_credit_info,
//            'rocket_credit_info' => $rocket_credit_info,
//            'upay_credit_info' => $upay_credit_info,
//            'employee_salary_credit_info' => $employee_salary_credit_info,
//            'boss_credit_info' => $boss_credit_info,
//            'PreBalance' => $PreBalance,
//            'preDebCre' => $preDebCre,
//            'pre_debit' => $pre_debit,
//            'pre_credit' => $pre_credit,
//            'chart_of_account_name' => $chart_of_account_name,
//            'from_date' => $from_date,
//            'to_date' => $to_date,
//        ];

        $ledger_data = [
            'sale_info' => [
                'debit' => $sale_info->debit,
                'credit' => 0
            ],
            'sale_return_info' => [
                'debit' => 0,
                'credit' => $sale_return_info->credit ? $sale_return_info->credit : 0
            ],
            'bkash_credit_info' => [
                'debit' => 0,
                'credit' => $bkash_credit_info->credit ? $bkash_credit_info->credit :0
            ],
            'card_credit_info' => [
                'debit' => 0,
                'credit' => $card_credit_info->credit ? $card_credit_info->credit : 0
            ],
            'rocket_credit_info' => [
                'debit' => 0,
                'credit' => $rocket_credit_info->credit ? $rocket_credit_info->credit : 0
            ],
            'upay_credit_info' => [
                'debit' => 0,
                'credit' => $upay_credit_info->credit ? $upay_credit_info->credit : 0
            ],
            'employee_salary_credit_info' => [
                'debit' => 0,
                'credit' => $employee_salary_credit_info->credit ? $employee_salary_credit_info->credit : 0
            ],
            'boss_credit_info' => [
                'debit' => 0,
                'credit' => $boss_credit_info->credit ? $boss_credit_info->credit : 0
            ],
            'PreBalance' => $PreBalance,
            'preDebCre' => $preDebCre,
            'pre_debit' => $pre_debit,
            'pre_credit' => $pre_credit,
            'chart_of_account_name' => $chart_of_account_name,
            'from_date' => $from_date,
            'to_date' => $to_date,
        ];
        return response()->json(['success'=>true,'response' => $ledger_data], $this->successStatus);


    }

    public function balanceSheet(Request $request){
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

            return response()->json($response, $this-> validationStatus);
        }

        if($request->store_id != 0){
            $sum_asset_amount = DB::table('chart_of_account_transaction_details')
                ->where('chart_of_account_type','=','A')
                ->where('year','=',$request->year)
                ->where('month','<=',$request->month)
                ->where('store_id',$request->store_id)
                ->select(DB::raw('SUM(debit) as total_debit'),DB::raw('SUM(credit) as total_credit'))
                ->first();

            $sum_liability_amount = DB::table('chart_of_account_transaction_details')
                ->where('chart_of_account_type','=','L')
                ->where('year','=',$request->year)
                ->where('month','<=',$request->month)
                ->where('store_id',$request->store_id)
                ->select(DB::raw('SUM(debit) as total_debit'),DB::raw('SUM(credit) as total_credit'))
                ->first();

            $sum_income_amount = DB::table('chart_of_account_transaction_details')
                ->where('chart_of_account_type','=','I')
                ->where('year','=',$request->year)
                ->where('month','<=',$request->month)
                ->where('store_id',$request->store_id)
                ->select(DB::raw('SUM(debit) as total_debit'),DB::raw('SUM(credit) as total_credit'))
                ->first();

            $sum_expense_amount = DB::table('chart_of_account_transaction_details')
                ->where('chart_of_account_type','=','E')
                ->where('year','=',$request->year)
                ->where('month','<=',$request->month)
                ->where('store_id',$request->store_id)
                ->select(DB::raw('SUM(debit) as total_debit'),DB::raw('SUM(credit) as total_credit'))
                ->first();



            $sum_equity_amount = DB::table('chart_of_account_transaction_details')
                ->where('chart_of_account_type','=','EL')
                ->where('year','=',$request->year)
                ->where('month','<=',$request->month)
                ->where('store_id',$request->store_id)
                ->select(DB::raw('SUM(debit) as total_debit'),DB::raw('SUM(credit) as total_credit'))
                ->first();

            $sum_drawing_amount = DB::table('chart_of_account_transaction_details')
                ->where('chart_of_account_type','=','DL')
                ->where('year','=',$request->year)
                ->where('month','<=',$request->month)
                ->where('store_id',$request->store_id)
                ->select(DB::raw('SUM(debit) as total_debit'),DB::raw('SUM(credit) as total_credit'))
                ->first();
        }else{
            $sum_asset_amount = DB::table('chart_of_account_transaction_details')
                ->where('chart_of_account_type','=','A')
                ->where('year','=',$request->year)
                ->where('month','<=',$request->month)
                ->select(DB::raw('SUM(debit) as total_debit'),DB::raw('SUM(credit) as total_credit'))
                ->first();

            $sum_liability_amount = DB::table('chart_of_account_transaction_details')
                ->where('chart_of_account_type','=','L')
                ->where('year','=',$request->year)
                ->where('month','<=',$request->month)
                ->select(DB::raw('SUM(debit) as total_debit'),DB::raw('SUM(credit) as total_credit'))
                ->first();

            $sum_income_amount = DB::table('chart_of_account_transaction_details')
                ->where('chart_of_account_type','=','I')
                ->where('year','=',$request->year)
                ->where('month','<=',$request->month)
                ->select(DB::raw('SUM(debit) as total_debit'),DB::raw('SUM(credit) as total_credit'))
                ->first();

            $sum_expense_amount = DB::table('chart_of_account_transaction_details')
                ->where('chart_of_account_type','=','E')
                ->where('year','=',$request->year)
                ->where('month','<=',$request->month)
                ->select(DB::raw('SUM(debit) as total_debit'),DB::raw('SUM(credit) as total_credit'))
                ->first();



            $sum_equity_amount = DB::table('chart_of_account_transaction_details')
                ->where('chart_of_account_type','=','EL')
                ->where('year','=',$request->year)
                ->where('month','<=',$request->month)
                ->select(DB::raw('SUM(debit) as total_debit'),DB::raw('SUM(credit) as total_credit'))
                ->first();

            $sum_drawing_amount = DB::table('chart_of_account_transaction_details')
                ->where('chart_of_account_type','=','DL')
                ->where('year','=',$request->year)
                ->where('month','<=',$request->month)
                ->select(DB::raw('SUM(debit) as total_debit'),DB::raw('SUM(credit) as total_credit'))
                ->first();
        }



        $response = [
            'sum_asset_amount' => $sum_asset_amount,
            'sum_liability_amount' => $sum_liability_amount,
            'sum_oe_amount' => [
                'sum_equity_amount' => $sum_equity_amount,
                'sum_income_amount' => $sum_income_amount,
                'sum_expense_amount' => $sum_expense_amount,
                'sum_drawing_amount' => $sum_drawing_amount,
            ]
        ];

        if($sum_asset_amount)
        {
            return response()->json(['success'=>true,'response' => $response], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Chart Of Account Transaction Found!'], $this->failStatus);
        }
    }














    // backup database
    public function backupDatabase(Request $request){
        // Database configuration
        $host = "127.0.0.1";
        $username = "erp_boibichitra_user";
        $password = "mGubJAw6e+m834Bs";
        $database_name = "erp_boibichitra_db";

        // Get connection object and set the charset
        $conn = mysqli_connect($host, $username, $password, $database_name);
        $conn->set_charset("utf8");


        // Get All Table Names From the Database
        $tables = array();
        $sql = "SHOW TABLES";
        $result = mysqli_query($conn, $sql);

        while ($row = mysqli_fetch_row($result)) {
            $tables[] = $row[0];
        }

        $sqlScript = "";
        foreach ($tables as $table) {

            // Prepare SQLscript for creating table structure
            $query = "SHOW CREATE TABLE $table";
            $result = mysqli_query($conn, $query);
            $row = mysqli_fetch_row($result);

            $sqlScript .= "\n\n" . $row[1] . ";\n\n";


            $query = "SELECT * FROM $table";
            $result = mysqli_query($conn, $query);

            $columnCount = mysqli_num_fields($result);

            // Prepare SQLscript for dumping data for each table
            for ($i = 0; $i < $columnCount; $i ++) {
                while ($row = mysqli_fetch_row($result)) {
                    $sqlScript .= "INSERT INTO $table VALUES(";
                    for ($j = 0; $j < $columnCount; $j ++) {
                        $row[$j] = $row[$j];

                        if (isset($row[$j])) {
                            $sqlScript .= '"' . $row[$j] . '"';
                        } else {
                            $sqlScript .= '""';
                        }
                        if ($j < ($columnCount - 1)) {
                            $sqlScript .= ',';
                        }
                    }
                    $sqlScript .= ");\n";
                }
            }

            $sqlScript .= "\n";
        }

        if(!empty($sqlScript))
        {
            // Save the SQL script to a backup file
            $backup_file_name = $database_name . '_backup_' . time() . '.sql';
            $fileHandler = fopen($backup_file_name, 'w+');
            $number_of_lines = fwrite($fileHandler, $sqlScript);
            fclose($fileHandler);

            // Download the SQL backup file to the browser
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename=' . basename($backup_file_name));
            header('Content-Transfer-Encoding: binary');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($backup_file_name));
            ob_clean();
            flush();
            readfile($backup_file_name);
            exec('rm ' . $backup_file_name);
        }
    }

//    public function sum_sub_total(){
//        $sum_price = DB::table('product_purchase_details')
//            //->where('stock_transfer_id', $stock_transfer_id)
//            ->select('product_id',DB::raw('SUM(sub_total) as total_amount'))
//            ->groupBy('product_id')
//            ->orderBy('total_amount', 'DESC')
//            ->get();
//
//        if($sum_price)
//        {
//            return response()->json(['success'=>true,'response' => $sum_price], $this->successStatus);
//        }else{
//            return response()->json(['success'=>false,'response'=>'No Sum Price Found!'], $this->failStatus);
//        }
//    }




}
