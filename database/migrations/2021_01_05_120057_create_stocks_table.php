<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStocksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stocks', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('ref_id');
            $table->bigInteger('user_id');
            $table->bigInteger('warehouse_id')->unsigned()->nullable();
            $table->bigInteger('store_id')->unsigned()->nullable();
            $table->bigInteger('product_unit_id')->unsigned();
            $table->bigInteger('product_brand_id')->unsigned()->nullable();
            $table->bigInteger('product_id')->unsigned();
            $table->enum('stock_type', ['whole_purchase','pos_purchase','purchase_return','from_warehouse_to_store','from_store_to_store','whole_sale','pos_sale','sale_return']);
            $table->enum('stock_where', ['warehouse','store']);
            $table->enum('stock_in_out', ['stock_in','stock_out']);
            $table->string('previous_stock')->nullable();
            $table->string('stock_in')->nullable();
            $table->string('stock_out')->nullable();
            $table->string('current_stock');
            $table->string('stock_date');
            $table->string('stock_date_time');
            $table->timestamps();
            $table->foreign('warehouse_id')->references('id')->on('warehouses')->onDelete('cascade');
            $table->foreign('store_id')->references('id')->on('stores')->onDelete('cascade');
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
        Schema::dropIfExists('stocks');
    }
}
