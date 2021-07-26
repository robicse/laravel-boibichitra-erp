<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateChartOfAccountTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('chart_of_account_transactions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('user_id');
            $table->integer('store_id')->nullable();
            $table->bigInteger('voucher_type_id')->unsigned();
            $table->string('voucher_no');
            $table->enum('is_approved',['pending','canceled','approved'])->default('approved');
            $table->string('transaction_date');
            $table->string('transaction_date_time');
            $table->timestamps();
            $table->foreign('voucher_type_id')->references('id')->on('voucher_types')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('chart_of_account_transactions');
    }
}
