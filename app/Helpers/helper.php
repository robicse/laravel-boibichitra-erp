<?php
//filter products published
use App\LeaveApplication;
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
        $today_purchase = 0;
        $today_purchase_history = DB::table('product_purchases')
            ->where('purchase_date', date('Y-m-d'))
            ->select(DB::raw('SUM(total_amount) as today_purchase'))
            ->first();
        if(!empty($today_purchase_history)){
            $today_purchase = $today_purchase_history->today_purchase;
        }

        return $today_purchase;
    }
}

// total purchase sum
if (! function_exists('totalPurchase')) {
    function totalPurchase() {
        $total_purchase = 0;
        $total_purchase_history = DB::table('product_purchases')
            ->select(DB::raw('SUM(total_amount) as total_purchase'))
            ->first();
        if(!empty($total_purchase_history)){
            $total_purchase = $total_purchase_history->total_purchase;
        }

        return $total_purchase;
    }
}

// today purchase return sum
if (! function_exists('todayPurchaseReturn')) {
    function todayPurchaseReturn() {
        $today_purchase_return = 0;
        $today_purchase_return_history = DB::table('product_purchase_returns')
            ->where('product_purchase_return_date', date('Y-m-d'))
            ->select(DB::raw('SUM(total_amount) as today_purchase_return'))
            ->first();
        if(!empty($today_purchase_return_history)){
            $today_purchase_return = $today_purchase_return_history->today_purchase_return;
        }

        return $today_purchase_return;
    }
}

// total purchase return sum
if (! function_exists('totalPurchaseReturn')) {

    function totalPurchaseReturn() {
        $total_purchase_return = 0;
        $total_purchase_return_history = DB::table('product_purchase_returns')
            ->select(DB::raw('SUM(total_amount) as total_purchase_return'))
            ->first();
        if(!empty($total_purchase_return_history)){
            $total_purchase_return = $total_purchase_return_history->total_purchase_return;
        }
        return $total_purchase_return;
    }
}

// today sale sum
if (! function_exists('todaySale')) {
    function todaySale() {
        $today_sale = 0;
        $today_sale_history = DB::table('product_sales')
            ->where('sale_date', date('Y-m-d'))
            ->select(DB::raw('SUM(total_amount) as today_sale'),DB::raw('SUM(total_vat_amount) as today_sale_vat_amount'))
            ->first();
        if(!empty($today_sale_history)){
            $today_sale = $today_sale_history->today_sale - $today_sale_history->today_sale_vat_amount;
        }
        return $today_sale;
    }
}

// total sale sum
if (! function_exists('totalSale')) {
    function totalSale() {
        $total_sale = 0;
        $total_sale_history = DB::table('product_sales')
            ->select(DB::raw('SUM(total_amount) as total_sale'),DB::raw('SUM(total_vat_amount) as total_sale_vat_amount'))
            ->first();
        if(!empty($total_sale_history)){
            $total_sale = $total_sale_history->total_sale - $total_sale_history->total_sale_vat_amount;
        }
        return $total_sale;
    }
}

// today sale return sum
if (! function_exists('todaySaleReturn')) {
    function todaySaleReturn() {
        $today_sale_return = 0;
        $today_sale_return_history = DB::table('product_sale_returns')
            ->join('product_sales','product_sale_returns.product_sale_invoice_no','product_sales.invoice_no')
            ->where('product_sale_returns.product_sale_return_date', date('Y-m-d'))
            ->where('product_sales.sale_type', 'pos_sale')
            ->select(DB::raw('SUM(product_sale_returns.total_amount) as today_sale_return'))
            ->first();
        if(!empty($today_sale_return_history)){
            $today_sale_return = $today_sale_return_history->today_sale_return;
        }
        return $today_sale_return;
    }
}

