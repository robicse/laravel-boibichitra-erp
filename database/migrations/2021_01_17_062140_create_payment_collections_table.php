<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentCollectionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payment_collections', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('invoice_no');
            $table->bigInteger('product_sale_id')->unsigned();
            $table->bigInteger('product_sale_return_id')->unsigned()->nullable();
            $table->bigInteger('user_id');
            $table->bigInteger('party_id')->unsigned();
            $table->bigInteger('warehouse_id')->unsigned()->nullable();
            $table->bigInteger('store_id')->unsigned()->nullable();
            $table->enum('collection_type',['Sale','Return Cash','Return Balance']);
            $table->float('collection_amount', 8,2);
            $table->float('due_amount', 8,2);
            $table->float('current_collection_amount', 8,2);
            $table->string('collection_date');
            $table->string('collection_date_time');
            $table->timestamps();
            $table->foreign('product_sale_id')->references('id')->on('product_sales')->onDelete('cascade');
            $table->foreign('party_id')->references('id')->on('parties')->onDelete('cascade');
            $table->foreign('warehouse_id')->references('id')->on('warehouses')->onDelete('cascade');
            $table->foreign('store_id')->references('id')->on('stores')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payment_collections');
    }
}
