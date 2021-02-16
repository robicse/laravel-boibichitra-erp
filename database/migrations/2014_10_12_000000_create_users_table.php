<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('id');
            //$table->string('user_type');
            $table->string('name');
            $table->string('slug')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->unique();
            $table->string('password')->nullable();
            $table->integer('status')->default(0);
            $table->bigInteger('warehouse_id')->nullable();
            $table->bigInteger('store_id')->nullable();
            $table->bigInteger('party_id')->nullable();
            $table->bigInteger('employee_id')->nullable();
            $table->rememberToken();
            $table->timestamps();
            //$table->foreign('warehouses')->references('id')->on('warehouse_id')->onDelete('cascade');
            //$table->foreign('stores')->references('id')->on('store_id')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
