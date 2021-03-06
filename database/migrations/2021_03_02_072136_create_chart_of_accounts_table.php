<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateChartOfAccountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('chart_of_accounts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('head_code');
            $table->string('head_name')->unique();
            $table->string('parent_head_name');
            $table->string('head_type')->nullable();
            $table->integer('head_level')->nullable();
            $table->string('is_active')->nullable();
            $table->string('is_transaction')->nullable();
            $table->string('is_general_ledger')->nullable();
            $table->string('ref_id')->nullable();
            $table->string('user_bank_account_no')->nullable();
            $table->string('created_by')->nullable();
            $table->string('updated_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('chart_of_accounts');
    }
}
