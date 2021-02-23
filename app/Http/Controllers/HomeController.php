<?php

namespace App\Http\Controllers;

use App\PaymentCollection;
use App\ProductSale;
use App\StockTransfer;
use App\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }


    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */

    public function index()
    {
        return view('home');
    }

    public function test()
    {
        //echo 'test';
        //return view('home');

        $stock_infos = StockTransfer::all();
        if(count($stock_infos) > 0){
            foreach ($stock_infos as $data){
                //echo $data->invoice_no.'<br/>';
                $stock_transfer_id = $data->id;

                $sum_sub_total = DB::table('stock_transfer_details')
                    ->where('stock_transfer_id', $stock_transfer_id)
                    ->select(DB::raw('SUM(sub_total) as total_amount'))
                    ->first();

                if($sum_sub_total){
                    //echo $sum_sub_total->total_amount.'<br/>';

                    $total_amount = $sum_sub_total->total_amount;
                    $stock_transfer_update = StockTransfer::find($stock_transfer_id);
                    $stock_transfer_update->total_amount = $total_amount;
                    $stock_transfer_update->due_amount = $total_amount;
                    $affectedRow =$stock_transfer_update->save();
                    if($affectedRow){
                        echo 'successfully updated.'.'<br/>';
                    }

                }
            }
        }
        die();
    }

    public function manually_pos_sale_update()
    {

        $product_pos_sales = DB::table('product_sales')
            ->where('sale_type')
            ->get();


        if(count($product_pos_sales) > 0){
            foreach ($product_pos_sales as $data){
                //echo $data->invoice_no.'<br/>';
                $product_sale_id = $data->id;
                $total_amount = $data->total_amount;

                $product_pos_sale_update = ProductSale::find($product_sale_id);
                $product_pos_sale_update->paid_amount=$total_amount;
                $product_pos_sale_update->due_amount=0;
                $affectedRow = $product_pos_sale_update->save();
                if($affectedRow){
                    // transaction update
                    $transaction = Transaction::where('ref_id',$product_sale_id)
                        ->where('transaction_type','pos_sale')
                        ->first();
                    $transaction->amount=$data->total_amount;
                    $transaction->save();

                    // payment collection update
                    $payment_collection = PaymentCollection::where('product_sale_id',$product_sale_id)
                        ->where('collection_type','Sale')
                        ->first();
                    $payment_collection->collection_amount=$data->total_amount;
                    $payment_collection->due_amount=0;
                    $payment_collection->current_collection_amount=$data->total_amount;
                    $payment_collection->save();

                    echo 'successfully updated.'.'<br/>';
                }
            }
        }
        die();
    }
}
