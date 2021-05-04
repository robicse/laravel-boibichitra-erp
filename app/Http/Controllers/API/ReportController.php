<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\ProductSale;
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
    public function dateWiseSalesReport(Request $request){
        $validator = Validator::make($request->all(), [
            'from_date' => 'required',
            'to_date'=> 'required',
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


        if($sale_type != ''){
            if($sale_type == 'pos_sale'){
                if($store_id != 0){
                    $product_sales = ProductSale::where('sale_date','>=',$from_date)
                        ->where('sale_date','<=',$to_date)
                        ->where('sale_type',$sale_type)
                        ->where('store_id',$store_id)
                        ->get();
                    $total_sale_history = DB::table('product_sales')
                        ->where('sale_date','>=',$from_date)
                        ->where('sale_date','<=',$to_date)
                        ->where('sale_type',$sale_type)
                        ->where('store_id',$store_id)
                        ->select(DB::raw('SUM(total_amount) as total_sale'))
                        ->first();
                }else{
                    $product_sales = ProductSale::where('sale_date','>=',$from_date)
                        ->where('sale_date','<=',$to_date)
                        ->where('sale_type',$sale_type)
                        ->get();
                    $total_sale_history = DB::table('product_sales')
                        ->where('sale_date','>=',$from_date)
                        ->where('sale_date','<=',$to_date)
                        ->where('sale_type',$sale_type)
                        ->select(DB::raw('SUM(total_amount) as total_sale'))
                        ->first();
                }

            }elseif($sale_type == 'whole_sale'){
                if($warehouse_id != 0){
                    $product_sales = ProductSale::where('sale_date','>=',$from_date)
                        ->where('sale_date','<=',$to_date)
                        ->where('sale_type',$sale_type)
                        ->where('warehouse_id',$warehouse_id)
                        ->get();
                    $total_sale_history = DB::table('product_sales')
                        ->where('sale_date','>=',$from_date)
                        ->where('sale_date','<=',$to_date)
                        ->where('sale_type',$sale_type)
                        ->where('warehouse_id',$warehouse_id)
                        ->select(DB::raw('SUM(total_amount) as total_sale'))
                        ->first();
                }else{
                    $product_sales = ProductSale::where('sale_date','>=',$from_date)
                        ->where('sale_date','<=',$to_date)
                        ->where('sale_type',$sale_type)
                        ->get();
                    $total_sale_history = DB::table('product_sales')
                        ->where('sale_date','>=',$from_date)
                        ->where('sale_date','<=',$to_date)
                        ->where('sale_type',$sale_type)
                        ->select(DB::raw('SUM(total_amount) as total_sale'))
                        ->first();
                }

            }else{
                $product_sales = ProductSale::where('sale_date','>=',$from_date)
                    ->where('sale_date','<=',$to_date)
                    ->where('sale_type',$sale_type)
                    ->get();
                $total_sale_history = DB::table('product_sales')
                    ->where('sale_date','>=',$from_date)
                    ->where('sale_date','<=',$to_date)
                    ->where('sale_type',$sale_type)
                    ->select(DB::raw('SUM(total_amount) as total_sale'))
                    ->first();
            }

            $grand_total_amount = $total_sale_history->total_sale;
        }else{
            $product_sales = ProductSale::where('sale_date','>=',$from_date)
                ->where('sale_date','<=',$to_date)
                ->get();

            $total_sale_history = DB::table('product_sales')
                ->where('sale_date','>=',$from_date)
                ->where('sale_date','<=',$to_date)
                ->select(DB::raw('SUM(total_amount) as total_sale'))
                ->first();
            $grand_total_amount = $total_sale_history->total_sale;
        }

        if($product_sales)
        {
            return response()->json(['success'=>true,'response' => $product_sales,'grand_total_amount'=>$grand_total_amount], $this->successStatus);
        }else{
            return response()->json(['success'=>false,'response'=>null], $this->successStatus);
        }
    }
}
