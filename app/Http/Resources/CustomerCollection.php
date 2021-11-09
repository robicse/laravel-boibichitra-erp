<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class CustomerCollection extends ResourceCollection
{
    public function toArray($request)
    {
        return [
            'data' => $this->collection->map(function($data) {
                $transaction_type = $data->customer_type == 'POS Sale' ? 'pos_sale' : 'whole_sale';
                return [
                    'id' => $data->id,
                    'type' => $data->type,
                    'customer_type' => $data->customer_type,
                    'name' => $data->name,
                    'phone' => $data->phone,
                    'address' => $data->address,
                    'virtual_balance' => $data->virtual_balance,
                    'initial_due' => $data->initial_due,
                    'status' => $data->status,
                    'sale_total_amount' => customerSaleTotalAmount($data->id,$transaction_type) != null ? customerSaleTotalAmount($data->id,$transaction_type) : 0,
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
