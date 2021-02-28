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
            $table->string('employee_card_no');
            $table->string('employee_name');
            $table->string('date');
            $table->string('year');
            $table->string('month');
            $table->string('day');
            $table->string('on_duty');
            $table->string('off_duty');
            $table->string('clock_in');
            $table->string('clock_out');
            $table->string('late')->nullable();
            $table->string('early')->nullable();
            $table->string('absent')->nullable();
            $table->string('work_time')->nullable();
            $table->string('att_time')->nullable();
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
