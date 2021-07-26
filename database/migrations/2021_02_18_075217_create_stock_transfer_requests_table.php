<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStockTransferRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stock_transfer_requests', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('invoice_no');
            $table->bigInteger('request_from_warehouse_id')->unsigned()->nullable();
            $table->bigInteger('request_from_store_id')->unsigned()->nullable();
            $table->bigInteger('request_to_warehouse_id')->unsigned()->nullable();
            $table->bigInteger('request_to_store_id')->unsigned()->nullable();
            $table->bigInteger('request_by_user_id');
            $table->string('request_date');
            $table->text('request_remarks')->nullable();
            $table->enum('request_status',['Pending','On Review','Canceled','Approved'])->default('Pending')->nullable();
            $table->bigInteger('view_by_user_id')->nullable();
            $table->enum('view_status',['Seen','Unseen'])->default('Unseen')->nullable();
            $table->bigInteger('send_by_user_id')->nullable();
            $table->string('send_date')->nullable();
            $table->text('send_remarks')->nullable();
            $table->enum('send_status',['Pending','On Review','On Processing','Canceled','Delivered','Completed'])->default('Pending')->nullable();
            $table->bigInteger('received_by_user_id')->nullable();
            $table->string('received_date')->nullable();
            $table->text('received_remarks')->nullable();
            $table->enum('received_status',['Pending','Canceled','Received'])->nullable();
            $table->timestamps();
            $table->foreign('request_from_warehouse_id')->references('id')->on('warehouses')->onDelete('cascade');
            $table->foreign('request_to_warehouse_id')->references('id')->on('warehouses')->onDelete('cascade');
            $table->foreign('request_from_store_id')->references('id')->on('stores')->onDelete('cascade');
            $table->foreign('request_to_store_id')->references('id')->on('stores')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('stock_transfer_requests');
    }
}
