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
            ->where('product_sale_return_date', date('Y-m-d'))
            ->select(DB::raw('SUM(total_amount) as today_sale_return'))
            ->first();

        return $today_sale_return_history->today_sale_return;
    }
}

// total sale return sum
if (! function_exists('totalSaleReturn')) {
    function totalSaleReturn() {
        $total_sale_return_history = DB::table('product_sale_returns')
            ->select(DB::raw('SUM(total_amount) as total_sale_return'))
            ->first();

        return $total_sale_return_history->total_sale_return;
    }
}

// today sale sum for profit calculation
if (! function_exists('todayProfit')) {
    function todayProfit() {
        $total_sale_for_profit_loss_history = DB::table('product_sale_details')
            ->where('sale_date', date('Y-m-d'))
            ->select(DB::raw('SUM(sub_total) as sub_total'),DB::raw('SUM(vat_amount) as vat_amount'),DB::raw('SUM(purchase_price) as purchase_price'))
            ->first();

        return $total_sale_for_profit_loss_history->sub_total - ($total_sale_for_profit_loss_history->vat_amount + $total_sale_for_profit_loss_history->purchase_price);
    }
}

// total sale sum for profit calculation
if (! function_exists('totalProfit')) {
    function totalProfit() {
        $total_sale_for_profit_loss_history = DB::table('product_sale_details')
            ->select(DB::raw('SUM(sub_total) as sub_total'),DB::raw('SUM(vat_amount) as vat_amount'),DB::raw('SUM(purchase_price) as purchase_price'))
            ->first();

        return $total_sale_for_profit_loss_history->sub_total - ($total_sale_for_profit_loss_history->vat_amount + $total_sale_for_profit_loss_history->purchase_price);
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





