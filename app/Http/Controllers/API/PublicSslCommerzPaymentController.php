<?php

namespace App\Http\Controllers\API;

use App\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use Session;
use App\Http\Controllers\Controller;
session_start();

class PublicSslCommerzPaymentController extends Controller
{
    public function index(Request $request)
    {

        $transaction =  Transaction::where('id',$request->transaction_id)->firstOrFail();

        $amount =$transaction->amount;
        //$shipping_cost = number_format(Session::get('shipping_cost'),2);
        $total = $amount;
        # Here you have to receive all the order data to initate  payment.
        # Lets your oder trnsaction informations are saving in a table called "orders"
        # In orders table order uniq identity is "order_id","transaction_status" field contain status of the transaction, "grand_total" is the order amount to be paid and "currency" is for storing Site Currency which will be checked with paid currency.

        $post_data = array();
        $post_data['total_amount'] = $total; # You cant not pay less than 10
        $post_data['currency'] = "BDT";
        $post_data['tran_id'] = uniqid(); // tran_id must be unique


        Transaction::where('id',$request->transaction_id)->update(
            array(
                'currency' => $post_data['currency'],
                'transaction_id' => $post_data['tran_id'],
                'transaction_status' => 'Pending',
            )
        );


        #Start to save these value  in session to pick in success page.
        $_SESSION['payment_values']['tran_id']=$post_data['tran_id'];
        #End to save these value  in session to pick in success page.
//dd($_SESSION['payment_values']['tran_id']);
//        $server_name=$request->root()."/";
        $server_name=url('/');
        $post_data['success_url'] = $server_name . "/api/success";
        $post_data['fail_url'] = $server_name . "/api/fail";
        $post_data['cancel_url'] = $server_name . "/api/cancel";

        #Before  going to initiate the payment order status need to update as Pending.
        $sslc = new SSLCommerz();
        # initiate(Transaction Data , false: Redirect to SSLCOMMERZ gateway/ true: Show all the Payement gateway here )
        $payment_options = $sslc->initiate($post_data, true);
///        dd($payment_options);

        if (!is_array($payment_options)) {
            print_r($payment_options);
            $payment_options = array();
        }

    }

    public function success(Request $request)
    {
        //dd($request->all());
        //echo "Transaction is Successful";
        $sslc = new SSLCommerz();
        #Start to received these value from session. which was saved in index function.
        $tran_id = $request->tran_id;
        //$tran_id = $_SESSION['payment_values']['tran_id'];
        #End to received these value from session. which was saved in index function.
        #Check order status in order tabel against the transaction id or order id.

        $order_detials = DB::table('transactions')
            ->where('transaction_id', $tran_id)
            ->select('transaction_id', 'transaction_status','currency','amount')->first();


        //$chekTotal= $order_detials->order_price + number_format(Session::get('shipping_cost'),2);
        $chekTotal= $order_detials->amount;

        if($order_detials->transaction_status=='Pending')
        {

            $validation = $sslc->orderValidate($tran_id, $chekTotal, $order_detials->currency, $request->all());
            if($validation == TRUE)
            {
                /*
                That means IPN did not work or IPN URL was not set in your merchant panel. Here you need to update order status
                in order table as Processing or Complete.
                Here you can also sent sms or email for successfull transaction to customer
                */
                DB::table('transactions')
                    ->where('transaction_id', $tran_id)
                    ->update([
                        'transaction_status' => 'Completed',
                        //'payment_method' => $_POST['card_type'],
                        'payment_details' => json_encode($_POST),
                    ]);

                //Toastr::success('Transaction is successfully Completed tar','Success');
                //Cart::destroy();
                Session::forget('order_id');
                //Session::forget('shipping_cost');
//                $message = Lang::get("website.Payment has been processed successfully");
//                return redirect('/');

                 return redirect('api/ssl/redirect/success');

            }
            else
            {
                /*
                That means IPN did not work or IPN URL was not set in your merchant panel and Transation validation failed.
                Here you need to update order status as Failed in order table.
                */
                DB::table('transactions')
                    ->where('transaction_id', $tran_id)
                    ->update(['transaction_status' => 'Failed']);
//                echo "validation Fail";
                return redirect('api/ssl/redirect/fail');
            }
        }
        else if($order_detials->ssl_status=='Processing' || $order_detials->ssl_status=='Complete')
        {
            /*
             That means through IPN Order status already updated. Now you can just show the customer that transaction is completed. No need to udate database.
             */
            //echo "Transaction is successfully Complete ash";
//            Toastr::success('Transaction is successfully Completed tar','Success');
            ////////Cart::destroy();
            return redirect('api/ssl/redirect/fail');
        }
        else
        {
            #That means something wrong happened. You can redirect customer to your product page.
            //echo "Invalid Transaction";
//            Toastr::error('Invalid Transaction ','Error');
            ////////Cart::destroy();
//            return redirect('/checkout')->withSuccess('Invalid Transaction');
            return redirect('api/ssl/redirect/fail');
        }
    }
    public function fail(Request $request)
    {
        $tran_id = $request->tran_id;
        $order_detials = DB::table('transactions')
            ->where('transaction_id', $tran_id)
            ->select('id', 'transaction_status','currency','amount')->first();

        if($order_detials->transaction_status=='Pending')
        {
            //dd($_POST);
            DB::table('transactions')
                ->where('transaction_id', $tran_id)
                ->update(['transaction_status' => 'Failed']);
            return redirect('api/ssl/redirect/fail');
        }
        else if($order_detials->transaction_status=='Processing' || $order_detials->transaction_status=='Complete')
        {
            return redirect('api/ssl/redirect/success');
        }
        else
        {
//            echo "Transaction is Invalid";
            return redirect('api/ssl/redirect/fail');
        }
    }

