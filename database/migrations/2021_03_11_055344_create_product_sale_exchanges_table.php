<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductSaleExchangesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_sale_exchanges', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('invoice_no');
            $table->string('sale_invoice_no');
            $table->bigInteger('user_id');
            $table->bigInteger('party_id')->unsigned();
            $table->bigInteger('warehouse_id')->unsigned()->nullable();
            $table->bigInteger('store_id')->unsigned();
            $table->enum('sale_exchange_type', ['whole_sale','pos_sale']);
            $table->enum('discount_type', ['Flat','Percentage'])->nullable();
            $table->string('discount_amount')->nullable();
            $table->text('miscellaneous_comment')->nullable();
            $table->float('miscellaneous_charge', 8,2);
            $table->float('total_vat_amount', 8,2);
            $table->float('total_amount', 8,2);
            $table->float('previous_paid_amount', 8,2);
            $table->float('paid_amount', 8,2)->nullable();
            $table->float('back_amount', 8,2)->nullable();
            $table->float('due_amount', 8,2);
            $table->string('sale_exchange_date');
            $table->string('sale_exchange_date_time');
            $table->timestamps();
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
        Schema::dropIfExists('product_sale_exchanges');
    }
}
