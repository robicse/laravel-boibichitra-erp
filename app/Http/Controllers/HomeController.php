<?php

namespace App\Http\Controllers;

use App\PaymentCollection;
use App\Product;
use App\ProductSale;
use App\ProductSaleDetail;
use App\StockTransfer;
use App\StockTransferDetail;
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
        //$this->middleware('auth');
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

    public function test_helper()
    {
        return test_helper();
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
            ->where('sale_type','pos_sale')
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

    public function manually_stock_transfer_vat_update()
    {





        $stock_transfers = DB::table('stock_transfers')
            ->where('total_vat_amount','>',0)
            ->get();


        if(count($stock_transfers) > 0){
            foreach ($stock_transfers as $data){
                //echo $data->invoice_no.'<br/>';
                $stock_transfer_id = $data->id;
                $total_vat_amount = $data->total_vat_amount;
                $total_amount = $data->total_amount;
                //$due_amount = $data->due_amount;
                $current_total_amount = $total_amount - $total_vat_amount;
                //echo 'stock_transfer_id'.$stock_transfer_id.'<br/>';

                $stock_transfer_update = StockTransfer::find($stock_transfer_id);
                $stock_transfer_update->total_vat_amount=0;
                $stock_transfer_update->total_amount=$current_total_amount;
                $stock_transfer_update->due_amount=$current_total_amount;
                $affectedRow = $stock_transfer_update->save();
                if($affectedRow){
                    // transaction update
                    $stock_transfer_details = StockTransferDetail::where('stock_transfer_id',$stock_transfer_id)
                        //->where('transaction_type','pos_sale')
                        ->get();
                    if(count($stock_transfer_details) > 0){
                        foreach ($stock_transfer_details as $sdata){
                            $stock_transfer_detail_id = $sdata->id;
                            $vat_amount = $sdata->vat_amount;
                            $sub_total = $sdata->sub_total;
                            $current_sub_total_amount = $sub_total - $vat_amount;

                            $stock_transfer_detail_update = StockTransferDetail::find($stock_transfer_detail_id);
                            $stock_transfer_detail_update->vat_amount=0;
                            $stock_transfer_detail_update->sub_total=$current_sub_total_amount;
                            $stock_transfer_detail_update->save();
                        }
                    }
                }
                echo 'successfully updated.'.'<br/>';
            }
        }



//        $stock_transfer_details = StockTransferDetail::where('vat_amount','>',0)->get();
//        if(count($stock_transfer_details) > 0){
//            foreach ($stock_transfer_details as $sdata){
//                $stock_transfer_detail_id = $sdata->id;
//                //echo 'stock_transfer_detail_id'.$stock_transfer_detail_id.'<br/>';
//                $vat_amount = $sdata->vat_amount;
//                $sub_total = $sdata->sub_total;
//                $current_sub_total_amount = $sub_total - $vat_amount;
//
//                $stock_transfer_detail_update = StockTransferDetail::find($stock_transfer_detail_id);
//                $stock_transfer_detail_update->vat_amount=0;
//                $stock_transfer_detail_update->sub_total=$current_sub_total_amount;
//                $affectedRow = $stock_transfer_detail_update->save();
//                if($affectedRow){
//                    echo 'successfully updated.'.'<br/>';
//                }
//            }
//        }
        die();
    }

    public function manually_discount_update(){
        // manually product sale discount add
        //$productPosSales = ProductSale::where('sale_type','pos')->get();
        $productPosSales = ProductSale::all();
        if(count($productPosSales)){
            foreach ($productPosSales as $productPosSale){
                //echo $productPosSale->id.'<br/>';
                if($productPosSale->discount_amount > 0){
                    //echo $productPosSale->id.'<br/>';
                    $discount_amount = $productPosSale->discount_amount;
                    $total_amount = $productPosSale->total_amount;
                    $discount_type = $productPosSale->discount_type;
                    //echo '<br/>';

                    $productSaleDetails = ProductSaleDetail::where('product_sale_id',$productPosSale->id)->get();
                    foreach ($productSaleDetails as $productSaleDetail){

                        //echo $discount_amount;
                        //echo $total_amount;
                        $price = $productSaleDetail->price;
                        $final_discount_amount = (float)$discount_amount * (float)$price;
                        $final_total_amount = (float)$discount_amount + (float)$total_amount;
                        //echo '<br/>';
                        //echo '<br/>';



                        $discount = (float)$final_discount_amount/(float)$final_total_amount;
                        if($discount_type == 'Flat'){
                            $discount = round($discount);
                        }

                        $updateProductSaleDetailDiscount = ProductSaleDetail::find($productSaleDetail->id);
                        $updateProductSaleDetailDiscount->discount=$discount;
                        $affectedRow = $updateProductSaleDetailDiscount->update();
                        if($affectedRow){
                            echo 'updated id = '.$productSaleDetail->id;
                            echo 'updated discount = '.$discount;
                            echo '<br/>';
                        }
                    }
                }
            }
        }
        dd('ops');
    }

    public function manually_purchase_price_update(){
        // manually product sale discount add
        //$productPosSales = ProductSale::where('sale_type','pos')->get();
        $productSaleDetails = ProductSaleDetail::all();
        if(count($productSaleDetails)){
            foreach ($productSaleDetails as $productSaleDetail){
                $product_sale_detail_id = $productSaleDetail->id;
                $product_id = $productSaleDetail->product_id;
                $get_purchase_price = Product::where('id',$product_id)->pluck('purchase_price')->first();
                $productSaleDetail = ProductSaleDetail::find($product_sale_detail_id);
                $productSaleDetail->purchase_price = $get_purchase_price;
                $affectedRow = $productSaleDetail->update();
                if($affectedRow){
                    echo 'updated id = '.$productSaleDetail->id;
                    echo 'updated discount = '.$get_purchase_price;
                    echo '<br/>';
                }
            }
        }
        dd('ops');
    }

    public function backup_database()
    {
        // Database configuration
        $host = "127.0.0.1";
        //erp
        $username = "erp_boibichitra_user";
        $password = "mGubJAw6e+m834Bs";
        $database_name = "erp_boibichitra_db";
        //dev
//        $username = "dev_boibichitra_user";
//        $password = "v4PAzgdmt9IN6EiP";
//        $database_name = "dev_boibichitra_db";

        // Get connection object and set the charset
        $conn = mysqli_connect($host, $username, $password, $database_name);
        $conn->set_charset("utf8");


        // Get All Table Names From the Database
        $tables = array();
        $sql = "SHOW TABLES";
        $result = mysqli_query($conn, $sql);

        while ($row = mysqli_fetch_row($result)) {
            $tables[] = $row[0];
        }

        $sqlScript = "";
        foreach ($tables as $table) {

            // Prepare SQLscript for creating table structure
            $query = "SHOW CREATE TABLE $table";
            $result = mysqli_query($conn, $query);
            $row = mysqli_fetch_row($result);

            $sqlScript .= "\n\n" . $row[1] . ";\n\n";


            $query = "SELECT * FROM $table";
            $result = mysqli_query($conn, $query);

            $columnCount = mysqli_num_fields($result);

            // Prepare SQLscript for dumping data for each table
            for ($i = 0; $i < $columnCount; $i ++) {
                while ($row = mysqli_fetch_row($result)) {
                    $sqlScript .= "INSERT INTO $table VALUES(";
                    for ($j = 0; $j < $columnCount; $j ++) {
                        $row[$j] = $row[$j];

                        if (isset($row[$j])) {
                            $sqlScript .= '"' . $row[$j] . '"';
                        } else {
                            $sqlScript .= '""';
                        }
                        if ($j < ($columnCount - 1)) {
                            $sqlScript .= ',';
                        }
                    }
                    $sqlScript .= ");\n";
                }
            }

            $sqlScript .= "\n";
        }

        if(!empty($sqlScript))
        {
            // Save the SQL script to a backup file
            $backup_file_name = $database_name . '_backup_' . time() . '.sql';
            $fileHandler = fopen($backup_file_name, 'w+');
            $number_of_lines = fwrite($fileHandler, $sqlScript);
            fclose($fileHandler);

            // Download the SQL backup file to the browser
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename=' . basename($backup_file_name));
            header('Content-Transfer-Encoding: binary');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($backup_file_name));
            ob_clean();
            flush();
            readfile($backup_file_name);
            exec('rm ' . $backup_file_name);
        }
    }
}
