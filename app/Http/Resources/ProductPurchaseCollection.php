<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class ProductPurchaseCollection extends ResourceCollection
{
    public function toArray($request)
    {
        return [
            'data' => $this->collection->map(function($data) {
                return [
                    'id' => $data->id,
                    'invoice_no' => $data->invoice_no,
                    'discount_type' => $data->discount_type,
                    'discount_amount' => $data->discount_amount,
                    'total_amount' => $data->total_amount,
                    'paid_amount' => $data->paid_amount,
                    'due_amount' => $data->due_amount,
                    'purchase_date_time' => $data->purchase_date_time,

                    'user_name' => userName($data->user_id),
                    'supplier_id' => $data->party_id,
                    'supplier_name' => partyName($data->party_id),
                    'warehouse_id' => $data->warehouse_id,
                    'warehouse_name' => warehouseName($data->warehouse_id),
                    'payment_type' => paymentType($data->id),
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
