<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductSalePreviousDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_sale_previous_details', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('pro_sale_ex_id')->unsigned();
            $table->bigInteger('pro_sale_detail_id')->unsigned();
            $table->bigInteger('product_unit_id')->unsigned();
            $table->bigInteger('product_brand_id')->unsigned()->nullable();
            $table->bigInteger('product_id')->unsigned();
            $table->string('barcode');
            $table->integer('qty');
            $table->float('price', 8,2);
            $table->float('vat_amount', 8,2);
            $table->float('sub_total', 8,2);
            $table->string('sale_exchange_date');
            $table->timestamps();
            $table->foreign('pro_sale_ex_id')->references('id')->on('product_sale_exchanges')->onDelete('cascade');
            $table->foreign('pro_sale_detail_id')->references('id')->on('product_sale_details')->onDelete('cascade');
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
        Schema::dropIfExists('product_sale_previous_details');
    }
}
