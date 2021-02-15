<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmployeeOfficeInformationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('employee_office_informations', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('employee_id')->unsigned();
            $table->enum('employee_type', ['Permanent','Provisional']);
            $table->string('card_no')->unique();
            $table->bigInteger('department_id')->unsigned();
            $table->bigInteger('designation_id')->unsigned();
            $table->string('joining_date')->nullable();
            $table->string('resignation_date')->nullable();
            $table->string('last_office_date')->nullable();
            $table->integer('status')->default(1);
            $table->timestamps();
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
            $table->foreign('department_id')->references('id')->on('departments')->onDelete('cascade');
            $table->foreign('designation_id')->references('id')->on('designations')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('employee_office_informations');
    }
}