// total sale return sum
if (! function_exists('totalSaleReturn')) {
    function totalSaleReturn() {
        $total_sale_return = 0;
        $total_sale_return_history = DB::table('product_sale_returns')
            ->join('product_sales','product_sale_returns.product_sale_invoice_no','product_sales.invoice_no')
            ->where('product_sales.sale_type', 'pos_sale')
            ->select(DB::raw('SUM(product_sale_returns.total_amount) as total_sale_return'))
            ->first();

        if(!empty($total_sale_return_history)){
            $total_sale_return = $total_sale_return_history->total_sale_return;
        }
        return $total_sale_return;
    }
}

// Current User Details
if (! function_exists('VatPercent')) {
    function VatPercent() {
        return \App\ProductVat::pluck('vat_percentage')->first();
    }
}

// today sale sum for profit calculation
if (! function_exists('todayProfit')) {
    function todayProfit() {
        $sum_total_sale = 0;
        $sum_total_purchase = 0;
        $total_sale_for_profit_loss_histories = DB::table('product_sale_details')
            ->join('product_sales','product_sale_details.product_sale_id','product_sales.id')
            ->where('product_sale_details.sale_date', date('Y-m-d'))
            //->where('product_sales.sale_type', 'pos_sale')
            ->select('product_sale_details.product_id','product_sale_details.purchase_price','product_sale_details.qty','product_sale_details.sub_total')
            ->get();
        if(count($total_sale_for_profit_loss_histories) > 0){
            foreach ($total_sale_for_profit_loss_histories as $total_sale_for_profit_loss_history){
                $sum_total_sale += $total_sale_for_profit_loss_history->sub_total;
                $sum_total_purchase += $total_sale_for_profit_loss_history->purchase_price*$total_sale_for_profit_loss_history->qty;
            }
        }
        return $sum_total_sale - $sum_total_purchase;
    }
}

