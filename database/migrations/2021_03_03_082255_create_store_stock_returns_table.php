<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStoreStockReturnsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('store_stock_returns', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('invoice_no');
            $table->bigInteger('return_by_user_id');
            $table->bigInteger('return_from_store_id')->unsigned()->nullable();
            $table->bigInteger('return_to_warehouse_id')->unsigned()->nullable();
            $table->text('return_remarks')->nullable();
            $table->string('return_date');
            $table->string('return_date_time');
            $table->integer('status')->default(1);
            $table->timestamps();
            $table->foreign('return_from_store_id')->references('id')->on('stores')->onDelete('cascade');
            $table->foreign('return_to_warehouse_id')->references('id')->on('warehouses')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('store_stock_returns');
    }
}
