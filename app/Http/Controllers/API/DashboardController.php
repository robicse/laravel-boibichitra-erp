<?php

namespace App\Http\Controllers\API;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public $successStatus = 200;
    public $authStatus = 401;
    public $failStatus = 402;
    public $ExistsStatus = 403;
    public $validationStatus = 404;

    public function todayPurchase(Request $request){
        $today_purchase_sum = todayPurchase() != null ? todayPurchase() : 0;
        return response()->json(['success'=>true,'response' => $today_purchase_sum], $this->successStatus);

    }

    public function totalPurchase(Request $request){
        $total_purchase_sum = totalPurchase() != null ? totalPurchase() : 0;
        return response()->json(['success'=>true,'response' => $total_purchase_sum], $this->successStatus);
    }

    public function todayPurchaseReturn(Request $request){
        $today_purchase_return_sum = todayPurchaseReturn() != null ? todayPurchaseReturn() : 0;
        return response()->json(['success'=>true,'response' => $today_purchase_return_sum], $this->successStatus);
    }

    public function totalPurchaseReturn(Request $request){
        $total_purchase_return_sum = totalPurchaseReturn() != null ? totalPurchaseReturn() : 0;
        return response()->json(['success'=>true,'response' => $total_purchase_return_sum], $this->successStatus);
    }

    public function todaySale(Request $request){
        $today_sale_sum = todaySale() != null ? todaySale() : 0;
        return response()->json(['success'=>true,'response' => $today_sale_sum], $this->successStatus);
    }

    public function totalSale(Request $request){

        $total_sale_sum = totalSale() != null ? totalSale() : 0;
        return response()->json(['success'=>true,'response' => $total_sale_sum], $this->successStatus);
    }

    public function todaySaleReturn(Request $request){
        $today_sale_return_sum = todaySaleReturn() != null ? todaySaleReturn() : 0;
        return response()->json(['success'=>true,'response' => $today_sale_return_sum], $this->successStatus);
    }

    public function totalSaleReturn(Request $request){
        $total_sale_return_sum = totalSaleReturn() != null ? totalSaleReturn() : 0;
        return response()->json(['success'=>true,'response' => $total_sale_return_sum], $this->successStatus);
    }

