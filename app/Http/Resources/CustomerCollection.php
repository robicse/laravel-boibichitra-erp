<?php

namespace App\Http\Resources;

use App\Party;
use Illuminate\Http\Resources\Json\ResourceCollection;

class CustomerCollection extends ResourceCollection
{
    public function toArray($request)
    {
        return [
            'data' => $this->collection->map(function($data) {
                $party_info = Party::find($data->id);
                return [
                    'id' => $data->id,
                    'type' => $party_info->type,
                    'customer_type' => $party_info->customer_type,
                    'name' => $party_info->name,
                    'phone' => $party_info->phone,
                    'email' => $party_info->email,
                    'address' => $party_info->address,
                    'virtual_balance' => $party_info->virtual_balance,
                    'initial_due' => $party_info->initial_due,
                    'status' => $party_info->status,
//                    //'sale_total_amount' => customerSaleTotalAmount($data->id,$transaction_type) != null ? customerSaleTotalAmount($data->id,$transaction_type) : 0,
                    'sale_total_amount' => $data->total_amount,
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
