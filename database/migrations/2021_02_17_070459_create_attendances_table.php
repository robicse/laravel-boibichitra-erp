<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAttendancesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('employee_id')->unsigned();
            $table->string('employee_card');
            $table->string('employee_name');
            $table->string('date');
            $table->string('year');
            $table->string('month');
            $table->string('work_in_time');
            $table->string('work_out_time');
            $table->string('in_time');
            $table->string('out_time');
            $table->enum('late_status',['Late','In Time']);
            $table->enum('present_status',['Present','Absent','Leave']);
            $table->text('note')->nullable();
            $table->timestamps();
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('attendances');
    }
}
