<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductSaleReturnBalancesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_sale_return_balances', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('pro_sale_id')->unsigned();
            $table->bigInteger('pro_sale_return_id')->unsigned()->nullable();
            $table->enum('in_out_balance', ['in_balance','out_balance']);
            $table->float('previous_balance', 8,2);
            $table->float('in_balance', 8,2);
            $table->float('out_balance', 8,2);
            $table->float('current_balance', 8,2);
            $table->timestamps();
            $table->foreign('pro_sale_id')->references('id')->on('product_sales')->onDelete('cascade');
            $table->foreign('pro_sale_return_id')->references('id')->on('product_sale_returns')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('product_sale_return_balances');
    }
}
