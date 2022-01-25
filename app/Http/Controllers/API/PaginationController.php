<?php

namespace App\Http\Controllers\API;

use App\Attendance;
use App\ChartOfAccount;
use App\ChartOfAccountTransaction;
use App\ChartOfAccountTransactionDetail;
use App\Department;
use App\Designation;
use App\Employee;
use App\EmployeeOfficeInformation;
use App\EmployeeSalaryInformation;
use App\ExpenseCategory;
use App\Helpers\APIHelpers;
use App\Helpers\UserInfo;
use App\Holiday;
use App\Http\Controllers\Controller;
use App\Http\Resources\ProductPOSSaleCollection;
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
use App\ProductSaleExchange;
use App\ProductSaleExchangeDetail;
use App\ProductSalePreviousDetail;
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
use App\StoreExpense;
use App\StoreStockReturn;
use App\StoreStockReturnDetail;
use App\TangibleAssets;
use App\Transaction;
use App\User;
use App\VoucherType;
use App\Warehouse;
use App\WarehouseCurrentStock;
use App\WarehouseProductDamage;
use App\WarehouseProductDamageDetail;
use App\WarehouseStoreCurrentStock;
use App\Weekend;
use Illuminate\Database\Eloquent\Model;
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

class PaginationController extends Controller
{
    public $successStatus = 200;
    public $authStatus = 401;
    public $failStatus = 402;
    public $ExistsStatus = 403;
    public $validationStatus = 404;

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

