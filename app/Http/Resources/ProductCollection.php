<?php

namespace App\Http\Resources;

use App\ProductUnit;
use Illuminate\Http\Resources\Json\ResourceCollection;

class ProductCollection extends ResourceCollection
{
    public function toArray($request)
    {
        return [
            'data' => $this->collection->map(function($data) {
                $unit_name = ProductUnit::where('id',$data->product_unit_id)->pluck('name')->first();
                return [
                    'id' => $data->id,
                    'product_name' => $data->name,
                    'image' => $data->image,
                    'unit_id' => $data->product_unit_id,
                    'unit_name' => $unit_name,
                    'item_code' => $data->item_code,
                    'barcode' => $data->barcode,
                    'self_no' => $data->self_no,
                    'low_inventory_alert' => $data->low_inventory_alert,
                    //'brand_id' => $data->brand_id,
                    //'brand_name' => $data->brand_id,
                    'purchase_price' => $data->purchase_price,
                    'whole_sale_price' => $data->whole_sale_price,
                    'selling_price' => $data->selling_price,
                    'note' => $data->note,
                    'date' => $data->date,
                    'status' => $data->status,
                    'vat_status' => $data->vat_status,
                    'vat_percentage' => $data->vat_percentage,
                    'vat_amount' => $data->vat_amount,
                    'vat_whole_amount' => $data->vat_whole_amount,
                    'warehouse_current_stock' => warehouseCurrentStock($data->id),
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
