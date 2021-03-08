<?php

namespace App\Http\Controllers;

use App\Stock;
use App\WarehouseCurrentStock;
use App\WarehouseStoreCurrentStock;
use Illuminate\Http\Request;

class StockSyncController extends Controller
{
    // stock sync
    function product_store_stock_sync($warehouse_id,$store_id,$product_id){

        echo 'warehouse_id => '.$warehouse_id.'<br/>';
        echo 'store_id => '.$store_id.'<br/>';
        echo 'product_id => '.$product_id.'<br/>';
        echo '-------------'.'<br/>';
        echo '<br/>';

        if($store_id){
            $stock_data = Stock::where('warehouse_id',$warehouse_id)->where('store_id',$store_id)->where('product_id',$product_id)->get();
            //$stock_data = [];
        }else{
            $stock_data = Stock::where('warehouse_id',$warehouse_id)->where('store_id','=',NULL)->where('product_id',$product_id)->get();
            //$stock_data = [];
        }



        $row_count = count($stock_data);
        if($row_count > 0) {
            $store_previous_row_current_stock = null;
            $stock_in_flag = 0;
            $stock_out_flag = 0;

            foreach ($stock_data as $key => $data) {

//                $id = $data->id;
//                echo 'id=>' . $id . '<br/>';
//                echo 'previous_stock=>' . $data->previous_stock . '<br/>';
//                echo '<br/>';



                $id = $data->id;
                $previous_stock = $data->previous_stock;
                $stock_in = $data->stock_in;
                $stock_out = $data->stock_out;
                $current_stock = $data->current_stock;



                if($key == 0) {
                    echo 'row_id =>' . $id . '<br/>';
                    echo 'warehouse_id =>' . $warehouse_id . '<br/>';
                    echo 'store_id =>' . $store_id . '<br/>';
                    echo 'product_id =>' . $product_id . '<br/>';
                    echo 'store_previous_row_current_stock => ' . $store_previous_row_current_stock . '<br/>';
                    echo 'this_row_current_stock =>' . $current_stock . '<br/>';
                    echo '<br/>';

                    $stock = Stock::find($id);
                    $stock->previous_stock = 0;
                    $stock->current_stock = $stock_in;
                    $affectedRow = $stock->update();
                    if($affectedRow){
                        echo 'this_row_current_stock => updated => '.$stock_in.'<br/>';
                        echo '<br/>';
                        $current_stock = $stock->current_stock;
                    }
                }else{
                    echo 'row_id =>'.$id.'<br/>';
                    echo 'warehouse_id =>' . $warehouse_id . '<br/>';
                    echo 'store_id =>'.$store_id.'<br/>';
                    echo 'product_id =>'.$product_id.'<br/>';
                    echo 'store_previous_row_current_stock => '.$store_previous_row_current_stock.'<br/>';
                    echo 'this_row_current_stock =>'.$current_stock.'<br/>';
                    echo '<br/>';

                    // update part
                    if($stock_in > 0){
                        if($stock_in_flag == 1){
                            $stock = Stock::find($id);
                            $stock->previous_stock = $store_previous_row_current_stock;
                            $stock->current_stock = $store_previous_row_current_stock + $stock_in;
                            $affectedRow = $stock->update();
                            if($affectedRow){
                                echo 'this_row_current_stock => updated => '.$stock_in.'<br/>';
                                echo '<br/>';
                                $current_stock = $stock->current_stock;
                            }
                        }else if($previous_stock != $store_previous_row_current_stock){
                            $stock_in_flag = 1;

                            $stock = Stock::find($id);
                            $stock->previous_stock = $store_previous_row_current_stock;
                            $stock->current_stock = $store_previous_row_current_stock + $stock_in;
                            $affectedRow = $stock->update();
                            if($affectedRow){
                                echo 'this_row_current_stock => updated => '.$stock_in.'<br/>';
                                echo '<br/>';
                                $current_stock = $stock->current_stock;
                            }
                        }else{
                            echo 'this_row_current_stock => nothing => '.$stock_in.'<br/>';
                            echo '<br/>';
                        }
                    }else if($stock_out > 0){
                        if($stock_out_flag == 1) {
                            $stock = Stock::find($id);
                            $stock->previous_stock = $store_previous_row_current_stock;
                            $stock->current_stock = $store_previous_row_current_stock - $stock_out;
                            $affectedRow = $stock->update();
                            if ($affectedRow) {
                                echo 'This Row('.$id.') Current Stock => updated => ' . $stock_out . '<br/>';
                                echo '<br/>';
                                $current_stock = $stock->current_stock;
                            }
                        }else if($previous_stock != $store_previous_row_current_stock) {
                            $stock_out_flag = 1;

                            $stock = Stock::find($id);
                            $stock->previous_stock = $store_previous_row_current_stock;
                            $stock->current_stock = $store_previous_row_current_stock - $stock_out;
                            $affectedRow = $stock->update();
                            if ($affectedRow) {
                                echo 'This Row('.$id.') Current Stock => updated =>' . $stock_out . '<br/>';
                                echo '<br/>';
                                $current_stock = $stock->current_stock;
                            }
                        }else{
                            echo 'this_row_current_stock => nothing => '.$stock_out.'<br/>';
                            echo '<br/>';
                        }
                    }else{
                        echo 'this_row_current_stock => nothing<br/>';
                        echo '<br/>';
                    }
                }
                $store_previous_row_current_stock = $current_stock;
            }
        }else{
//            echo 'no found!'.'<br/>';
        }

    }


