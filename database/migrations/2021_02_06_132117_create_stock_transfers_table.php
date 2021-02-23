<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStockTransfersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stock_transfers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('invoice_no');
            $table->bigInteger('user_id');
            $table->bigInteger('warehouse_id')->unsigned()->nullable();
            $table->bigInteger('store_id')->unsigned();
            $table->text('miscellaneous_comment')->nullable();
            $table->float('miscellaneous_charge', 8,2);
            $table->float('total_vat_amount', 8,2);
            $table->float('total_amount', 8,2);
            $table->float('paid_amount', 8,2);
            $table->float('due_amount', 8,2);
            $table->string('issue_date');
            $table->string('due_date')->nullable();
            $table->timestamps();
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
        Schema::dropIfExists('stock_transfers');
    }
}