//    public function todayProfit(){
//        $sum_purchase_price = 0;
//        $sum_sale_price = 0;
//        $sum_purchase_return_price = 0;
//        $sum_sale_return_price = 0;
//        $sum_profit_or_loss_amount = 0;
//
//        $productPurchaseDetails = DB::table('product_purchase_details')
//            ->select('product_id','product_unit_id','product_brand_id', DB::raw('SUM(qty) as qty'), DB::raw('SUM(price) as price'), DB::raw('SUM(mrp_price) as mrp_price'), DB::raw('SUM(sub_total) as sub_total'))
//            ->groupBy('product_id')
//            ->groupBy('product_unit_id')
//            ->groupBy('product_brand_id')
//            ->get();
//
//        if(!empty($productPurchaseDetails)){
//            foreach($productPurchaseDetails as $key => $productPurchaseDetail){
//                $purchase_average_price = $productPurchaseDetail->sub_total/$productPurchaseDetail->qty;
//                $sum_purchase_price += $productPurchaseDetail->sub_total;
//
//
//                // purchase return
//                $productPurchaseReturnDetails = DB::table('product_purchase_return_details')
//                    ->join('product_purchase_returns','product_purchase_return_details.pro_pur_return_id','=','product_purchase_returns.id')
//                    ->select('product_purchase_return_details.product_id','product_purchase_return_details.product_unit_id','product_purchase_return_details.product_brand_id', DB::raw('SUM(qty) as qty'), DB::raw('SUM(price) as price'))
//                    ->where('product_purchase_return_details.product_id',$productPurchaseDetail->product_id)
//                    ->where('product_purchase_return_details.product_unit_id',$productPurchaseDetail->product_unit_id)
//                    ->where('product_purchase_return_details.product_brand_id',$productPurchaseDetail->product_brand_id)
//                    ->where('product_purchase_returns.product_purchase_return_date',date('Y-m-d'))
//                    ->groupBy('product_purchase_return_details.product_id')
//                    ->groupBy('product_purchase_return_details.product_unit_id')
//                    ->groupBy('product_purchase_return_details.product_brand_id')
//                    ->first();
//
//                if(!empty($productPurchaseReturnDetails))
//                {
//                    $purchase_return_total_qty = $productPurchaseReturnDetails->qty;
//                    $purchase_return_total_amount = $productPurchaseReturnDetails->price;
//                    $sum_purchase_return_price += $productPurchaseReturnDetails->price;
//                    $purchase_return_average_price = $purchase_return_total_amount/$productPurchaseReturnDetails->qty;
//
//                    if($purchase_return_total_qty > 0){
//                        $purchase_return_amount = $purchase_return_average_price - ($purchase_average_price*$purchase_return_total_qty);
//                        if($purchase_return_amount > 0){
//                            $sum_profit_or_loss_amount += $purchase_return_amount;
//                        }else{
//                            $sum_profit_or_loss_amount -= $purchase_return_amount;
//                        }
//                    }
//                }
//
//                // sale
//                $productSaleDetails = DB::table('product_sale_details')
//                    ->select('product_id','product_unit_id','product_brand_id', DB::raw('SUM(qty) as qty'), DB::raw('SUM(price) as price'), DB::raw('SUM(sub_total) as sub_total'))
//                    ->where('product_id',$productPurchaseDetail->product_id)
//                    ->where('product_unit_id',$productPurchaseDetail->product_unit_id)
//                    ->where('product_brand_id',$productPurchaseDetail->product_brand_id)
//                    ->where('sale_date',date('Y-m-d'))
//                    ->groupBy('product_id')
//                    ->groupBy('product_unit_id')
//                    ->groupBy('product_brand_id')
//                    ->first();
//
//                if(!empty($productSaleDetails))
//                {
//                    $sale_total_qty = $productSaleDetails->qty;
//                    $sum_sale_price += $productSaleDetails->sub_total;
//                    $sale_average_price = $productSaleDetails->sub_total/ (int) $productSaleDetails->qty;
//
//                    if($sale_total_qty > 0){
//                        $sale_amount = ($sale_average_price*$sale_total_qty) - ($purchase_average_price*$sale_total_qty);
//                        if($sale_amount > 0){
//                            $sum_profit_or_loss_amount += $sale_amount;
//                        }else{
//                            $sum_profit_or_loss_amount -= $sale_amount;
//                        }
//
//                    }
//                }
//
//                // sale return
//                $productSaleReturnDetails = DB::table('product_sale_return_details')
//                    ->join('product_sale_returns','product_sale_return_details.pro_sale_return_id','=','product_sale_returns.id')
//                    ->select('product_sale_return_details.product_id','product_sale_return_details.product_unit_id','product_sale_return_details.product_brand_id', DB::raw('SUM(qty) as qty'), DB::raw('SUM(price) as price'))
//                    ->where('product_sale_return_details.product_id',$productPurchaseDetail->product_id)
//                    ->where('product_sale_return_details.product_unit_id',$productPurchaseDetail->product_unit_id)
//                    ->where('product_sale_return_details.product_brand_id',$productPurchaseDetail->product_brand_id)
//                    ->where('product_sale_returns.product_sale_return_date',date('Y-m-d'))
//                    ->groupBy('product_sale_return_details.product_id')
//                    ->groupBy('product_sale_return_details.product_unit_id')
//                    ->groupBy('product_sale_return_details.product_brand_id')
//                    ->first();
//
//                if(!empty($productSaleReturnDetails))
//                {
//                    $sale_return_total_qty = $productSaleReturnDetails->qty;
//                    $sale_return_total_amount = $productSaleReturnDetails->price;
//                    $sum_sale_return_price += $productSaleReturnDetails->price;
//                    $sale_return_average_price = $sale_return_total_amount/$productSaleReturnDetails->qty;
//
//                    if($sale_return_total_qty > 0){
//                        $sale_return_amount = $sale_return_average_price - ($purchase_average_price*$sale_return_total_qty);
//                        if($sale_return_amount > 0){
//                            $sum_profit_or_loss_amount -= $sale_return_amount;
//                        }else{
//                            $sum_profit_or_loss_amount += $sale_return_amount;
//                        }
//                    }
//                }
//            }
//        }
//
//        // product sales
//        $productSaleDiscounts = DB::table('product_sales')
//            ->select(DB::raw('SUM(total_vat_amount) as sum_total_vat_amount'),DB::raw('SUM(discount_amount) as sum_discount'))
//            ->where('sale_date',date('Y-m-d'))
//            ->first();
//
//        if(!empty($productSaleDiscounts))
//        {
//            // sale vat amount
//            $sum_vat_amount = $productSaleDiscounts->sum_total_vat_amount;
//            if($sum_profit_or_loss_amount > 0){
//                $sum_profit_or_loss_amount -= $sum_vat_amount;
//            }else{
//                $sum_profit_or_loss_amount += $sum_vat_amount;
//            }
//
//            // sale discount
//            $sum_discount = $productSaleDiscounts->sum_discount;
//            if($sum_discount > 0){
//                $sum_profit_or_loss_amount += $sum_discount;
//            }else{
//                $sum_profit_or_loss_amount -= $sum_discount;
//            }
//        }
//
//        if($sum_profit_or_loss_amount)
//        {
//            return response()->json(['success'=>true,'response' => $sum_profit_or_loss_amount], $this->successStatus);
//        }else{
//            return response()->json(['success'=>false,'response'=>null], $this->successStatus);
//        }
//    }

    public function todayProfit(){
        $sum_profit_or_loss_amount = todayProfit() != null ? todayProfit() : 0;
        $today_sale_return_sum = todaySaleReturn() != null ? todaySaleReturn() : 0;
        $final_profit_or_loss_amount = $sum_profit_or_loss_amount - $today_sale_return_sum;
        return response()->json(['success'=>true,'response' => $final_profit_or_loss_amount], $this->successStatus);
    }