    function stock_sync(){
        $stock_data = Stock::whereIn('id', function($query) {
            $query->from('stocks')->groupBy('warehouse_id')->groupBy('store_id')->groupBy('product_id')->selectRaw('MIN(id)');
        })->get();

        $row_count = count($stock_data);
        if($row_count > 0){
            foreach ($stock_data as $key => $data){
                $warehouse_id = $data->warehouse_id;
                $store_id = $data->store_id;
                $product_id = $data->product_id;
                $this->product_store_stock_sync($warehouse_id,$store_id,$product_id);
            }
            //Toastr::success('Stock Synchronize Successfully Updated!', 'Success');
        }
        //return redirect()->back();
        die();
    }

    function warehouse_stock_sync(){
        $stock_data = Stock::whereIn('id', function($query) {
            $query->from('stocks')
                ->where('stock_where','warehouse')
                ->groupBy('warehouse_id')
                //->groupBy('store_id')
                ->groupBy('product_id')
                //->selectRaw('MIN(id)');
                ->selectRaw('MAX(id)');
        })->get();

        $row_count = count($stock_data);
        if($row_count > 0){
            foreach ($stock_data as $key => $data){
                $stock_id = $data->id;
                $warehouse_id = $data->warehouse_id;
                //$store_id = $data->store_id;
                $product_id = $data->product_id;
                $current_stock = $data->current_stock;

//                echo 'stock_id => '.$stock_id.'<br/>';
//                echo 'warehouse_id => '.$warehouse_id.'<br/>';
//                echo 'product_id => '.$product_id.'<br/>';
//                echo 'current_stock => '.$current_stock.'<br/>';
//                echo '<br/>';

                $check_exists_warehouse_current_stock = WarehouseCurrentStock::where('warehouse_id',$warehouse_id)
                    ->where('product_id',$product_id)
                    ->first();
                if($check_exists_warehouse_current_stock){
                    $warehouse_current_stock_update = WarehouseCurrentStock::find($check_exists_warehouse_current_stock->id);
                    $warehouse_current_stock_update->current_stock=$current_stock;
                    $warehouse_current_stock_update->save();

                    echo 'this_row_current_stock => updated<br/>';
                    echo '<br/>';
                }else{
                    $warehouse_current_stock = new WarehouseCurrentStock();
                    $warehouse_current_stock->warehouse_id=$warehouse_id;
                    $warehouse_current_stock->product_id=$product_id;
                    $warehouse_current_stock->current_stock=$current_stock;
                    $warehouse_current_stock->save();

                    echo 'this_row_current_stock => inserted<br/>';
                    echo '<br/>';
                }

                //$this->product_store_stock_sync($warehouse_id,$store_id,$product_id);
            }
        }

        die();
    }

    function warehouse_store_stock_sync(){
        $stock_data = Stock::whereIn('id', function($query) {
            $query->from('stocks')
                ->where('store_id','!=',NULL)
                ->groupBy('warehouse_id')
                ->groupBy('store_id')
                ->groupBy('product_id')
                //->selectRaw('MIN(id)');
                ->selectRaw('MAX(id)');
        })->get();

        $row_count = count($stock_data);
        if($row_count > 0){
            foreach ($stock_data as $key => $data){
                $stock_id = $data->id;
                $warehouse_id = $data->warehouse_id;
                $store_id = $data->store_id;
                $product_id = $data->product_id;
                $current_stock = $data->current_stock;

//                echo 'stock_id => '.$stock_id.'<br/>';
//                echo 'warehouse_id => '.$warehouse_id.'<br/>';
//                echo 'store_id => '.$store_id.'<br/>';
//                echo 'product_id => '.$product_id.'<br/>';
//                echo 'current_stock => '.$current_stock.'<br/>';
//                echo '<br/>';

                $check_exists_warehouse_store_current_stock = WarehouseStoreCurrentStock::where('warehouse_id',$warehouse_id)
                    ->where('store_id',$store_id)
                    ->where('product_id',$product_id)
                    ->first();
                if($check_exists_warehouse_store_current_stock){
                    $warehouse_store_current_stock_update = WarehouseStoreCurrentStock::find($check_exists_warehouse_store_current_stock->id);
                    $warehouse_store_current_stock_update->current_stock=$current_stock;
                    $warehouse_store_current_stock_update->save();

                    echo 'this_row_current_stock => updated<br/>';
                    echo '<br/>';
                }else{
                    $warehouse_store_current_stock = new WarehouseStoreCurrentStock();
                    $warehouse_store_current_stock->warehouse_id=$warehouse_id;
                    $warehouse_store_current_stock->store_id=$store_id;
                    $warehouse_store_current_stock->product_id=$product_id;
                    $warehouse_store_current_stock->current_stock=$current_stock;
                    $warehouse_store_current_stock->save();

                    echo 'this_row_current_stock => inserted<br/>';
                    echo '<br/>';
                }

                //$this->product_store_stock_sync($warehouse_id,$store_id,$product_id);
            }
        }

        die();
    }
}
