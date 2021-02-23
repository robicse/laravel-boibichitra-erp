<?php

namespace App\Http\Controllers;

use App\StockTransfer;
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
}
