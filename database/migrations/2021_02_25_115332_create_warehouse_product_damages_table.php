<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWarehouseProductDamagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('warehouse_product_damages', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('invoice_no');
            $table->bigInteger('user_id');
            $table->bigInteger('warehouse_id')->unsigned()->nullable();
            $table->bigInteger('product_unit_id')->unsigned();
            $table->bigInteger('product_brand_id')->unsigned()->nullable();
            $table->bigInteger('product_id')->unsigned();
            $table->string('barcode');
            $table->integer('qty');
            $table->string('damage_date');
            $table->string('damage_date_time');
            $table->integer('status')->default(1);
            $table->timestamps();
            $table->foreign('warehouse_id')->references('id')->on('warehouses')->onDelete('cascade');
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
        Schema::dropIfExists('warehouse_product_damages');
    }
}