//    public function totalProfit(){
//        $sum_purchase_price = 0;
//        $sum_sale_price = 0;
//        $sum_purchase_return_price = 0;
//        $sum_sale_return_price = 0;
//        $sum_profit_or_loss_amount = 0;
//
//        $productPurchaseDetails = DB::table('product_purchase_details')
//            ->select('product_id','product_unit_id','product_brand_id', DB::raw('SUM(qty) as qty'), DB::raw('SUM(price) as price'), DB::raw('SUM(mrp_price) as mrp_price'), DB::raw('SUM(sub_total) as sub_total'))
//            //->where('product_purchases.store_id',$store->id)
//            //->where('product_purchases.ref_id',NULL)
//            //->where('product_purchases.purchase_product_type','Finish Goods')
//            ->groupBy('product_id')
//            ->groupBy('product_unit_id')
//            ->groupBy('product_brand_id')
//            ->get();
//
//        if(!empty($productPurchaseDetails)){
//            foreach($productPurchaseDetails as $key => $productPurchaseDetail){
//                $purchase_average_price = $productPurchaseDetail->sub_total/$productPurchaseDetail->qty;
//                $sum_purchase_price += $productPurchaseDetail->sub_total;
//
//
//                // purchase return
//                $productPurchaseReturnDetails = DB::table('product_purchase_return_details')
//                    ->select('product_id','product_unit_id','product_brand_id', DB::raw('SUM(qty) as qty'), DB::raw('SUM(price) as price'))
//                    ->where('product_id',$productPurchaseDetail->product_id)
//                    ->where('product_unit_id',$productPurchaseDetail->product_unit_id)
//                    ->where('product_brand_id',$productPurchaseDetail->product_brand_id)
//                    ->groupBy('product_id')
//                    ->groupBy('product_unit_id')
//                    ->groupBy('product_brand_id')
//                    ->first();
//
//                if(!empty($productPurchaseReturnDetails))
//                {
//                    $purchase_return_total_qty = $productPurchaseReturnDetails->qty;
//                    $purchase_return_total_amount = $productPurchaseReturnDetails->price;
//                    $sum_purchase_return_price += $productPurchaseReturnDetails->price;
//                    $purchase_return_average_price = $purchase_return_total_amount/$productPurchaseReturnDetails->qty;
//
//                    if($purchase_return_total_qty > 0){
//                        $purchase_return_amount = $purchase_return_average_price - ($purchase_average_price*$purchase_return_total_qty);
//                        if($purchase_return_amount > 0){
//                            $sum_profit_or_loss_amount += $purchase_return_amount;
//                        }else{
//                            $sum_profit_or_loss_amount -= $purchase_return_amount;
//                        }
//                    }
//                }
//
//                // sale
//                $productSaleDetails = DB::table('product_sale_details')
//                    ->select('product_id','product_unit_id','product_brand_id', DB::raw('SUM(qty) as qty'), DB::raw('SUM(price) as price'), DB::raw('SUM(sub_total) as sub_total'))
//                    ->where('product_id',$productPurchaseDetail->product_id)
//                    ->where('product_unit_id',$productPurchaseDetail->product_unit_id)
//                    ->where('product_brand_id',$productPurchaseDetail->product_brand_id)
//                    ->groupBy('product_id')
//                    ->groupBy('product_unit_id')
//                    ->groupBy('product_brand_id')
//                    ->first();
//
//                if(!empty($productSaleDetails))
//                {
//                    $sale_total_qty = $productSaleDetails->qty;
//                    $sum_sale_price += $productSaleDetails->sub_total;
//                    $sale_average_price = $productSaleDetails->sub_total/ (int) $productSaleDetails->qty;
//
//                    if($sale_total_qty > 0){
//                        $sale_amount = ($sale_average_price*$sale_total_qty) - ($purchase_average_price*$sale_total_qty);
//                        if($sale_amount > 0){
//                            $sum_profit_or_loss_amount += $sale_amount;
//                        }else{
//                            $sum_profit_or_loss_amount -= $sale_amount;
//                        }
//
//                    }
//                }
//
//                // sale return
//                $productSaleReturnDetails = DB::table('product_sale_return_details')
//                    ->select('product_id','product_unit_id','product_brand_id', DB::raw('SUM(qty) as qty'), DB::raw('SUM(price) as price'))
//                    ->where('product_id',$productPurchaseDetail->product_id)
//                    ->where('product_unit_id',$productPurchaseDetail->product_unit_id)
//                    ->where('product_brand_id',$productPurchaseDetail->product_brand_id)
//                    ->groupBy('product_id')
//                    ->groupBy('product_unit_id')
//                    ->groupBy('product_brand_id')
//                    ->first();
//
//                if(!empty($productSaleReturnDetails))
//                {
//                    $sale_return_total_qty = $productSaleReturnDetails->qty;
//                    $sale_return_total_amount = $productSaleReturnDetails->price;
//                    $sum_sale_return_price += $productSaleReturnDetails->price;
//                    //$sale_return_average_price = $sale_return_total_amount/$productSaleReturnDetails->qty;
//
//                    if($productSaleReturnDetails->qty != 0)
//                        $sale_return_average_price = $sale_return_total_amount / $productSaleReturnDetails->qty;
//                    else
//                        $sale_return_average_price = 0;
//
//                    if($sale_return_total_qty > 0){
//                        $sale_return_amount = $sale_return_average_price - ($purchase_average_price*$sale_return_total_qty);
//                        if($sale_return_amount > 0){
//                            $sum_profit_or_loss_amount -= $sale_return_amount;
//                        }else{
//                            $sum_profit_or_loss_amount += $sale_return_amount;
//                        }
//                    }
//                }
//            }
//        }
//
//        // product sale
//        $productSaleDiscounts = DB::table('product_sales')
//            ->select(DB::raw('SUM(total_vat_amount) as sum_total_vat_amount'),DB::raw('SUM(discount_amount) as sum_discount'))
//            ->first();
//
//        if(!empty($productSaleDiscounts))
//        {
//            // sale vat amount
//            $sum_vat_amount = $productSaleDiscounts->sum_total_vat_amount;
//            if($sum_profit_or_loss_amount > 0){
//                $sum_profit_or_loss_amount -= $sum_vat_amount;
//            }else{
//                $sum_profit_or_loss_amount += $sum_vat_amount;
//            }
//
//            // sale discount
//            $sum_discount = $productSaleDiscounts->sum_discount;
//            if($sum_discount > 0){
//                $sum_profit_or_loss_amount += $sum_discount;
//            }else{
//                $sum_profit_or_loss_amount -= $sum_discount;
//            }
//        }
//
//        if($sum_profit_or_loss_amount)
//        {
//            return response()->json(['success'=>true,'response' => $sum_profit_or_loss_amount], $this->successStatus);
//        }else{
//            return response()->json(['success'=>false,'response'=>'No Profit Or Loss History Found!'], $this->failStatus);
//        }
//    }

    public function totalProfit(){
        $sum_profit_or_loss_amount = totalProfit() != null ? totalProfit() : 0;
        $total_sale_return_sum = totalSaleReturn() != null ? totalSaleReturn() : 0;
        $final_profit_or_loss_amount = $sum_profit_or_loss_amount - $total_sale_return_sum;
        return response()->json(['success'=>true,'response' => $final_profit_or_loss_amount], $this->successStatus);
    }
}
