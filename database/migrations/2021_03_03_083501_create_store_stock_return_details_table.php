<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStoreStockReturnDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('store_stock_return_details', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('store_stock_return_id')->unsigned();
            $table->bigInteger('product_unit_id')->unsigned();
            $table->bigInteger('product_brand_id')->unsigned()->nullable();
            $table->bigInteger('product_id')->unsigned();
            $table->string('barcode');
            $table->integer('qty');
            $table->float('price', 8,2);
            $table->float('vat_amount', 8,2);
            $table->float('sub_total', 8,2);
            $table->timestamps();
            $table->foreign('store_stock_return_id')->references('id')->on('store_stock_returns')->onDelete('cascade');
            $table->foreign('product_unit_id')->references('id')->on('product_units')->onDelete('cascade');
            $table->foreign('product_brand_id')->references('id')->on('product_brands')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('store_stock_return_details');
    }
}
