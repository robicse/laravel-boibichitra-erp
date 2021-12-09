<?php
//filter products published
use App\User;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

//helper file test check
if (! function_exists('test_helper')) {
    function test_helper() {
        dd('test helper');
    }
}

// today purchase sum
if (! function_exists('todayPurchase')) {
    function todayPurchase() {
        $today_purchase_history = DB::table('product_purchases')
            ->where('purchase_date', date('Y-m-d'))
            ->select(DB::raw('SUM(total_amount) as today_purchase'))
            ->first();

        return $today_purchase_history->today_purchase;
    }
}

// total purchase sum
if (! function_exists('totalPurchase')) {
    function totalPurchase() {
        $total_purchase_history = DB::table('product_purchases')
            ->select(DB::raw('SUM(total_amount) as total_purchase'))
            ->first();

        return $total_purchase_history->total_purchase;
    }
}

// today purchase return sum
if (! function_exists('todayPurchaseReturn')) {
    function todayPurchaseReturn() {
        $today_purchase_return_history = DB::table('product_purchase_returns')
            ->where('product_purchase_return_date', date('Y-m-d'))
            ->select(DB::raw('SUM(total_amount) as today_purchase_return'))
            ->first();

        return $today_purchase_return_history->today_purchase_return;
    }
}

// total purchase return sum
if (! function_exists('totalPurchaseReturn')) {
    function totalPurchaseReturn() {
        $total_purchase_return_history = DB::table('product_purchase_returns')
            ->select(DB::raw('SUM(total_amount) as total_purchase_return'))
            ->first();

        return $total_purchase_return_history->total_purchase_return;
    }
}

// today sale sum
if (! function_exists('todaySale')) {
    function todaySale() {
        $today_sale_history = DB::table('product_sales')
            ->where('sale_date', date('Y-m-d'))
            ->select(DB::raw('SUM(total_amount) as today_sale'),DB::raw('SUM(total_vat_amount) as today_sale_vat_amount'))
            ->first();

        return $today_sale_history->today_sale - $today_sale_history->today_sale_vat_amount;
    }
}

// total sale sum
if (! function_exists('totalSale')) {
    function totalSale() {
        $total_sale_history = DB::table('product_sales')
            ->select(DB::raw('SUM(total_amount) as total_sale'),DB::raw('SUM(total_vat_amount) as total_sale_vat_amount'))
            ->first();

        return $total_sale_history->total_sale - $total_sale_history->total_sale_vat_amount;
    }
}

// today sale return sum
if (! function_exists('todaySaleReturn')) {
    function todaySaleReturn() {
        $today_sale_return_history = DB::table('product_sale_returns')
            ->join('product_sales','product_sale_returns.product_sale_invoice_no','product_sales.invoice_no')
            ->where('product_sale_returns.product_sale_return_date', date('Y-m-d'))
            ->where('product_sales.sale_type', 'pos_sale')
            ->select(DB::raw('SUM(product_sale_returns.total_amount) as today_sale_return'))
            ->first();

        return $today_sale_return_history->today_sale_return;
    }
}

// total sale return sum
if (! function_exists('totalSaleReturn')) {
    function totalSaleReturn() {
        $total_sale_return_history = DB::table('product_sale_returns')
            ->join('product_sales','product_sale_returns.product_sale_invoice_no','product_sales.invoice_no')
            ->where('product_sales.sale_type', 'pos_sale')
            ->select(DB::raw('SUM(product_sale_returns.total_amount) as total_sale_return'))
            ->first();

        return $total_sale_return_history->total_sale_return;
    }
}

