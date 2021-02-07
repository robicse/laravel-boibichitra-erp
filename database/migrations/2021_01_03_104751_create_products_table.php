<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->bigInteger('product_unit_id')->unsigned();
            $table->string('item_code')->nullable();
            $table->string('barcode')->unique();
            $table->string('self_no')->nullable();
            $table->integer('low_inventory_alert')->nullable();
            $table->bigInteger('product_brand_id')->unsigned()->nullable();
            $table->float('purchase_price',8,2);
            $table->float('whole_sale_price',8,2);
            $table->float('selling_price',8,2);
            $table->integer('vat_status')->default(0);
            $table->integer('vat_percentage')->default(0);
            $table->float('vat_whole_amount',8,2)->default(0);
            $table->float('vat_amount',8,2)->default(0);
            $table->text('note')->nullable();
            $table->string('date');
            $table->integer('status')->default(1);
            $table->string('image')->default('default.jpg');
            $table->timestamps();
            $table->foreign('product_brand_id')->references('id')->on('product_brands')->onDelete('cascade');
            $table->foreign('product_unit_id')->references('id')->on('product_units')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('products');
    }
}
