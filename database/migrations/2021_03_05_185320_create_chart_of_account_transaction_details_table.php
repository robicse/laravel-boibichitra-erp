<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateChartOfAccountTransactionDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('chart_of_account_transaction_details', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('store_id')->nullable();
            $table->bigInteger('chart_of_account_transaction_id')->unsigned();
            $table->bigInteger('chart_of_account_id')->unsigned();
            $table->string('chart_of_account_number');
            $table->string('chart_of_account_name');
            $table->string('chart_of_account_parent_name');
            $table->string('chart_of_account_type');
            $table->float('debit', 8,2);
            $table->float('credit', 8,2);
            $table->text('description')->nullable();
            $table->string('year');
            $table->string('month');
            $table->string('transaction_date');
            $table->string('transaction_date_time');
            $table->timestamps();
            //$table->foreign('chart_of_account_transaction_id')->references('id')->on('chart_of_account_transactions')->onDelete('cascade');
            $table->foreign('chart_of_account_id')->references('id')->on('chart_of_accounts')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('chart_of_account_transaction_details');
    }
}