// today sale sum for profit calculation
if (! function_exists('todayProfit')) {
    function todayProfit() {
        $total_sale_for_profit_loss_history = DB::table('product_sale_details')
            ->join('product_sales','product_sale_details.product_sale_id','product_sales.id')
            ->where('product_sale_details.sale_date', date('Y-m-d'))
            ->where('product_sales.sale_type', 'pos_sale')
            ->select(DB::raw('SUM(product_sale_details.sub_total) as sub_total'),DB::raw('SUM(product_sale_details.purchase_price) as purchase_price'))
            ->first();

        $total_discount_sale_for_profit_loss_history = DB::table('product_sales')
            ->where('sale_date', date('Y-m-d'))
            ->where('sale_type', 'pos_sale')
            ->select(DB::raw('SUM(discount_amount) as discount_amount'))
            ->first();

        $after_discount = $total_sale_for_profit_loss_history->sub_total - $total_discount_sale_for_profit_loss_history->discount_amount;
        return $after_discount - $total_sale_for_profit_loss_history->purchase_price;
    }
}

// total sale sum for profit calculation
if (! function_exists('totalProfit')) {
    function totalProfit() {
        $total_sale_for_profit_loss_history = DB::table('product_sale_details')
            ->join('product_sales','product_sale_details.product_sale_id','product_sales.id')
            ->where('product_sales.sale_type', 'pos_sale')
            ->select(DB::raw('SUM(product_sale_details.sub_total) as sub_total'),DB::raw('SUM(product_sale_details.purchase_price) as purchase_price'))
            ->first();

        $total_discount_sale_for_profit_loss_history = DB::table('product_sales')
            ->where('sale_type', 'pos_sale')
            ->select(DB::raw('SUM(discount_amount) as discount_amount'))
            ->first();

        $after_discount = $total_sale_for_profit_loss_history->sub_total - $total_discount_sale_for_profit_loss_history->discount_amount;
        return $after_discount - $total_sale_for_profit_loss_history->purchase_price;
    }
}

// warehouse current stock
if (! function_exists('warehouseCurrentStock')) {
    function warehouseCurrentStock($product_id) {
        $warehouse_current_stock = DB::table('warehouse_current_stocks')
            ->where('product_id',$product_id)
            ->latest('id')
            ->pluck('current_stock')
            ->first();

        if($warehouse_current_stock == NULL){
            $warehouse_current_stock = 0;
        }
        return $warehouse_current_stock;
    }
}

// customer sale total amount
if (! function_exists('customerSaleTotalAmount')) {
    function customerSaleTotalAmount($customer_id,$type) {

        $total_amount = DB::table('transactions')
            ->select(DB::raw('SUM(amount) as sum_total_amount'))
            ->where('party_id',$customer_id)
            //->where('transaction_type',$type)
            ->first();

        return $total_amount->sum_total_amount;
    }
}

// user name as id
if (! function_exists('userName')) {
    function userName($user_id) {

        return DB::table('users')
            ->where('id',$user_id)
            ->pluck('name')
            ->first();
    }
}

// party name as id
if (! function_exists('partyName')) {
    function partyName($party_id) {

        return DB::table('parties')
            ->where('id',$party_id)
            ->pluck('name')
            ->first();
    }
}

// party name as id
if (! function_exists('partyPhone')) {
    function partyPhone($party_id) {

        return DB::table('parties')
            ->where('id',$party_id)
            ->pluck('phone')
            ->first();
    }
}

// party name as id
if (! function_exists('partyEmail')) {
    function partyEmail($party_id) {

        return DB::table('parties')
            ->where('id',$party_id)
            ->pluck('email')
            ->first();
    }
}

// party name as id
if (! function_exists('partyAddress')) {
    function partyAddress($party_id) {

        return DB::table('parties')
            ->where('id',$party_id)
            ->pluck('address')
            ->first();
    }
}

// warehouse name as id
if (! function_exists('warehouseName')) {
    function warehouseName($warehouse_id) {

        return DB::table('warehouses')
            ->where('id',$warehouse_id)
            ->pluck('name')
            ->first();
    }
}

// warehouse name as id
if (! function_exists('storeName')) {
    function storeName($store_id) {

        return DB::table('stores')
            ->where('id',$store_id)
            ->pluck('name')
            ->first();
    }
}

// payment type
if (! function_exists('paymentType')) {
    function paymentType($id) {

        return DB::table('transactions')->where('ref_id',$id)->where('transaction_type','whole_purchase')->pluck('payment_type')->first();
    }
}





