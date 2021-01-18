<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentPaidsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payment_paids', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('invoice_no');
            $table->bigInteger('product_purchase_id')->unsigned();
            $table->bigInteger('product_purchase_return_id')->unsigned()->nullable();
            $table->bigInteger('user_id');
            $table->bigInteger('party_id')->unsigned();
            $table->bigInteger('warehouse_id')->unsigned()->nullable();
            $table->bigInteger('store_id')->unsigned()->nullable();
            $table->enum('paid_type', ['Purchase','Return']);
            $table->float('paid_amount', 8,2);
            $table->float('due_amount', 8,2);
            $table->float('current_paid_amount', 8,2);
            $table->string('paid_date');
            $table->string('paid_date_time');
            $table->timestamps();
            $table->foreign('product_purchase_id')->references('id')->on('product_purchases')->onDelete('cascade');
            $table->foreign('product_purchase_return_id')->references('id')->on('product_purchase_returns')->onDelete('cascade');
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
        Schema::dropIfExists('payment_paids');
    }
}
