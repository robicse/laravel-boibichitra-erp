<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStockTransferRequestDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stock_transfer_request_details', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('stock_transfer_request_id')->unsigned();
            $table->bigInteger('product_unit_id')->unsigned()->nullable();
            $table->bigInteger('product_brand_id')->unsigned()->nullable();
            $table->bigInteger('product_id')->unsigned()->nullable();
            $table->string('barcode')->nullable();
            $table->integer('request_qty')->nullable();
            $table->integer('send_qty')->nullable();
            $table->integer('received_qty')->nullable();
            $table->float('price', 8,2);
            $table->float('vat_amount', 8,2);
            $table->float('sub_total', 8,2);
            $table->string('received_date')->nullable();
            $table->timestamps();
            $table->foreign('stock_transfer_request_id')->references('id')->on('stock_transfer_requests')->onDelete('cascade');
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
        Schema::dropIfExists('stock_transfer_request_details');
    }
}
