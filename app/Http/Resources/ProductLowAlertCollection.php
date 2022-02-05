<?php

namespace App\Http\Resources;

use App\ProductUnit;
use App\Warehouse;
use App\warehouseCurrentStock;
use Illuminate\Http\Resources\Json\ResourceCollection;

class ProductLowAlertCollection extends ResourceCollection
{
    public function toArray($request)
    {
        return [
            'data' => $this->collection->map(function($data) {
                $warehouse_current_stock = WarehouseCurrentStock::where('product_id',$data->id)
                    ->latest('id')
                    ->pluck('current_stock')
                    ->first();

                    return [
                        'warehouse_name' => Warehouse::where('id',6)->pluck('name')->first(),
                        'product_id' => $data->id,
                        'product_name' => $data->name,
                        'low_inventory_alert' => $data->low_inventory_alert,
                        'warehouse_current_stock' => $warehouse_current_stock,
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
