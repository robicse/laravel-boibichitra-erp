<?php

namespace App\Http\Controllers\API;

use App\Helpers\APIHelpers;
use App\Http\Controllers\Controller;
use App\ProductSale;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ReportController extends Controller
{
    public $successStatus = 200;
    public $authStatus = 401;
    public $failStatus = 402;
    public $ExistsStatus = 403;
    public $validationStatus = 404;

    // report
//    public function dateWiseSalesReport(Request $request){
//        $validator = Validator::make($request->all(), [
//            'from_date' => 'required',
//            'to_date'=> 'required',
//            'sale_type'=> 'required',
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
//        $from_date = $request->from_date ? $request->from_date : '';
//        $to_date = $request->to_date ? $request->to_date : '';
//        $sale_type = $request->sale_type ? $request->sale_type : '';
//        $warehouse_id = $request->warehouse_id ? $request->warehouse_id : '';
//        $store_id = $request->store_id ? $request->store_id : '';
//
//        if($sale_type != ''){
//            if($sale_type == 'pos_sale'){
//                $sum_profit_or_loss_amount = 0;
//                if($store_id != 0){
//                    $product_sales = ProductSale::where('sale_date','>=',$from_date)
//                        ->where('sale_date','<=',$to_date)
//                        ->where('sale_type',$sale_type)
//                        ->where('store_id',$store_id)
//                        ->get();
//                    $total_sale_history = DB::table('product_sales')
//                        ->where('sale_date','>=',$from_date)
//                        ->where('sale_date','<=',$to_date)
//                        ->where('sale_type',$sale_type)
//                        ->where('store_id',$store_id)
//                        ->select(DB::raw('SUM(total_amount) as total_sale'))
//                        ->first();
//
//
//
//
//                    // loss or profit start
//                    $sum_purchase_price = 0;
//                    $sum_sale_price = 0;
//                    $sum_purchase_return_price = 0;
//                    $sum_sale_return_price = 0;
//
//                    $productPurchaseDetails = DB::table('product_purchase_details')
//                        ->select('product_id','product_unit_id','product_brand_id', DB::raw('SUM(qty) as qty'), DB::raw('SUM(price) as price'), DB::raw('SUM(mrp_price) as mrp_price'), DB::raw('SUM(sub_total) as sub_total'))
//                        ->groupBy('product_id')
//                        ->groupBy('product_unit_id')
//                        ->groupBy('product_brand_id')
//                        ->get();
//
//                    if(!empty($productPurchaseDetails)){
//                        foreach($productPurchaseDetails as $key => $productPurchaseDetail){
//                            $purchase_average_price = $productPurchaseDetail->sub_total/$productPurchaseDetail->qty;
//                            $sum_purchase_price += $productPurchaseDetail->sub_total;
//
//
//                            // purchase return
//                            $productPurchaseReturnDetails = DB::table('product_purchase_return_details')
//                                ->join('product_purchase_returns','product_purchase_return_details.pro_pur_return_id','=','product_purchase_returns.id')
//                                ->select('product_purchase_return_details.product_id','product_purchase_return_details.product_unit_id','product_purchase_return_details.product_brand_id', DB::raw('SUM(qty) as qty'), DB::raw('SUM(price) as price'))
//                                ->where('product_purchase_return_details.product_id',$productPurchaseDetail->product_id)
//                                ->where('product_purchase_return_details.product_unit_id',$productPurchaseDetail->product_unit_id)
//                                ->where('product_purchase_return_details.product_brand_id',$productPurchaseDetail->product_brand_id)
//                                ->where('product_purchase_returns.product_purchase_return_date',date('Y-m-d'))
//                                ->groupBy('product_purchase_return_details.product_id')
//                                ->groupBy('product_purchase_return_details.product_unit_id')
//                                ->groupBy('product_purchase_return_details.product_brand_id')
//                                ->first();
//
//                            if(!empty($productPurchaseReturnDetails))
//                            {
//                                $purchase_return_total_qty = $productPurchaseReturnDetails->qty;
//                                $purchase_return_total_amount = $productPurchaseReturnDetails->price;
//                                $sum_purchase_return_price += $productPurchaseReturnDetails->price;
//                                $purchase_return_average_price = $purchase_return_total_amount/$productPurchaseReturnDetails->qty;
//
//                                if($purchase_return_total_qty > 0){
//                                    $purchase_return_amount = $purchase_return_average_price - ($purchase_average_price*$purchase_return_total_qty);
//                                    if($purchase_return_amount > 0){
//                                        $sum_profit_or_loss_amount += $purchase_return_amount;
//                                    }else{
//                                        $sum_profit_or_loss_amount -= $purchase_return_amount;
//                                    }
//                                }
//                            }
//
//                            // sale
//                            $productSaleDetails = DB::table('product_sale_details')
//                                ->join('product_sales','product_sale_details.product_sale_id','product_sales.id')
//                                ->select(
//                                    'product_sale_details.product_id',
//                                    'product_sale_details.product_unit_id',
//                                    'product_sale_details.product_brand_id',
//                                    DB::raw('SUM(product_sale_details.qty) as qty'),
//                                    DB::raw('SUM(product_sale_details.price) as price'),
//                                    DB::raw('SUM(product_sale_details.sub_total) as sub_total')
//                                )
//                                ->where('product_sale_details.product_id',$productPurchaseDetail->product_id)
//                                ->where('product_sale_details.product_unit_id',$productPurchaseDetail->product_unit_id)
//                                ->where('product_sale_details.product_brand_id',$productPurchaseDetail->product_brand_id)
//                                ->where('product_sales.sale_date','>=',$from_date)
//                                ->where('product_sales.sale_date','<=',$to_date)
//                                ->where('product_sales.sale_type',$sale_type)
//                                ->where('product_sales.store_id',$store_id)
//                                ->groupBy('product_sale_details.product_id')
//                                ->groupBy('product_sale_details.product_unit_id')
//                                ->groupBy('product_sale_details.product_brand_id')
//                                ->first();
//
//                            if(!empty($productSaleDetails))
//                            {
//                                $sale_total_qty = $productSaleDetails->qty;
//                                $sum_sale_price += $productSaleDetails->sub_total;
//                                $sale_average_price = $productSaleDetails->sub_total/ (int) $productSaleDetails->qty;
//
//                                if($sale_total_qty > 0){
//                                    $sale_amount = ($sale_average_price*$sale_total_qty) - ($purchase_average_price*$sale_total_qty);
//                                    if($sale_amount > 0){
//                                        $sum_profit_or_loss_amount += $sale_amount;
//                                    }else{
//                                        $sum_profit_or_loss_amount -= $sale_amount;
//                                    }
//
//                                }
//                            }
//
//                            // sale return
//                            $productSaleReturnDetails = DB::table('product_sale_return_details')
//                                ->join('product_sale_returns','product_sale_return_details.pro_sale_return_id','=','product_sale_returns.id')
//                                ->select('product_sale_return_details.product_id','product_sale_return_details.product_unit_id','product_sale_return_details.product_brand_id', DB::raw('SUM(qty) as qty'), DB::raw('SUM(price) as price'))
//                                ->where('product_sale_return_details.product_id',$productPurchaseDetail->product_id)
//                                ->where('product_sale_return_details.product_unit_id',$productPurchaseDetail->product_unit_id)
//                                ->where('product_sale_return_details.product_brand_id',$productPurchaseDetail->product_brand_id)
//                                ->where('product_sale_returns.product_sale_return_date','>=',$from_date)
//                                ->where('product_sale_returns.product_sale_return_date','<=',$to_date)
//                                //->where('product_sale_returns.sale_type',$sale_type)
//                                ->where('product_sale_returns.store_id',$store_id)
//                                ->groupBy('product_sale_return_details.product_id')
//                                ->groupBy('product_sale_return_details.product_unit_id')
//                                ->groupBy('product_sale_return_details.product_brand_id')
//                                ->first();
//
//                            if(!empty($productSaleReturnDetails))
//                            {
//                                $sale_return_total_qty = $productSaleReturnDetails->qty;
//                                $sale_return_total_amount = $productSaleReturnDetails->price;
//                                $sum_sale_return_price += $productSaleReturnDetails->price;
//                                //$sale_return_average_price = $sale_return_total_amount/$productSaleReturnDetails->qty;
//                                if($productSaleReturnDetails->qty != 0)
//                                    $sale_return_average_price = $sale_return_total_amount / $productSaleReturnDetails->qty;
//                                else
//                                    $sale_return_average_price = 0;
//
//                                if($sale_return_total_qty > 0){
//                                    $sale_return_amount = $sale_return_average_price - ($purchase_average_price*$sale_return_total_qty);
//                                    if($sale_return_amount > 0){
//                                        $sum_profit_or_loss_amount -= $sale_return_amount;
//                                    }else{
//                                        $sum_profit_or_loss_amount += $sale_return_amount;
//                                    }
//                                }
//                            }
//                        }
//                    }
//
//                    // product sales
//                    $productSaleDiscounts = DB::table('product_sales')
//                        ->select(DB::raw('SUM(total_vat_amount) as sum_total_vat_amount'),DB::raw('SUM(discount_amount) as sum_discount'))
//                        ->where('sale_date','>=',$from_date)
//                        ->where('sale_date','<=',$to_date)
//                        ->where('sale_type',$sale_type)
//                        ->where('store_id',$store_id)
//                        ->first();
//
//                    if(!empty($productSaleDiscounts))
//                    {
//                        // sale vat amount
//                        $sum_vat_amount = $productSaleDiscounts->sum_total_vat_amount;
//                        if($sum_profit_or_loss_amount > 0){
//                            $sum_profit_or_loss_amount -= $sum_vat_amount;
//                        }else{
//                            $sum_profit_or_loss_amount += $sum_vat_amount;
//                        }
//
//                        // sale discount
//                        $sum_discount = $productSaleDiscounts->sum_discount;
//                        if($sum_discount > 0){
//                            $sum_profit_or_loss_amount += $sum_discount;
//                        }else{
//                            $sum_profit_or_loss_amount -= $sum_discount;
//                        }
//                    }
//                    // loss or profit end
//
//
//
//
//                }else{
//                    $product_sales = ProductSale::where('sale_date','>=',$from_date)
//                        ->where('sale_date','<=',$to_date)
//                        ->where('sale_type',$sale_type)
//                        ->get();
//                    $total_sale_history = DB::table('product_sales')
//                        ->where('sale_date','>=',$from_date)
//                        ->where('sale_date','<=',$to_date)
//                        ->where('sale_type',$sale_type)
//                        ->select(DB::raw('SUM(total_amount) as total_sale'))
//                        ->first();
//
//
//                    // loss or profit start
//                    $sum_purchase_price = 0;
//                    $sum_sale_price = 0;
//                    $sum_purchase_return_price = 0;
//                    $sum_sale_return_price = 0;
//
//
//                    $productPurchaseDetails = DB::table('product_purchase_details')
//                        ->select('product_id','product_unit_id','product_brand_id', DB::raw('SUM(qty) as qty'), DB::raw('SUM(price) as price'), DB::raw('SUM(mrp_price) as mrp_price'), DB::raw('SUM(sub_total) as sub_total'))
//                        ->groupBy('product_id')
//                        ->groupBy('product_unit_id')
//                        ->groupBy('product_brand_id')
//                        ->get();
//
//                    if(!empty($productPurchaseDetails)){
//                        foreach($productPurchaseDetails as $key => $productPurchaseDetail){
//                            $purchase_average_price = $productPurchaseDetail->sub_total/$productPurchaseDetail->qty;
//                            $sum_purchase_price += $productPurchaseDetail->sub_total;
//
//
//                            // purchase return
//                            $productPurchaseReturnDetails = DB::table('product_purchase_return_details')
//                                ->join('product_purchase_returns','product_purchase_return_details.pro_pur_return_id','=','product_purchase_returns.id')
//                                ->select('product_purchase_return_details.product_id','product_purchase_return_details.product_unit_id','product_purchase_return_details.product_brand_id', DB::raw('SUM(qty) as qty'), DB::raw('SUM(price) as price'))
//                                ->where('product_purchase_return_details.product_id',$productPurchaseDetail->product_id)
//                                ->where('product_purchase_return_details.product_unit_id',$productPurchaseDetail->product_unit_id)
//                                ->where('product_purchase_return_details.product_brand_id',$productPurchaseDetail->product_brand_id)
//                                ->where('product_purchase_returns.product_purchase_return_date',date('Y-m-d'))
//                                ->groupBy('product_purchase_return_details.product_id')
//                                ->groupBy('product_purchase_return_details.product_unit_id')
//                                ->groupBy('product_purchase_return_details.product_brand_id')
//                                ->first();
//
//                            if(!empty($productPurchaseReturnDetails))
//                            {
//                                $purchase_return_total_qty = $productPurchaseReturnDetails->qty;
//                                $purchase_return_total_amount = $productPurchaseReturnDetails->price;
//                                $sum_purchase_return_price += $productPurchaseReturnDetails->price;
//                                $purchase_return_average_price = $purchase_return_total_amount/$productPurchaseReturnDetails->qty;
//
//                                if($purchase_return_total_qty > 0){
//                                    $purchase_return_amount = $purchase_return_average_price - ($purchase_average_price*$purchase_return_total_qty);
//                                    if($purchase_return_amount > 0){
//                                        $sum_profit_or_loss_amount += $purchase_return_amount;
//                                    }else{
//                                        $sum_profit_or_loss_amount -= $purchase_return_amount;
//                                    }
//                                }
//                            }
//
//                            // sale
//                            $productSaleDetails = DB::table('product_sale_details')
//                                ->join('product_sales','product_sale_details.product_sale_id','product_sales.id')
//                                ->select(
//                                    'product_sale_details.product_id',
//                                    'product_sale_details.product_unit_id',
//                                    'product_sale_details.product_brand_id',
//                                    DB::raw('SUM(product_sale_details.qty) as qty'),
//                                    DB::raw('SUM(product_sale_details.price) as price'),
//                                    DB::raw('SUM(product_sale_details.sub_total) as sub_total')
//                                )
//                                ->where('product_sale_details.product_id',$productPurchaseDetail->product_id)
//                                ->where('product_sale_details.product_unit_id',$productPurchaseDetail->product_unit_id)
//                                ->where('product_sale_details.product_brand_id',$productPurchaseDetail->product_brand_id)
//                                ->where('product_sales.sale_date','>=',$from_date)
//                                ->where('product_sales.sale_date','<=',$to_date)
//                                ->where('product_sales.sale_type',$sale_type)
//                                ->where('product_sales.store_id',$store_id)
//                                ->groupBy('product_sale_details.product_id')
//                                ->groupBy('product_sale_details.product_unit_id')
//                                ->groupBy('product_sale_details.product_brand_id')
//                                ->first();
//
//                            if(!empty($productSaleDetails))
//                            {
//                                $sale_total_qty = $productSaleDetails->qty;
//                                $sum_sale_price += $productSaleDetails->sub_total;
//                                $sale_average_price = $productSaleDetails->sub_total/ (int) $productSaleDetails->qty;
//
//                                if($sale_total_qty > 0){
//                                    $sale_amount = ($sale_average_price*$sale_total_qty) - ($purchase_average_price*$sale_total_qty);
//                                    if($sale_amount > 0){
//                                        $sum_profit_or_loss_amount += $sale_amount;
//                                    }else{
//                                        $sum_profit_or_loss_amount -= $sale_amount;
//                                    }
//
//                                }
//                            }
//
//                            // sale return
//                            $productSaleReturnDetails = DB::table('product_sale_return_details')
//                                ->join('product_sale_returns','product_sale_return_details.pro_sale_return_id','=','product_sale_returns.id')
//                                ->select('product_sale_return_details.product_id','product_sale_return_details.product_unit_id','product_sale_return_details.product_brand_id', DB::raw('SUM(qty) as qty'), DB::raw('SUM(price) as price'))
//                                ->where('product_sale_return_details.product_id',$productPurchaseDetail->product_id)
//                                ->where('product_sale_return_details.product_unit_id',$productPurchaseDetail->product_unit_id)
//                                ->where('product_sale_return_details.product_brand_id',$productPurchaseDetail->product_brand_id)
//                                ->where('product_sale_returns.product_sale_return_date','>=',$from_date)
//                                ->where('product_sale_returns.product_sale_return_date','<=',$to_date)
//                                //->where('product_sale_returns.sale_type',$sale_type)
//                                ->groupBy('product_sale_return_details.product_id')
//                                ->groupBy('product_sale_return_details.product_unit_id')
//                                ->groupBy('product_sale_return_details.product_brand_id')
//                                ->first();
//
//                            if(!empty($productSaleReturnDetails))
//                            {
//                                $sale_return_total_qty = $productSaleReturnDetails->qty;
//                                $sale_return_total_amount = $productSaleReturnDetails->price;
//                                $sum_sale_return_price += $productSaleReturnDetails->price;
//                                $sale_return_average_price = $sale_return_total_amount/$productSaleReturnDetails->qty;
//
//                                if($sale_return_total_qty > 0){
//                                    $sale_return_amount = $sale_return_average_price - ($purchase_average_price*$sale_return_total_qty);
//                                    if($sale_return_amount > 0){
//                                        $sum_profit_or_loss_amount -= $sale_return_amount;
//                                    }else{
//                                        $sum_profit_or_loss_amount += $sale_return_amount;
//                                    }
//                                }
//                            }
//                        }
//                    }
//
//                    // product sales
//                    $productSaleDiscounts = DB::table('product_sales')
//                        ->select(DB::raw('SUM(total_vat_amount) as sum_total_vat_amount'),DB::raw('SUM(discount_amount) as sum_discount'))
//                        ->where('sale_date','>=',$from_date)
//                        ->where('sale_date','<=',$to_date)
//                        ->where('sale_type',$sale_type)
//                        ->first();
//
//                    if(!empty($productSaleDiscounts))
//                    {
//                        // sale vat amount
//                        $sum_vat_amount = $productSaleDiscounts->sum_total_vat_amount;
//                        if($sum_profit_or_loss_amount > 0){
//                            $sum_profit_or_loss_amount -= $sum_vat_amount;
//                        }else{
//                            $sum_profit_or_loss_amount += $sum_vat_amount;
//                        }
//
//                        // sale discount
//                        $sum_discount = $productSaleDiscounts->sum_discount;
//                        if($sum_discount > 0){
//                            $sum_profit_or_loss_amount += $sum_discount;
//                        }else{
//                            $sum_profit_or_loss_amount -= $sum_discount;
//                        }
//                    }
//                    // loss or profit end
//
//
//                }
//
//            }elseif($sale_type == 'whole_sale'){
//                if($warehouse_id != 0){
//                    $product_sales = ProductSale::where('sale_date','>=',$from_date)
//                        ->where('sale_date','<=',$to_date)
//                        ->where('sale_type',$sale_type)
//                        ->where('warehouse_id',$warehouse_id)
//                        ->get();
//                    $total_sale_history = DB::table('product_sales')
//                        ->where('sale_date','>=',$from_date)
//                        ->where('sale_date','<=',$to_date)
//                        ->where('sale_type',$sale_type)
//                        ->where('warehouse_id',$warehouse_id)
//                        ->select(DB::raw('SUM(total_amount) as total_sale'))
//                        ->first();
//                }else{
//                    $product_sales = ProductSale::where('sale_date','>=',$from_date)
//                        ->where('sale_date','<=',$to_date)
//                        ->where('sale_type',$sale_type)
//                        ->get();
//                    $total_sale_history = DB::table('product_sales')
//                        ->where('sale_date','>=',$from_date)
//                        ->where('sale_date','<=',$to_date)
//                        ->where('sale_type',$sale_type)
//                        ->select(DB::raw('SUM(total_amount) as total_sale'))
//                        ->first();
//                }
//
//            }else{
//                $product_sales = ProductSale::where('sale_date','>=',$from_date)
//                    ->where('sale_date','<=',$to_date)
//                    ->where('sale_type',$sale_type)
//                    ->get();
//                $total_sale_history = DB::table('product_sales')
//                    ->where('sale_date','>=',$from_date)
//                    ->where('sale_date','<=',$to_date)
//                    ->where('sale_type',$sale_type)
//                    ->select(DB::raw('SUM(total_amount) as total_sale'))
//                    ->first();
//            }
//
//            $grand_total_amount = $total_sale_history->total_sale;
//        }else{
//            $product_sales = ProductSale::where('sale_date','>=',$from_date)
//                ->where('sale_date','<=',$to_date)
//                ->get();
//
//            $total_sale_history = DB::table('product_sales')
//                ->where('sale_date','>=',$from_date)
//                ->where('sale_date','<=',$to_date)
//                ->select(DB::raw('SUM(total_amount) as total_sale'))
//                ->first();
//            $grand_total_amount = $total_sale_history->total_sale;
//        }
//
//        $store_info = DB::table('stores')
//            ->where('id',$request->store_id)
//            ->select('name','phone','email','address')
//            ->first();
//
//        if($product_sales)
//        {
//            return response()->json(['success'=>true,'response' => $product_sales,'grand_total_amount'=>$grand_total_amount,'profit_amount'=>$sum_profit_or_loss_amount,'store_info'=>$store_info], $this->successStatus);
//        }else{
//            return response()->json(['success'=>false,'response'=>null], $this->successStatus);
//        }
//    }

    public function dateWiseSalesReport(Request $request){
        try {
            // required and unique
            $validator = Validator::make($request->all(), [
                'search_type'=> 'required',
                'sale_type'=> 'required',
            ]);

            if ($validator->fails()) {
                $response = APIHelpers::createAPIResponse(true,400,$validator->errors(),null);
                return response()->json($response,400);
            }

            $from_date = $request->from_date ? $request->from_date : '';
            $to_date = $request->to_date ? $request->to_date : '';
            $sale_type = $request->sale_type ? $request->sale_type : '';
            $warehouse_id = $request->warehouse_id ? $request->warehouse_id : '';
            $store_id = $request->store_id ? $request->store_id : '';


            $sale_infos = [
                'date_wise' => '',
                'month_wise' => '',
                'year_wise' => '',
            ];

            $grand_total_amount = 0;
            $grand_total_vat_amount = 0;

            if($sale_type == 'pos_sale'){
                if($request->search_type == 'date'){
                    if($store_id != ''){
                        $product_sales = DB::table('product_sales')
                            ->select(DB::raw('sum(total_amount) as `sum_total_amount`'),DB::raw('sum(total_vat_amount) as `sum_total_vat_amount`'),DB::raw('DATE(created_at) date'))
                            ->where('sale_date','>=',$from_date)
                            ->where('sale_date','<=',$to_date)
                            ->where('sale_type',$sale_type)
                            ->where('store_id',$store_id)
                            ->groupBy(DB::raw('DATE(created_at)'))
                            ->get();
                    }else{
                        $product_sales = DB::table('product_sales')
                            ->select(DB::raw('sum(total_amount) as `sum_total_amount`'),DB::raw('sum(total_vat_amount) as `sum_total_vat_amount`'),DB::raw('DATE(created_at) date'))
                            ->where('sale_date','>=',$from_date)
                            ->where('sale_date','<=',$to_date)
                            ->where('sale_type',$sale_type)
                            ->groupBy(DB::raw('DATE(created_at)'))
                            ->get();
                    }

                    if(count($product_sales) > 0){
                        foreach ($product_sales as $product_sale){
                            $grand_total_amount += $product_sale->sum_total_amount;
                            $grand_total_vat_amount += $product_sale->sum_total_vat_amount;
                        }
                    }

                    $sale_infos['date_wise'] = [
                        'product_sales' => $product_sales,
                        'grand_total_amount' => $grand_total_amount,
                        'grand_total_vat_amount' => $grand_total_vat_amount,
                    ];
                }

                if($request->search_type == 'month'){
                    $from_year = $request->from_year;
                    $to_year = $request->to_year;
                    $from_month = $request->from_month;
                    $to_month = $request->to_month;

                    $from = $from_year.'-'.$from_month.'-01';
                    $to = $to_year.'-'.$to_month.'-31';

                    if($store_id != ''){
                        $product_sales = DB::table('product_sales')
                            ->select(DB::raw('sum(total_amount) as `sum_total_amount`'),DB::raw('sum(total_vat_amount) as `sum_total_vat_amount`'),DB::raw('YEAR(created_at) year'),DB::raw('MONTH(created_at) month'))
                            ->where('sale_date','>=',$from)
                            ->where('sale_date','<=',$to)
                            ->where('sale_type',$sale_type)
                            ->where('store_id',$store_id)
                            ->groupBy(DB::raw('YEAR(created_at)'), DB::raw('MONTH(created_at)'))
                            ->get();
                    }else{
                        $product_sales = DB::table('product_sales')
                            ->select(DB::raw('sum(total_amount) as `sum_total_amount`'),DB::raw('sum(total_vat_amount) as `sum_total_vat_amount`'),DB::raw('YEAR(created_at) year'),DB::raw('MONTH(created_at) month'))
                            ->where('sale_date','>=',$from)
                            ->where('sale_date','<=',$to)
                            ->where('sale_type',$sale_type)
                            ->groupBy(DB::raw('YEAR(created_at)'), DB::raw('MONTH(created_at)'))
                            ->get();
                    }

                    if(count($product_sales) > 0){
                        foreach ($product_sales as $product_sale){
                            $grand_total_amount += $product_sale->sum_total_amount;
                            $grand_total_vat_amount += $product_sale->sum_total_vat_amount;
                        }
                    }

                    $sale_infos['month_wise'] = [
                        'product_sales' => $product_sales,
                        'grand_total_amount' => $grand_total_amount,
                        'grand_total_vat_amount' => $grand_total_vat_amount,
                    ];
                }

                if($request->search_type == 'year'){
                    $from_year = $request->from_year;
                    $to_year = $request->to_year;

                    $from = $from_year.'-01-01';
                    $to = $to_year.'-12-31';


                    // no delete (first requirement)

    //                if($store_id != ''){
    //                    $product_sales = DB::table('product_sales')
    //                        ->select(DB::raw('sum(total_amount) as `sum_total_amount`'),DB::raw('sum(total_vat_amount) as `sum_total_vat_amount`'),DB::raw('YEAR(created_at) year'))
    //                        ->where('sale_date','>=',$from)
    //                        ->where('sale_date','<=',$to)
    //                        ->where('sale_type',$sale_type)
    //                        ->where('store_id',$store_id)
    //                        ->groupBy(DB::raw('YEAR(created_at)'))
    //                        ->get();
    //                }else{
    //                    $product_sales = DB::table('product_sales')
    //                        ->select(DB::raw('sum(total_amount) as `sum_total_amount`'),DB::raw('sum(total_vat_amount) as `sum_total_vat_amount`'),DB::raw('YEAR(created_at) year'))
    //                        ->where('sale_date','>=',$from)
    //                        ->where('sale_date','<=',$to)
    //                        ->where('sale_type',$sale_type)
    //                        ->groupBy(DB::raw('YEAR(created_at)'))
    //                        ->get();
    //                }
    //
    //                $grand_total_amount = 0;
    //                $grand_total_vat_amount = 0;
    //                if(count($product_sales) > 0){
    //                    foreach ($product_sales as $product_sale){
    //                        $grand_total_amount += $product_sale->sum_total_amount;
    //                        $grand_total_vat_amount += $product_sale->sum_total_vat_amount;
    //                    }
    //                }




                    // no delete (second requirement)
                    $years = [];
                    for ($nYear = $from_year; $nYear <= $to_year; $nYear++) {
                        //echo $nYear . "\n";
                        if($store_id != ''){
                            $product_sales = DB::table('product_sales')
                                ->select(DB::raw('sum(total_amount) as `sum_total_amount`'),DB::raw('sum(total_vat_amount) as `sum_total_vat_amount`'),DB::raw('YEAR(created_at) year'))
                                ->where('sale_date','>=',$from)
                                ->where('sale_date','<=',$to)
                                ->where('sale_type',$sale_type)
                                ->where('store_id',$store_id)
                                ->whereYear('created_at',$nYear)
                                ->groupBy(DB::raw('YEAR(created_at)'))
                                ->get();
                        }else{
                            $product_sales = DB::table('product_sales')
                                ->select(DB::raw('sum(total_amount) as `sum_total_amount`'),DB::raw('sum(total_vat_amount) as `sum_total_vat_amount`'),DB::raw('YEAR(created_at) year'))
                                ->where('sale_date','>=',$from)
                                ->where('sale_date','<=',$to)
                                ->where('sale_type',$sale_type)
                                ->whereYear('created_at',$nYear)
                                ->groupBy(DB::raw('YEAR(created_at)'))
                                ->get();
                        }
                        $grand_total_amount = 0;
                        $grand_total_vat_amount = 0;
                        if(count($product_sales) > 0){
                            foreach ($product_sales as $product_sale){
                                $grand_total_amount += $product_sale->sum_total_amount;
                                $grand_total_vat_amount += $product_sale->sum_total_vat_amount;
                            }
                        }

                        $years[] = [
                            'year'=>$nYear,
                            'product_sales'=>$product_sales
                        ];
                    }




                    $sale_infos['year_wise'] = [
                        //'product_sales' => $product_sales,
                        'grand_total_amount' => $grand_total_amount,
                        'grand_total_vat_amount' => $grand_total_vat_amount,
                        'years' => $years,
                    ];
                }

            }elseif($sale_type == 'whole_sale'){
                if($request->search_type == 'date'){

                    $product_sales = DB::table('product_sales')
                        ->select(DB::raw('sum(total_amount) as `sum_total_amount`'),DB::raw('sum(total_vat_amount) as `sum_total_vat_amount`'),DB::raw('DATE(created_at) date'))
                        ->where('sale_date','>=',$from_date)
                        ->where('sale_date','<=',$to_date)
                        ->where('sale_type',$sale_type)
                        ->groupBy(DB::raw('DATE(created_at)'))
                        ->get();

                    if(count($product_sales) > 0){
                        foreach ($product_sales as $product_sale){
                            $grand_total_amount += $product_sale->sum_total_amount;
                            $grand_total_vat_amount += $product_sale->sum_total_vat_amount;
                        }
                    }

                    $sale_infos['date_wise'] = [
                        'product_sales' => $product_sales,
                        'grand_total_amount' => $grand_total_amount,
                        'grand_total_vat_amount' => $grand_total_vat_amount,
                    ];
                }

                if($request->search_type == 'month'){
                    $from_year = $request->from_year;
                    $to_year = $request->to_year;
                    $from_month = $request->from_month;
                    $to_month = $request->to_month;

                    $from = $from_year.'-'.$from_month.'-01';
                    $to = $to_year.'-'.$to_month.'-31';

                    $product_sales = DB::table('product_sales')
                        ->select(DB::raw('sum(total_amount) as `sum_total_amount`'),DB::raw('sum(total_vat_amount) as `sum_total_vat_amount`'),DB::raw('YEAR(created_at) year'),DB::raw('MONTH(created_at) month'))
                        ->where('sale_date','>=',$from)
                        ->where('sale_date','<=',$to)
                        ->where('sale_type',$sale_type)
                        ->groupBy(DB::raw('YEAR(created_at)'), DB::raw('MONTH(created_at)'))
                        ->get();

                    if(count($product_sales) > 0){
                        foreach ($product_sales as $product_sale){
                            $grand_total_amount += $product_sale->sum_total_amount;
                            $grand_total_vat_amount += $product_sale->sum_total_vat_amount;
                        }
                    }

                    $sale_infos['month_wise'] = [
                        'product_sales' => $product_sales,
                        'grand_total_amount' => $grand_total_amount,
                        'grand_total_vat_amount' => $grand_total_vat_amount,
                    ];
                }

                if($request->search_type == 'year'){
                    $from_year = $request->from_year;
                    $to_year = $request->to_year;

                    $from = $from_year.'-01-01';
                    $to = $to_year.'-12-31';




                    // no delete (first requirement)

    //                $product_sales = DB::table('product_sales')
    //                    ->select(DB::raw('sum(total_amount) as `sum_total_amount`'),DB::raw('sum(total_vat_amount) as `sum_total_vat_amount`'),DB::raw('YEAR(created_at) year'))
    //                    ->where('sale_date','>=',$from)
    //                    ->where('sale_date','<=',$to)
    //                    ->where('sale_type',$sale_type)
    //                    ->groupBy(DB::raw('YEAR(created_at)'))
    //                    ->get();
    //
    //                $grand_total_amount = 0;
    //                $grand_total_vat_amount = 0;
    //                if(count($product_sales) > 0){
    //                    foreach ($product_sales as $product_sale){
    //                        $grand_total_amount += $product_sale->sum_total_amount;
    //                        $grand_total_vat_amount += $product_sale->sum_total_vat_amount;
    //                    }
    //                }


                    // no delete (second requirement)
                    $years = [];
                    for ($nYear = $from_year; $nYear <= $to_year; $nYear++) {
                        //echo $nYear . "\n";
                        $product_sales = DB::table('product_sales')
                            ->select(DB::raw('sum(total_amount) as `sum_total_amount`'),DB::raw('sum(total_vat_amount) as `sum_total_vat_amount`'),DB::raw('YEAR(created_at) year'))
                            ->where('sale_date','>=',$from)
                            ->where('sale_date','<=',$to)
                            ->where('sale_type',$sale_type)
                            ->whereYear('created_at',$nYear)
                            ->groupBy(DB::raw('YEAR(created_at)'))
                            ->get();
                        $grand_total_amount = 0;
                        $grand_total_vat_amount = 0;
                        if(count($product_sales) > 0){
                            foreach ($product_sales as $product_sale){
                                $grand_total_amount += $product_sale->sum_total_amount;
                                $grand_total_vat_amount += $product_sale->sum_total_vat_amount;
                            }
                        }

                        $years[] = [
                            'year'=>$nYear,
                            'product_sales'=>$product_sales
                        ];
                    }


                    $sale_infos['year_wise'] = [
                        //'product_sales' => $product_sales,
                        'grand_total_amount' => $grand_total_amount,
                        'grand_total_vat_amount' => $grand_total_vat_amount,
                        'years' => $years,
                    ];
                }
            }

            $store_info = '';
            if($request->store_id){
                $store_info = DB::table('stores')
                    ->where('id',$request->store_id)
                    ->select('name','phone','email','address')
                    ->first();
            }

            //return response()->json(['success'=>true,'response' => $sale_infos,'store_info'=>$store_info], $this->successStatus);

            $success['sale_infos'] = $sale_infos;
            $success['store_info'] = $store_info;
            $response = APIHelpers::createAPIResponse(false,200,'',$success);
            return response()->json($response,200);


        } catch (\Exception $e) {
            //return $e->getMessage();
            $response = APIHelpers::createAPIResponse(false,500,'Internal Server Error.',null);
            return response()->json($response,500);
        }

    }

//    public function dateWiseVatsReport(Request $request){
//
//        $vats = DB::table('product_sale_details')
//            ->join('product_sales','product_sale_details.product_sale_id','product_sales.id')
//            ->select('product_sales.invoice_no',DB::raw('SUM(product_sale_details.price) as total_price'),DB::raw('SUM(product_sale_details.vat_amount) as total_vat_amount'))
//            ->where('product_sale_details.vat_amount', '>', 0)
//            ->where('product_sales.sale_date','>=',$request->from_date)
//            ->where('product_sales.sale_date','<=',$request->to_date)
//            ->groupBy('product_sales.invoice_no')
//            ->get();
//
//        $sum_vats_amount = DB::table('product_sale_details')
//            ->join('product_sales','product_sale_details.product_sale_id','product_sales.id')
//            ->select(DB::raw('SUM(product_sale_details.price) as total_price'),DB::raw('SUM(product_sale_details.vat_amount) as total_vat_amount'))
//            ->where('product_sale_details.vat_amount', '>', 0)
//            ->where('product_sales.sale_date','>=',$request->from_date)
//            ->where('product_sales.sale_date','<=',$request->to_date)
//            ->first();
//
//        if($vats)
//        {
//            return response()->json(['success'=>true,'response' => $vats,'sum_vats_amount'=>$sum_vats_amount], $this->successStatus);
//        }else{
//            return response()->json(['success'=>false,'response'=>null], $this->successStatus);
//        }
//    }

    public function dateWiseVatsReport(Request $request){
        $validator = Validator::make($request->all(), [
            'search_type'=> 'required',
            'sale_type'=> 'required',
        ]);

        if ($validator->fails()) {
            $response = [
                'success' => false,
                'data' => 'Validation Error.',
                'message' => $validator->errors()
            ];

            return response()->json($response, $this-> validationStatus);
        }

        $from_date = $request->from_date ? $request->from_date : '';
        $to_date = $request->to_date ? $request->to_date : '';
        $sale_type = $request->sale_type ? $request->sale_type : '';
        $warehouse_id = $request->warehouse_id ? $request->warehouse_id : '';
        $store_id = $request->store_id ? $request->store_id : '';


        $sale_infos = [
            'date_wise' => '',
            'month_wise' => '',
            'year_wise' => '',
        ];

        $grand_total_amount = 0;
        $grand_total_vat_amount = 0;

        if($sale_type == 'pos_sale'){
            if($request->search_type == 'date'){
                if($store_id != ''){
                    $product_sales = DB::table('product_sales')
                        ->select(DB::raw('sum(total_amount) as `sum_total_amount`'),DB::raw('sum(total_vat_amount) as `sum_total_vat_amount`'),DB::raw('DATE(created_at) date'))
                        ->where('sale_date','>=',$from_date)
                        ->where('sale_date','<=',$to_date)
                        ->where('sale_type',$sale_type)
                        ->where('store_id',$store_id)
                        ->groupBy(DB::raw('DATE(created_at)'))
                        ->get();
                }else{
                    $product_sales = DB::table('product_sales')
                        ->select(DB::raw('sum(total_amount) as `sum_total_amount`'),DB::raw('sum(total_vat_amount) as `sum_total_vat_amount`'),DB::raw('DATE(created_at) date'))
                        ->where('sale_date','>=',$from_date)
                        ->where('sale_date','<=',$to_date)
                        ->where('sale_type',$sale_type)
                        ->groupBy(DB::raw('DATE(created_at)'))
                        ->get();
                }

                if(count($product_sales) > 0){
                    foreach ($product_sales as $product_sale){
                        $grand_total_amount += $product_sale->sum_total_amount;
                        $grand_total_vat_amount += $product_sale->sum_total_vat_amount;
                    }
                }

                $sale_infos['date_wise'] = [
                    'product_sales' => $product_sales,
                    'grand_total_amount' => $grand_total_amount,
                    'grand_total_vat_amount' => $grand_total_vat_amount,
                ];
            }

            if($request->search_type == 'month'){
                $from_year = $request->from_year;
                $to_year = $request->to_year;
                $from_month = $request->from_month;
                $to_month = $request->to_month;

                $from = $from_year.'-'.$from_month.'-01';
                $to = $to_year.'-'.$to_month.'-31';

                if($store_id != ''){
                    $product_sales = DB::table('product_sales')
                        ->select(DB::raw('sum(total_amount) as `sum_total_amount`'),DB::raw('sum(total_vat_amount) as `sum_total_vat_amount`'),DB::raw('YEAR(created_at) year'),DB::raw('MONTH(created_at) month'))
                        ->where('sale_date','>=',$from)
                        ->where('sale_date','<=',$to)
                        ->where('sale_type',$sale_type)
                        ->where('store_id',$store_id)
                        ->groupBy(DB::raw('YEAR(created_at)'), DB::raw('MONTH(created_at)'))
                        ->get();
                }else{
                    $product_sales = DB::table('product_sales')
                        ->select(DB::raw('sum(total_amount) as `sum_total_amount`'),DB::raw('sum(total_vat_amount) as `sum_total_vat_amount`'),DB::raw('YEAR(created_at) year'),DB::raw('MONTH(created_at) month'))
                        ->where('sale_date','>=',$from)
                        ->where('sale_date','<=',$to)
                        ->where('sale_type',$sale_type)
                        ->groupBy(DB::raw('YEAR(created_at)'), DB::raw('MONTH(created_at)'))
                        ->get();
                }

                if(count($product_sales) > 0){
                    foreach ($product_sales as $product_sale){
                        $grand_total_amount += $product_sale->sum_total_amount;
                        $grand_total_vat_amount += $product_sale->sum_total_vat_amount;
                    }
                }

                $sale_infos['month_wise'] = [
                    'product_sales' => $product_sales,
                    'grand_total_amount' => $grand_total_amount,
                    'grand_total_vat_amount' => $grand_total_vat_amount,
                ];
            }

            if($request->search_type == 'year'){
                $from_year = $request->from_year;
                $to_year = $request->to_year;

                $from = $from_year.'-01-01';
                $to = $to_year.'-12-31';

                // no delete (first requirement)

//                if($store_id != ''){
//                    $product_sales = DB::table('product_sales')
//                        ->select(DB::raw('sum(total_amount) as `sum_total_amount`'),DB::raw('sum(total_vat_amount) as `sum_total_vat_amount`'),DB::raw('YEAR(created_at) year'))
//                        ->where('sale_date','>=',$from)
//                        ->where('sale_date','<=',$to)
//                        ->where('sale_type',$sale_type)
//                        ->where('store_id',$store_id)
//                        ->groupBy(DB::raw('YEAR(created_at)'))
//                        ->get();
//                }else{
//                    $product_sales = DB::table('product_sales')
//                        ->select(DB::raw('sum(total_amount) as `sum_total_amount`'),DB::raw('sum(total_vat_amount) as `sum_total_vat_amount`'),DB::raw('YEAR(created_at) year'))
//                        ->where('sale_date','>=',$from)
//                        ->where('sale_date','<=',$to)
//                        ->where('sale_type',$sale_type)
//                        ->groupBy(DB::raw('YEAR(created_at)'))
//                        ->get();
//                }
//
//                $grand_total_amount = 0;
//                $grand_total_vat_amount = 0;
//                if(count($product_sales) > 0){
//                    foreach ($product_sales as $product_sale){
//                        $grand_total_amount += $product_sale->sum_total_amount;
//                        $grand_total_vat_amount += $product_sale->sum_total_vat_amount;
//                    }
//                }


                // no delete (second requirement)
                $years = [];
                for ($nYear = $from_year; $nYear <= $to_year; $nYear++) {
                    //echo $nYear . "\n";
                    $product_sales = DB::table('product_sales')
                        ->select(DB::raw('sum(total_amount) as `sum_total_amount`'),DB::raw('sum(total_vat_amount) as `sum_total_vat_amount`'),DB::raw('YEAR(created_at) year'))
                        ->where('sale_date','>=',$from)
                        ->where('sale_date','<=',$to)
                        ->where('sale_type',$sale_type)
                        ->whereYear('created_at',$nYear)
                        ->groupBy(DB::raw('YEAR(created_at)'))
                        ->get();
                    $grand_total_amount = 0;
                    $grand_total_vat_amount = 0;
                    if(count($product_sales) > 0){
                        foreach ($product_sales as $product_sale){
                            $grand_total_amount += $product_sale->sum_total_amount;
                            $grand_total_vat_amount += $product_sale->sum_total_vat_amount;
                        }
                    }

                    $years[] = [
                        'year'=>$nYear,
                        'product_sales'=>$product_sales
                    ];
                }

                $sale_infos['year_wise'] = [
                    //'product_sales' => $product_sales,
                    'grand_total_amount' => $grand_total_amount,
                    'grand_total_vat_amount' => $grand_total_vat_amount,
                    'years' => $years,
                ];
            }

        }elseif($sale_type == 'whole_sale'){
            if($request->search_type == 'date'){

                $product_sales = DB::table('product_sales')
                    ->select(DB::raw('sum(total_amount) as `sum_total_amount`'),DB::raw('sum(total_vat_amount) as `sum_total_vat_amount`'),DB::raw('DATE(created_at) date'))
                    ->where('sale_date','>=',$from_date)
                    ->where('sale_date','<=',$to_date)
                    ->where('sale_type',$sale_type)
                    ->groupBy(DB::raw('DATE(created_at)'))
                    ->get();

                if(count($product_sales) > 0){
                    foreach ($product_sales as $product_sale){
                        $grand_total_amount += $product_sale->sum_total_amount;
                        $grand_total_vat_amount += $product_sale->sum_total_vat_amount;
                    }
                }

                $sale_infos['date_wise'] = [
                    'product_sales' => $product_sales,
                    'grand_total_amount' => $grand_total_amount,
                    'grand_total_vat_amount' => $grand_total_vat_amount,
                ];
            }

            if($request->search_type == 'month'){
                $from_year = $request->from_year;
                $to_year = $request->to_year;
                $from_month = $request->from_month;
                $to_month = $request->to_month;

                $from = $from_year.'-'.$from_month.'-01';
                $to = $to_year.'-'.$to_month.'-31';

                $product_sales = DB::table('product_sales')
                    ->select(DB::raw('sum(total_amount) as `sum_total_amount`'),DB::raw('sum(total_vat_amount) as `sum_total_vat_amount`'),DB::raw('YEAR(created_at) year'),DB::raw('MONTH(created_at) month'))
                    ->where('sale_date','>=',$from)
                    ->where('sale_date','<=',$to)
                    ->where('sale_type',$sale_type)
                    ->groupBy(DB::raw('YEAR(created_at)'), DB::raw('MONTH(created_at)'))
                    ->get();

                if(count($product_sales) > 0){
                    foreach ($product_sales as $product_sale){
                        $grand_total_amount += $product_sale->sum_total_amount;
                        $grand_total_vat_amount += $product_sale->sum_total_vat_amount;
                    }
                }

                $sale_infos['month_wise'] = [
                    'product_sales' => $product_sales,
                    'grand_total_amount' => $grand_total_amount,
                    'grand_total_vat_amount' => $grand_total_vat_amount,
                ];
            }

            if($request->search_type == 'year'){
                $from_year = $request->from_year;
                $to_year = $request->to_year;

                $from = $from_year.'-01-01';
                $to = $to_year.'-12-31';

                // no delete (first requirement)

//                $product_sales = DB::table('product_sales')
//                    ->select(DB::raw('sum(total_amount) as `sum_total_amount`'),DB::raw('sum(total_vat_amount) as `sum_total_vat_amount`'),DB::raw('YEAR(created_at) year'))
//                    ->where('sale_date','>=',$from)
//                    ->where('sale_date','<=',$to)
//                    ->where('sale_type',$sale_type)
//                    ->groupBy(DB::raw('YEAR(created_at)'))
//                    ->get();
//
//                $grand_total_amount = 0;
//                $grand_total_vat_amount = 0;
//                if(count($product_sales) > 0){
//                    foreach ($product_sales as $product_sale){
//                        $grand_total_amount += $product_sale->sum_total_amount;
//                        $grand_total_vat_amount += $product_sale->sum_total_vat_amount;
//                    }
//                }

                // no delete (second requirement)
                $years = [];
                for ($nYear = $from_year; $nYear <= $to_year; $nYear++) {
                    //echo $nYear . "\n";
                    $product_sales = DB::table('product_sales')
                        ->select(DB::raw('sum(total_amount) as `sum_total_amount`'),DB::raw('sum(total_vat_amount) as `sum_total_vat_amount`'),DB::raw('YEAR(created_at) year'))
                        ->where('sale_date','>=',$from)
                        ->where('sale_date','<=',$to)
                        ->where('sale_type',$sale_type)
                        ->whereYear('created_at',$nYear)
                        ->groupBy(DB::raw('YEAR(created_at)'))
                        ->get();
                    $grand_total_amount = 0;
                    $grand_total_vat_amount = 0;
                    if(count($product_sales) > 0){
                        foreach ($product_sales as $product_sale){
                            $grand_total_amount += $product_sale->sum_total_amount;
                            $grand_total_vat_amount += $product_sale->sum_total_vat_amount;
                        }
                    }

                    $years[] = [
                        'year'=>$nYear,
                        'product_sales'=>$product_sales
                    ];
                }

                $sale_infos['year_wise'] = [
                    //'product_sales' => $product_sales,
                    'grand_total_amount' => $grand_total_amount,
                    'grand_total_vat_amount' => $grand_total_vat_amount,
                    'years' => $years,
                ];
            }
        }

        $store_info = '';
        if($request->store_id){
            $store_info = DB::table('stores')
                ->where('id',$request->store_id)
                ->select('name','phone','email','address')
                ->first();
        }

        return response()->json(['success'=>true,'response' => $sale_infos,'store_info'=>$store_info], $this->successStatus);

    }

    public function dateAndSupplierWisePurchaseReport(Request $request){
        $from_date = $request->from_date;
        $to_date = $request->to_date;
        $purchases = DB::table('product_purchases')
            ->select('purchase_date as transaction_date','invoice_no','paid_amount as debit','total_amount as credit')
            ->where('party_id', $request->party_id)
            ->where('product_purchases.purchase_date','>=',"$from_date")
            ->where('product_purchases.purchase_date','<=',"$to_date")
            //->groupBy('product_purchases.invoice_no')
            ->get();


        if($purchases)
        {
            $data = [];
            foreach($purchases as $purchase){
                $nested_data['transaction_date']=$purchase->transaction_date;
                $nested_data['invoice_no']=$purchase->invoice_no;
                $nested_data['vch_type']='Purchase Invoice';
                $nested_data['debit']=$purchase->debit;
                $nested_data['credit']=$purchase->credit;

                array_push($data, $nested_data);
            }

            return response()->json(['success'=>true,'response' => $data], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>null], $this->successStatus);
        }
    }

    public function dateAndCustomerWiseWholeSaleReport(Request $request){
        try {
            // required and unique
            $validator = Validator::make($request->all(), [
                'from_date' => 'required',
                'to_date' => 'required',
            ]);

            if ($validator->fails()) {
                $response = APIHelpers::createAPIResponse(true,400,$validator->errors(),null);
                return response()->json($response,400);
            }

            $from_date = $request->from_date;
            $to_date = $request->to_date;

            $gl_pre_valance_data = DB::table('product_sales')
                ->join('parties','product_sales.party_id','parties.id')
                ->select(DB::raw('SUM(product_sales.total_amount) as debit, SUM(product_sales.paid_amount) as credit'))
                ->where('product_sales.party_id', $request->party_id)
                ->where('product_sales.sale_date','<',"$from_date")
                //->groupBy('product_sales.sale_date')
                ->first();

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

            $sales = DB::table('product_sales')
                ->join('parties','product_sales.party_id','parties.id')
                ->select('product_sales.sale_date as transaction_date','product_sales.invoice_no','product_sales.total_amount as debit','product_sales.paid_amount as credit','product_sales.due_amount as balance')
                ->where('product_sales.party_id', $request->party_id)
                ->where('product_sales.sale_date','>=',"$from_date")
                ->where('product_sales.sale_date','<=',"$to_date")
                //->groupBy('invoice_no')
                ->get();

            if($sales)
            {
                $total_balance = 0;
                $data = [];
                foreach($sales as $sale){
                    $nested_data['transaction_date']=$sale->transaction_date;
                    $nested_data['invoice_no']=$sale->invoice_no;
                    $nested_data['vch_type']='Sale Invoice';
                    $nested_data['debit']=$sale->debit;
                    $nested_data['credit']=$sale->credit;
                    $nested_data['balance']=$sale->balance;

                    $total_balance += $sale->balance;

                    array_push($data, $nested_data);
                }

                $customer_info = DB::table('parties')
                    ->where('id',$request->party_id)
                    ->select('name','phone','email','address')
                    ->first();

//                return response()->json(
//                    [
//                        'success'=>true,
//                        'response' => $data,
//                        'customer_info' => $customer_info,
//                        'total_balance' => $total_balance,
//                        'PreBalance' => $PreBalance,
//                        'preDebCre' => $preDebCre,
//                        'pre_debit' => $pre_debit,
//                        'pre_credit' => $pre_credit,
//                    ], $this->successStatus);

                $whole_sale_report = [
                    'success'=>true,
                    'response' => $data,
                    'customer_info' => $customer_info,
                    'total_balance' => $total_balance,
                    'PreBalance' => $PreBalance,
                    'preDebCre' => $preDebCre,
                    'pre_debit' => $pre_debit,
                    'pre_credit' => $pre_credit,
                ];

                $response = APIHelpers::createAPIResponse(false,200,'',$whole_sale_report);
                return response()->json($response,200);
            }else{
                //return response()->json(['success'=>false,'response'=>null], $this->successStatus);
                $response = APIHelpers::createAPIResponse(true,404,'No Whole Sale Report Found.',null);
                return response()->json($response,404);
            }
        } catch (\Exception $e) {
            //return $e->getMessage();
            $response = APIHelpers::createAPIResponse(false,500,'Internal Server Error.',null);
            return response()->json($response,500);
        }
    }

    public function dateWiseSalesLedger(Request $request){
        $validator = Validator::make($request->all(), [
            'party_id'=> 'required',
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









        if($request->from_date && $request->to_date){
            $chart_of_account_transaction = DB::table("chart_of_account_transaction_details")
                ->join('chart_of_account_transactions','chart_of_account_transaction_details.chart_of_account_transaction_id','=','chart_of_account_transactions.id')
                ->leftJoin('voucher_types','chart_of_account_transactions.voucher_type_id','=','voucher_types.id')
                ->where('chart_of_account_transaction_details.chart_of_account_name',$request->chart_of_account_name)
                ->where('chart_of_account_transaction_details.transaction_date','>=',$request->from_date)
                ->where('chart_of_account_transaction_details.transaction_date','<=',$request->to_date)
                ->where('chart_of_account_transaction_details.store_id','<=',$request->store_id)
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
                ->where('chart_of_account_transaction_details.store_id','<=',$request->store_id)
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


        if($chart_of_account_transaction)
        {
            $ledger_data = [
                'chart_of_account_transaction' => $chart_of_account_transaction,
                'party_id' => $request->party_id,
                'party_name' => $request->party_name,
                'from_date' => $request->from_date,
                'to_date' => $request->to_date,
            ];
            return response()->json(['success'=>true,'response' => $ledger_data], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>'No Chart Of Account Transaction Found!'], $this->failStatus);
        }
    }
}