// total sale sum for profit calculation
if (! function_exists('totalProfit')) {
    function totalProfit() {
        $sum_total_sale = 0;
        $sum_total_purchase = 0;
        $total_sale_for_profit_loss_histories = DB::table('product_sale_details')
            ->join('product_sales','product_sale_details.product_sale_id','product_sales.id')
            //->where('product_sales.sale_type', 'pos_sale')
            ->select('product_sale_details.product_id','product_sale_details.purchase_price','product_sale_details.qty','product_sale_details.sub_total')
            ->get();
        if(count($total_sale_for_profit_loss_histories) > 0){
            foreach ($total_sale_for_profit_loss_histories as $total_sale_for_profit_loss_history){
                $sum_total_sale += $total_sale_for_profit_loss_history->sub_total;
                $sum_total_purchase += $total_sale_for_profit_loss_history->purchase_price*$total_sale_for_profit_loss_history->qty;
            }
        }
        return $sum_total_sale - $sum_total_purchase;
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

// warehouse and product current stock
if (! function_exists('warehouseProductCurrentStock')) {
    function warehouseProductCurrentStock($warehouse_id,$product_id) {
        $warehouse_current_stock = DB::table('warehouse_current_stocks')
            ->where('warehouse_id',$warehouse_id)
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

// warehouse and product current stock
if (! function_exists('warehouseStoreProductCurrentStock')) {
    function warehouseStoreProductCurrentStock($warehouse_id,$store_id,$product_id) {
        $warehouse_store_current_stock = DB::table('warehouse_store_current_stocks')
            ->where('warehouse_id',$warehouse_id)
            ->where('store_id',$store_id)
            ->where('product_id',$product_id)
            ->latest('id')
            ->pluck('current_stock')
            ->first();

        if($warehouse_store_current_stock == NULL){
            $warehouse_store_current_stock = 0;
        }
        return $warehouse_store_current_stock;
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

// warehouse name as id
if (! function_exists('storeAddress')) {
    function storeAddress($store_id) {

        return DB::table('stores')
            ->where('id',$store_id)
            ->pluck('address')
            ->first();
    }
}

// warehouse name as id
if (! function_exists('storePhone')) {
    function storePhone($store_id) {

        return DB::table('stores')
            ->where('id',$store_id)
            ->pluck('phone')
            ->first();
    }
}

// payment type
if (! function_exists('paymentType')) {
    function paymentType($id) {

        return DB::table('transactions')->where('ref_id',$id)->where('transaction_type','whole_purchase')->pluck('payment_type')->first();
    }
}

// Current User Details
if (! function_exists('currentUserDetails')) {
    function currentUserDetails($user_id) {
        $user = User::find(Auth::id());
        $user_role = $user->getRoleNames()[0];
        $warehouse_id = $user->warehouse_id;
        $warehouse_name = \App\Warehouse::where('id',$warehouse_id)->pluck('name')->first();
        $store_id = $user->store_id;
        $store_name = \App\Store::where('id',$store_id)->pluck('name')->first();


        $currentUserDetails = [
            'user_id' => $user_id,
            'user_name' => $user->name,
            'role' => $user_role,
            'warehouse_id' => $warehouse_id,
            'warehouse_name' => $warehouse_name,
            //'category_id' => '',
            //'category_name' => '',
            'store_id' => $store_id,
            'store_name' => $store_name,
        ];

        return $currentUserDetails;
    }
}


if (! function_exists('countWeekendThisMonth')) {
    function countWeekendThisMonth($year, $month, $employee_id) {
        $total_weekend = 0;
        $weekend = DB::table('attendances')
            ->select(DB::raw('COUNT(id) as weekend'))
            ->where('year',$year)
            ->where('month',$month)
            ->where('employee_id',$employee_id)
            ->where('status','Weekend')
            ->first();

        if($weekend){
            $total_weekend = $weekend->weekend;
        }

        return $total_weekend;
    }
}

if (! function_exists('countHolidayThisMonth')) {
    function countHolidayThisMonth($year, $month, $employee_id) {
        $total_holiday = 0;
        $holiday = DB::table('attendances')
            ->select(DB::raw('COUNT(id) as holiday'))
            ->where('year',$year)
            ->where('month',$month)
            ->where('employee_id',$employee_id)
            ->where('status','Holiday')
            ->first();

        if($holiday){
            $total_holiday = $holiday->holiday;
        }

        return $total_holiday;
    }
}

if (! function_exists('countPresentThisMonth')) {
    function countPresentThisMonth($year, $month, $employee_id) {
        $total_present = 0;
        $present = DB::table('attendances')
            ->select(DB::raw('COUNT(status) as total_present'))
            ->where('year',$year)
            ->where('month',$month)
            ->where('employee_id',$employee_id)
            ->where('status','Present')
            ->first();

        if($present){
            $total_present = $present->total_present;
        }

        return $total_present;
    }
}

if (! function_exists('countLeaveApprovedThisMonth')) {
    function countLeaveApprovedThisMonth($year, $month, $employee_id) {
        $total_approved_leave = 0;
        $approved_leave = LeaveApplication::where('employee_id', $employee_id)
            ->where('year', $year)
            ->where('month', $month)
            ->where('approval_status', '=','Approved')
            ->select('employee_id','year','month', DB::raw('SUM(duration) as total_duration'))
            ->groupBy('employee_id','year','month')
            ->first();

        if($approved_leave){
            $total_approved_leave = $approved_leave->total_duration;
        }

        return $total_approved_leave;
    }
}

if (! function_exists('countLeavePendingThisMonth')) {
    function countLeavePendingThisMonth($year, $month, $employee_id) {
        $total_pending_leave = 0;
        $pending_leave = LeaveApplication::where('employee_id', $employee_id)
            ->where('year', $year)
            ->where('month', $month)
            ->where('approval_status', '=','Pending')
            ->select('employee_id','year','month', DB::raw('SUM(duration) as total_duration'))
            ->groupBy('employee_id','year','month')
            ->first();

        if($pending_leave){
            $total_pending_leave = $pending_leave->total_duration;
        }

        return $total_pending_leave;
    }
}

if (! function_exists('countLateAbsentThisMonth')) {
    function countLateInfoThisMonth($year, $month, $employee_id) {
        $total_late_absent_info = [
            'total_late_count' => 0,
            'total_late_absent_quotient' => 0,
            'total_late_absent_remainder' => 0
        ];
        $late = DB::table('attendances')
            ->select(DB::raw('COUNT(id) as total_late'))
            ->where('year',$year)
            ->where('month',$month)
            ->where('employee_id',$employee_id)
            ->where('status','Late')
            ->first();

        if($late){
            $total_late = $late->total_late;
            $total_late_absent_info['total_late_count'] = $total_late;
            if($total_late <= 2){
                $total_late_absent_info['total_late_absent_remainder'] = $total_late;
            }elseif($total_late > 2){
                $calculated_late = $total_late/3;
                $total_late_absent_info['total_late_absent_quotient'] = (int) floor($calculated_late);

                $total_late_absent_info['total_late_absent_remainder'] = (int) $total_late % 3;
            }
        }

        return $total_late_absent_info;
    }
}


//if (! function_exists('getExcelAttendanceData')) {
//    function getExcelAttendanceData($datas, $current_date, $employee_card_no)
//    {
//        $employeeInfoData = [
//            'date_match_or_not' => '',
//            'employee_data' => '',
//            'attendance_data' => '',
//        ];
//
//        foreach ($datas as $data){
//            $attendance_date = $data['date'];
//
//            $attendance_year = date('Y', strtotime($attendance_date));
//            $attendance_month = date('m', strtotime($attendance_date));
//            $attendance_day = date('d', strtotime($attendance_date));
//            $attendance_update_date = $attendance_year . '-' . $attendance_month . '-' . $attendance_day;
//            if ($employee_card_no === $data['card_no']) {
//                $employeeInfoData['attendance_data'] = $data;
//            }
//            $employeeInfoData['employee_data'] = DB::table('employees')
//                ->join('employee_office_informations','employees.id','=','employee_office_informations.employee_id')
//                ->where('employee_office_informations.card_no',$employee_card_no)
//                ->select('employees.id','employees.name','employee_office_informations.card_no','employees.warehouse_id','employees.store_id')
//                ->first();
//
//            if ($attendance_update_date === $current_date) {
//                $employeeInfoData['date_match_or_not'] = $current_date;
//                break;
//            }
//        }
//        return $employeeInfoData;
//    }
//
//}

if (! function_exists('getExcelAttendanceData')) {
    function getExcelAttendanceData($datas, $current_date, $employee_card_no)
    {
        $employeeInfoData = [
            'date_match_or_not' => '',
            'attendance_data' => '',
        ];

        foreach ($datas as $data) {
            $attendance_date = $data['date'];

            $attendance_year = date('Y', strtotime($attendance_date));
            $attendance_month = date('m', strtotime($attendance_date));
            $attendance_day = date('d', strtotime($attendance_date));
            $attendance_update_date = $attendance_year . '-' . $attendance_month . '-' . $attendance_day;

            if (($attendance_update_date === $current_date) && ($data['card_no'] === $employee_card_no)) {
                $employeeInfoData['date_match_or_not'] = $data['card_no'];
                $employeeInfoData['attendance_data'] = $data;
            }
        }
        return $employeeInfoData;
    }
}

if (! function_exists('getWeekendThisDate')) {
    function getWeekendThisDate($current_date)
    {
        return DB::table('weekends')->where('date',$current_date)->first();
    }
}

if (! function_exists('getHolidayThisDate')) {
    function getHolidayThisDate($current_date)
    {
        return DB::table('holidays')->where('date',$current_date)->first();
    }
}

if (! function_exists('getLeaveThisDate')) {
    function getLeaveThisDate($current_date)
    {
        return DB::table('leave_applications')
            ->where('start_date','<=',$current_date)
            ->where('end_date','>=',$current_date)
            ->pluck('approval_status')
            ->first();
    }
}