    public function cancel(Request $request)
    {
        $tran_id = $tran_id = $request->tran_id;
        $order_detials = DB::table('transactions')
            ->where('transaction_id', $tran_id)
            ->select('id', 'transaction_status','currency','amount')->first();
//dd($order_detials);
        if($order_detials->transaction_status=='Pending')
        {
            DB::table('transactions')
                ->where('transaction_id', $tran_id)
                ->update(['transaction_status' => 'Canceled']);
            echo "Transaction is Cancel";
        }
        else if($order_detials->transaction_status=='Processing' || $order_detials->transaction_status=='Complete')
        {
//            echo "Transaction is already Successful";
            return redirect('api/ssl/redirect/success');
        }
        else
        {
//            echo "Transaction is Invalid";
            return redirect('api/ssl/redirect/cancel');
        }


    }
    public function ipn(Request $request)
    {
        #Received all the payement information from the gateway
        if($request->input('tran_id')) #Check transation id is posted or not.
        {

            $tran_id = $request->input('tran_id');

            #Check order status in order tabel against the transaction id or order id.
            $order_details = DB::table('transactions')
                ->where('transaction_id', $tran_id)
                ->select('id', 'transaction_status','currency','amount')->first();

            if($order_details->transaction_status =='Pending')
            {
                $sslc = new SSLCommerz();
                $validation = $sslc->orderValidate($tran_id, $order_details->amount, $order_details->currency, $request->all());
                if($validation == TRUE)
                {
                    /*
                     *
                    That means IPN worked. Here you need to update order status
                    in order table as Processing or Complete.
                    Here you can also sent sms or email for successfull transaction to customer
                    */
                    DB::table('transactions')
                        ->where('transaction_id', $tran_id)
                        ->update(['transaction_status' => 'Complete']);

                    echo "Transaction is successfully Complete";
                }
                else
                {
                    /*
                    That means IPN worked, but Transation validation failed.
                    Here you need to update order status as Failed in order table.
                    */
                    DB::table('transactions')
                        ->where('order_id', $tran_id)
                        ->update(['transaction_status' => 'Failed']);

                    echo "validation Fail";
                }

            }
            else if($order_details->transaction_status == 'Processing' || $order_details->transaction_status =='Complete')
            {

                #That means Order status already updated. No need to udate database.

                echo "Transaction is already successfully Complete";
            }
            else
            {
                #That means something wrong happened. You can redirect customer to your product page.

                echo "Invalid Transaction";
            }
        }
        else
        {
            echo "Inavalid Data";
        }
    }
    public function status($status)
    {
        return view("status",compact('status'));
    }
}