    public function warehouseCurrentStockListPagination(Request $request){
        //return response()->json(['success'=>true,'response' => $request->all()], $this->successStatus);

        $warehouse_stock_product = DB::table('warehouse_current_stocks')
            ->join('warehouses','warehouse_current_stocks.warehouse_id','warehouses.id')
            ->leftJoin('products','warehouse_current_stocks.product_id','products.id')
            ->leftJoin('product_units','products.product_unit_id','product_units.id')
            ->leftJoin('product_brands','products.product_brand_id','product_brands.id')
            ->where('warehouse_current_stocks.warehouse_id',$request->warehouse_id)
            ->select('warehouse_current_stocks.*','warehouses.name as warehouse_name','products.name as product_name','products.purchase_price','products.selling_price','products.whole_sale_price','products.item_code','products.barcode','products.image','products.vat_status','products.vat_percentage','products.vat_amount','product_units.id as product_unit_id','product_units.name as product_unit_name','product_brands.id as product_brand_id','product_brands.name as product_brand_name')
            //->select('warehouses.name as warehouse_name')
            ->get();


        if($warehouse_stock_product)
        {
            $success['warehouse_current_stock_list'] =  $warehouse_stock_product;
            return response()->json(['success'=>true,'response' => $success], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Warehouse Current Stock List Found!'], $this->failStatus);
        }
    }

    public function warehouseCurrentStockListPaginationTwo(Request $request){

        $warehouse_stock_product = DB::table('warehouse_current_stocks')
            ->join('warehouses','warehouse_current_stocks.warehouse_id','warehouses.id')
            ->leftJoin('products','warehouse_current_stocks.product_id','products.id')
            ->leftJoin('product_units','products.product_unit_id','product_units.id')
            ->leftJoin('product_brands','products.product_brand_id','product_brands.id')
            ->where('warehouse_current_stocks.warehouse_id',$request->warehouse_id)
            ->select('warehouse_current_stocks.*','warehouses.name as warehouse_name','products.name as product_name','products.purchase_price','products.selling_price','products.whole_sale_price','products.item_code','products.barcode','products.image','products.vat_status','products.vat_percentage','products.vat_amount','product_units.id as product_unit_id','product_units.name as product_unit_name','product_brands.id as product_brand_id','product_brands.name as product_brand_name')
            //->select('warehouses.name as warehouse_name')
            ->paginate(12);


        if($warehouse_stock_product)
        {
            $success['warehouse_current_stock_list'] =  $warehouse_stock_product;
            return response()->json(['success'=>true,'response' => $success], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Warehouse Current Stock List Found!'], $this->failStatus);
        }
    }

    public function warehouseCurrentStockListPaginationTwoWithSearch(Request $request){
        if($request->search){
            $warehouse_stock_product = DB::table('warehouse_current_stocks')
                ->join('warehouses','warehouse_current_stocks.warehouse_id','warehouses.id')
                ->leftJoin('products','warehouse_current_stocks.product_id','products.id')
                ->leftJoin('product_units','products.product_unit_id','product_units.id')
                ->leftJoin('product_brands','products.product_brand_id','product_brands.id')
                ->where('warehouse_current_stocks.warehouse_id',$request->warehouse_id)
                ->where('products.name','like','%'.$request->search.'%')
                ->orWhere('product_units.name','like','%'.$request->search.'%')
                ->select('warehouse_current_stocks.*','warehouses.name as warehouse_name','products.name as product_name','products.purchase_price','products.selling_price','products.whole_sale_price','products.item_code','products.barcode','products.image','products.vat_status','products.vat_percentage','products.vat_amount','product_units.id as product_unit_id','product_units.name as product_unit_name','product_brands.id as product_brand_id','product_brands.name as product_brand_name')
                ->paginate(12);
        }else{
            $warehouse_stock_product = DB::table('warehouse_current_stocks')
                ->join('warehouses','warehouse_current_stocks.warehouse_id','warehouses.id')
                ->leftJoin('products','warehouse_current_stocks.product_id','products.id')
                ->leftJoin('product_units','products.product_unit_id','product_units.id')
                ->leftJoin('product_brands','products.product_brand_id','product_brands.id')
                ->where('warehouse_current_stocks.warehouse_id',$request->warehouse_id)
                ->select('warehouse_current_stocks.*','warehouses.name as warehouse_name','products.name as product_name','products.purchase_price','products.selling_price','products.whole_sale_price','products.item_code','products.barcode','products.image','products.vat_status','products.vat_percentage','products.vat_amount','product_units.id as product_unit_id','product_units.name as product_unit_name','product_brands.id as product_brand_id','product_brands.name as product_brand_name')
                ->paginate(12);
        }


        if($warehouse_stock_product)
        {
            $success['warehouse_current_stock_list'] =  $warehouse_stock_product;
            return response()->json(['success'=>true,'response' => $success], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Warehouse Current Stock List Found!'], $this->failStatus);
        }
    }

    public function warehouseCurrentStockListPaginationBarcode(Request $request){

        $warehouse_stock_product = DB::table('warehouse_current_stocks')
            ->join('warehouses','warehouse_current_stocks.warehouse_id','warehouses.id')
            ->leftJoin('products','warehouse_current_stocks.product_id','products.id')
            ->leftJoin('product_units','products.product_unit_id','product_units.id')
            ->leftJoin('product_brands','products.product_brand_id','product_brands.id')
            ->where('warehouse_current_stocks.warehouse_id',$request->warehouse_id)
            ->where('warehouse_current_stocks.current_stock','>',0)
            ->where('products.barcode',$request->barcode)
            ->select('warehouse_current_stocks.*','warehouses.name as warehouse_name','products.name as product_name','products.purchase_price','products.selling_price','products.whole_sale_price','products.item_code','products.barcode','products.image','products.vat_status','products.vat_percentage','products.vat_amount','product_units.id as product_unit_id','product_units.name as product_unit_name','product_brands.id as product_brand_id','product_brands.name as product_brand_name')
            ->paginate(1);

        if($warehouse_stock_product)
        {
            $success['warehouse_current_stock_list'] =  $warehouse_stock_product;
            return response()->json(['success'=>true,'response' => $success], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Warehouse Current Stock List Found!'], $this->failStatus);
        }
    }

    public function warehouseCurrentStockListPaginationItemcode(Request $request){

        $warehouse_stock_product = DB::table('warehouse_current_stocks')
            ->join('warehouses','warehouse_current_stocks.warehouse_id','warehouses.id')
            ->leftJoin('products','warehouse_current_stocks.product_id','products.id')
            ->leftJoin('product_units','products.product_unit_id','product_units.id')
            ->leftJoin('product_brands','products.product_brand_id','product_brands.id')
            ->where('warehouse_current_stocks.warehouse_id',$request->warehouse_id)
            ->where('warehouse_current_stocks.current_stock','>',0)
            ->where('products.item_code',$request->item_code)
            ->select('warehouse_current_stocks.*','warehouses.name as warehouse_name','products.name as product_name','products.purchase_price','products.selling_price','products.whole_sale_price','products.item_code','products.barcode','products.image','products.vat_status','products.vat_percentage','products.vat_amount','product_units.id as product_unit_id','product_units.name as product_unit_name','product_brands.id as product_brand_id','product_brands.name as product_brand_name')
            ->paginate(1);

        if($warehouse_stock_product)
        {
            $success['warehouse_current_stock_list'] =  $warehouse_stock_product;
            return response()->json(['success'=>true,'response' => $success], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Warehouse Current Stock List Found!'], $this->failStatus);
        }
    }

    public function warehouseCurrentStockListPaginationProductName(Request $request){

        $warehouse_stock_product = DB::table('warehouse_current_stocks')
            ->join('warehouses','warehouse_current_stocks.warehouse_id','warehouses.id')
            ->leftJoin('products','warehouse_current_stocks.product_id','products.id')
            ->leftJoin('product_units','products.product_unit_id','product_units.id')
            ->leftJoin('product_brands','products.product_brand_id','product_brands.id')
            ->where('warehouse_current_stocks.warehouse_id',$request->warehouse_id)
            ->where('warehouse_current_stocks.current_stock','>',0)
            ->where('products.name','like','%'.$request->name.'%')
            ->select('warehouse_current_stocks.*','warehouses.name as warehouse_name','products.name as product_name','products.purchase_price','products.selling_price','products.whole_sale_price','products.item_code','products.barcode','products.image','products.vat_status','products.vat_percentage','products.vat_amount','product_units.id as product_unit_id','product_units.name as product_unit_name','product_brands.id as product_brand_id','product_brands.name as product_brand_name')
            ->paginate(12);

        if($warehouse_stock_product)
        {
            $success['warehouse_current_stock_list'] =  $warehouse_stock_product;
            return response()->json(['success'=>true,'response' => $success], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Warehouse Current Stock List Found!'], $this->failStatus);
        }
    }

    public function storeCurrentStockListPagination(Request $request){

//        $this->validate($request, [
//            'store_id'=> 'required'
//        ]);
        if($request->search){
            $store_stock_product = DB::table('warehouse_store_current_stocks')
                ->join('stores','warehouse_store_current_stocks.store_id','stores.id')
                ->leftJoin('products','warehouse_store_current_stocks.product_id','products.id')
                ->leftJoin('product_units','products.product_unit_id','product_units.id')
                ->leftJoin('product_brands','products.product_brand_id','product_brands.id')
                ->where('warehouse_store_current_stocks.store_id',$request->store_id)
                ->where('products.name','like','%'.$request->search.'%')
                ->select('warehouse_store_current_stocks.*','stores.name as store_name','products.name as product_name','products.purchase_price','products.whole_sale_price','products.selling_price','products.item_code','products.barcode','products.image','products.vat_status','products.vat_percentage','products.vat_amount','products.vat_whole_amount','product_units.id as product_unit_id','product_units.name as product_unit_name','product_brands.id as product_brand_id','product_brands.name as product_brand_name')
                ->paginate(12);
        }else{
            $store_stock_product = DB::table('warehouse_store_current_stocks')
                ->join('stores','warehouse_store_current_stocks.store_id','stores.id')
                ->leftJoin('products','warehouse_store_current_stocks.product_id','products.id')
                ->leftJoin('product_units','products.product_unit_id','product_units.id')
                ->leftJoin('product_brands','products.product_brand_id','product_brands.id')
                ->where('warehouse_store_current_stocks.store_id',$request->store_id)
                ->select('warehouse_store_current_stocks.*','stores.name as store_name','products.name as product_name','products.purchase_price','products.whole_sale_price','products.selling_price','products.item_code','products.barcode','products.image','products.vat_status','products.vat_percentage','products.vat_amount','products.vat_whole_amount','product_units.id as product_unit_id','product_units.name as product_unit_name','product_brands.id as product_brand_id','product_brands.name as product_brand_name')
                ->paginate(12);
        }

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

    public function productPOSSaleListPagination(){
        $product_pos_sales = DB::table('product_sales')
            ->leftJoin('users','product_sales.user_id','users.id')
            ->leftJoin('parties','product_sales.party_id','parties.id')
            ->leftJoin('warehouses','product_sales.warehouse_id','warehouses.id')
            ->leftJoin('stores','product_sales.store_id','stores.id')
            ->leftJoin('transactions','product_sales.invoice_no','transactions.invoice_no')
            ->where('product_sales.sale_type','pos_sale')
            ->select('product_sales.id','product_sales.invoice_no','product_sales.sub_total','product_sales.discount_type','product_sales.discount_percent','product_sales.discount_amount','product_sales.total_vat_amount','product_sales.total_amount','product_sales.paid_amount','product_sales.due_amount','product_sales.sale_date_time','users.name as user_name','parties.id as customer_id','parties.name as customer_name','warehouses.id as warehouse_id','warehouses.name as warehouse_name','stores.id as store_id','stores.name as store_name','stores.address as store_address','stores.phone','transactions.payment_type')
            ->orderBy('product_sales.id','desc')
            ->paginate(12);

        if(count($product_pos_sales) > 0)
        {
            $success['product_pos_sales'] =  $product_pos_sales;

//            $product_pos_sale_arr = [];
//            foreach ($product_pos_sales as $data){
//                $payment_type = DB::table('transactions')->where('ref_id',$data->id)->where('transaction_type','pos_sale')->pluck('payment_type')->first();
//
//                $nested_data['id']=$data->id;
//                $nested_data['invoice_no']=$data->invoice_no;
//                $nested_data['discount_type']=$data->discount_type;
//                $nested_data['discount_amount']=$data->discount_amount;
//                $nested_data['total_vat_amount']=$data->total_vat_amount;
//                $nested_data['total_amount']=$data->total_amount;
//                $nested_data['paid_amount']=$data->paid_amount;
//                $nested_data['due_amount']=$data->due_amount;
//                $nested_data['sale_date_time']=$data->sale_date_time;
//                $nested_data['user_name']=$data->user_name;
//                $nested_data['customer_id']=$data->customer_id;
//                $nested_data['customer_name']=$data->customer_name;
//                $nested_data['warehouse_id']=$data->warehouse_id;
//                $nested_data['warehouse_name']=$data->warehouse_name;
//                $nested_data['store_id']=$data->store_id;
//                $nested_data['store_name']=$data->store_name;
//                $nested_data['store_address']=$data->store_address;
//                $nested_data['phone']=$data->phone;
//                $nested_data['payment_type']=$payment_type;
//
//                array_push($product_pos_sale_arr,$nested_data);
//            }
//            $success['product_pos_sales'] =  $product_pos_sale_arr;
            return response()->json(['success'=>true,'response' => $success], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Product Whole Sale List Found!'], $this->failStatus);
        }
    }

    public function productPOSSaleListPaginationWithSearch(Request $request){
//        try {
            $user_id = Auth::user()->id;
            $currentUserDetails = currentUserDetails($user_id);
            $role = $currentUserDetails['role'];
            $warehouse_id = $currentUserDetails['warehouse_id'];
            $store_id = $currentUserDetails['store_id'];

            if($role === 'admin'){
                if($request->search){
                    $product_pos_sales = ProductSale::join('parties','product_sales.party_id','parties.id')
                        ->where('product_sales.sale_type','pos_sale')
                        ->where(function ($q) use ($request){
                            $q->where('product_sales.invoice_no','like','%'.$request->search.'%')
                                ->orWhere('parties.name','like','%'.$request->search.'%');
                        })
                        ->select(
                            'product_sales.id',
                            'product_sales.invoice_no',
                            'product_sales.sub_total',
                            'product_sales.miscellaneous_comment',
                            'product_sales.miscellaneous_charge',
                            'product_sales.discount_type',
                            'product_sales.discount_percent',
                            'product_sales.discount_amount',
                            'product_sales.total_vat_amount',
                            'product_sales.total_amount',
                            'product_sales.paid_amount',
                            'product_sales.due_amount',
                            'product_sales.sale_date',
                            'product_sales.sale_date_time',
                            'product_sales.user_id',
                            'product_sales.party_id',
                            'product_sales.warehouse_id',
                            'product_sales.store_id'
                        )
                        ->latest('product_sales.id','desc')->paginate(12);
                    return new ProductPOSSaleCollection($product_pos_sales);
                }else{
                    return new ProductPOSSaleCollection(
                        ProductSale::where('sale_type','pos_sale')
                            ->latest()->paginate(12)
                    );
                }
            }else{
                if($request->search){
                    $product_pos_sales = ProductSale::join('parties','product_sales.party_id','parties.id')
                        ->join('stores','product_sales.store_id','stores.id')
                        ->where('product_sales.store_id1',$store_id)
                        ->where('product_sales.sale_type','pos_sale')
                        ->where(function ($q) use ($request){
                            $q->where('product_sales.invoice_no','like','%'.$request->search.'%')
                                ->orWhere('parties.name','like','%'.$request->search.'%');
                        })
                        ->select(
                            'product_sales.id',
                            'product_sales.invoice_no',
                            'product_sales.sub_total',
                            'product_sales.miscellaneous_comment',
                            'product_sales.miscellaneous_charge',
                            'product_sales.discount_type',
                            'product_sales.discount_percent',
                            'product_sales.discount_amount',
                            'product_sales.total_vat_amount',
                            'product_sales.total_amount',
                            'product_sales.paid_amount',
                            'product_sales.due_amount',
                            'product_sales.sale_date',
                            'product_sales.sale_date_time',
                            'product_sales.user_id',
                            'product_sales.party_id',
                            'product_sales.warehouse_id',
                            'product_sales.store_id'
                        )
                        ->latest('product_sales.id','desc')->paginate(12);

                }else{
                    $product_pos_sales =  ProductSale::where('sale_type','pos_sale')
                            ->where('store_id',$store_id)
                            ->latest()->paginate(12);
                }
                if($product_pos_sales){
                    return new ProductPOSSaleCollection($product_pos_sales);
                }else{
                    $response = APIHelpers::createAPIResponse(true,404,'No POS Sale Found.',null);
                    return response()->json($response,404);
                }
            }
//        } catch (\Exception $e) {
//            //return $e->getMessage();
//            $response = APIHelpers::createAPIResponse(false,500,'Internal Server Error.',null);
//            return response()->json($response,500);
//        }
    }

    public function warehouseCurrentStockListPaginationWithOutZero(Request $request){

        $warehouse_stock_product = DB::table('warehouse_current_stocks')
            ->join('warehouses','warehouse_current_stocks.warehouse_id','warehouses.id')
            ->leftJoin('products','warehouse_current_stocks.product_id','products.id')
            ->leftJoin('product_units','products.product_unit_id','product_units.id')
            ->leftJoin('product_brands','products.product_brand_id','product_brands.id')
            ->where('warehouse_current_stocks.warehouse_id',$request->warehouse_id)
            ->where('warehouse_current_stocks.current_stock','>',0)
            ->select('warehouse_current_stocks.*','warehouses.name as warehouse_name','products.name as product_name','products.purchase_price','products.selling_price','products.whole_sale_price','products.item_code','products.barcode','products.image','products.vat_status','products.vat_percentage','products.vat_amount','product_units.id as product_unit_id','product_units.name as product_unit_name','product_brands.id as product_brand_id','product_brands.name as product_brand_name')
            ->paginate(12);

        $warehouse_stock_total_amounts = DB::table('warehouse_current_stocks')
            ->leftJoin('products','warehouse_current_stocks.product_id','products.id')
            ->where('warehouse_current_stocks.warehouse_id',$request->warehouse_id)
            ->where('warehouse_current_stocks.current_stock','>',0)
            //->select(DB::raw('SUM(products.selling_price) as sum_total_amount'))
            ->select('warehouse_current_stocks.*','products.purchase_price','products.selling_price')
            ->get();

        $warehouse_stock_sum_total_amount = 0;
        if(count($warehouse_stock_total_amounts) > 0){
            foreach ($warehouse_stock_total_amounts as $warehouse_stock_amount){
                $warehouse_stock_sum_total_amount += ($warehouse_stock_amount->current_stock*$warehouse_stock_amount->selling_price);
            }
        }

        if($warehouse_stock_product)
        {
            $success['warehouse_current_stock_list'] =  $warehouse_stock_product;
            $success['warehouse_stock_sum_total_amount'] =  $warehouse_stock_sum_total_amount;
            return response()->json(['success'=>true,'response' => $success], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Warehouse Current Stock List Found!'], $this->failStatus);
        }
    }

    public function storeCurrentStockListPaginationWithOutZero(Request $request){
        try {
            $user_id = Auth::user()->id;
            $currentUserDetails = currentUserDetails($user_id);
            $role = $currentUserDetails['role'];
            $warehouse_id = $currentUserDetails['warehouse_id'];
            $store_id = $currentUserDetails['store_id'];

            $store_stock_product = DB::table('warehouse_store_current_stocks')
                ->join('warehouses','warehouse_store_current_stocks.warehouse_id','warehouses.id')
                ->leftJoin('products','warehouse_store_current_stocks.product_id','products.id')
                ->leftJoin('product_units','products.product_unit_id','product_units.id')
                ->leftJoin('product_brands','products.product_brand_id','product_brands.id')
                ->where('warehouse_store_current_stocks.store_id',$request->store_id)
                //->where('warehouse_store_current_stocks.store_id',$store_id)
                ->where('warehouse_store_current_stocks.current_stock','>',0)
                ->select('warehouse_store_current_stocks.*','warehouses.name as warehouse_name','products.name as product_name','products.purchase_price','products.whole_sale_price','products.selling_price','products.item_code','products.barcode','products.image','products.vat_status','products.vat_percentage','products.vat_amount','products.vat_whole_amount','product_units.id as product_unit_id','product_units.name as product_unit_name','product_brands.id as product_brand_id','product_brands.name as product_brand_name')
                ->paginate(12);

            $warehouse_store_stock_total_amounts = DB::table('warehouse_store_current_stocks')
                ->leftJoin('products','warehouse_store_current_stocks.product_id','products.id')
                ->where('warehouse_store_current_stocks.store_id',$request->store_id)
                //->where('warehouse_store_current_stocks.store_id',$store_id)
                ->where('warehouse_store_current_stocks.current_stock','>',0)
                ->select('warehouse_store_current_stocks.*','products.purchase_price','products.selling_price')
                ->get();

            $warehouse_store_stock_sum_total_amount = 0;
            if(count($warehouse_store_stock_total_amounts) > 0){
                foreach ($warehouse_store_stock_total_amounts as $warehouse_store_stock_amount){
                    $warehouse_store_stock_sum_total_amount += ($warehouse_store_stock_amount->current_stock*$warehouse_store_stock_amount->selling_price);
                }
            }

            if($store_stock_product)
            {
                $store_info = DB::table('stores')
                    ->where('id',$request->store_id)
                    ->select('name','phone','email','address')
                    ->first();

                $success['store_info'] =  $store_info;
                $success['store_current_stock_list'] =  $store_stock_product;
                $success['warehouse_store_stock_sum_total_amount'] =  $warehouse_store_stock_sum_total_amount;
//                return response()->json(['success'=>true,'response' => $success], $this->successStatus);

                $response = APIHelpers::createAPIResponse(false,200,'',$success);
                return response()->json($response,200);
            }else{
                $response = APIHelpers::createAPIResponse(true,404,'No Store Current Stock Found.',null);
                return response()->json($response,404);
            }
        } catch (\Exception $e) {
            //return $e->getMessage();
            $response = APIHelpers::createAPIResponse(false,500,'Internal Server Error.',null);
            return response()->json($response,500);
        }
    }
}
