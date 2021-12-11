<?php

namespace App\Http\Resources;

use App\Transaction;
use Illuminate\Http\Resources\Json\ResourceCollection;

class ProductPOSSaleCollection extends ResourceCollection
{
    public function toArray($request)
    {
        return [
            'data' => $this->collection->map(function($data) {
                $payment_type = Transaction::where('ref_id',$data->id)->where('transaction_type','pos_sale')->pluck('payment_type')->first();
                return [
                    'id' => $data->id,
                    'invoice_no' => $data->invoice_no,
                    'sale_date' => $data->sale_date,
                    'sub_total' => $data->sub_total,
                    'miscellaneous_comment' => $data->miscellaneous_comment,
                    'miscellaneous_charge' => $data->miscellaneous_charge,
                    'discount_type' => $data->discount_type,
                    'discount_percent' => $data->discount_percent,
                    'discount_amount' => $data->discount_amount,
                    'total_vat_amount' => $data->total_vat_amount,
                    'after_discount_amount' => $data->after_discount_amount,
                    'total_amount' => $data->total_amount,
                    'paid_amount' => $data->paid_amount,
                    'due_amount' => $data->due_amount,
                    'sale_date_time' => $data->sale_date_time,

                    'user_name' => userName($data->user_id),
                    'customer_id' => $data->party_id,
                    'customer_name' => partyName($data->party_id),
                    'customer_phone' => partyPhone($data->party_id),
                    'customer_email' => partyEmail($data->party_id),
                    'customer_address' => partyAddress($data->party_id),
                    'warehouse_id' => $data->warehouse_id,
                    'warehouse_name' => warehouseName($data->warehouse_id),
                    'store_id' => $data->store_id,
                    'store_name' => storeName($data->store_id),
                    'store_address' => storeAddress($data->store_id),
                    'phone' => storePhone($data->store_id),
                    //'payment_type' => paymentType($data->id),
                    'payment_type' => $payment_type
                ];
            })
        ];
    }

    public function with($request)
    {
        return [
            'success' => true,
            'status' => 200
        ];
    }
}
