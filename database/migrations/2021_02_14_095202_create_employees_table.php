<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmployeesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone');
            $table->enum('gender', ['Male','Female'])->nullable();
            $table->string('date_of_birth')->nullable();
            $table->string('blood_group')->nullable();
            $table->string('national_id')->nullable();
            $table->enum('marital_status', ['Married','Unmarried'])->nullable();
            $table->text('present_address')->nullable();
            $table->text('permanent_address')->nullable();
            $table->string('image')->nullable();
            $table->integer('status')->default(1);
            $table->integer('warehouse_id');
            $table->integer('store_id')->nullable();
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
        Schema::dropIfExists('employees');
    }
}
