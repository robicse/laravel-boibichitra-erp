<?php

namespace App\Http\Resources;

use App\Transaction;
use Illuminate\Http\Resources\Json\ResourceCollection;

class ProductPurchaseCollection extends ResourceCollection
{
    public function toArray($request)
    {
        return [
            'data' => $this->collection->map(function($data) {
                $payment_type = Transaction::where('invoice_no',$data->invoice_no)->where('transaction_type','whole_purchase')->pluck('payment_type')->first();
                return [
                    'id' => $data->id,
                    'invoice_no' => $data->invoice_no,
                    'sub_total' => $data->sub_total,
                    'discount_type' => $data->discount_type,
                    'discount_amount' => $data->discount_amount,
                    'discount_percent' => $data->discount_percent,
                    'total_amount' => $data->total_amount,
                    'paid_amount' => $data->paid_amount,
                    'due_amount' => $data->due_amount,
                    'purchase_date_time' => $data->purchase_date_time,
                    'payment_type' => $payment_type,

                    'user_name' => userName($data->user_id),
                    'supplier_id' => $data->party_id,
                    'supplier_name' => partyName($data->party_id),
                    'warehouse_id' => $data->warehouse_id,
                    'warehouse_name' => warehouseName($data->warehouse_id),
                    //'payment_type' => paymentType($data->id),
                ];
            })
        ];
    }

    public function with($request)
    {
        return [
            'success' => true,
            'code' => 200
        ];
    }
}
