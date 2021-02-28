<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePayrollsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payrolls', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('year');
            $table->string('month');
            $table->bigInteger('department_id')->unsigned();
            $table->bigInteger('designation_id')->unsigned();
            $table->bigInteger('employee_id')->unsigned();
            $table->string('card_no')->unique();
            $table->string('employee_name');
            $table->string('joining_date')->nullable();
            $table->float('basic', 8,2)->default(0);
            $table->float('basic', 8,2)->default(0);
            $table->float('house_rent', 8,2)->default(0);
            $table->float('medical', 8,2)->default(0);
            $table->float('conveyance', 8,2)->default(0);
            $table->float('special', 8,2)->default(0);
            $table->float('mobile_bill', 8,2)->default(0);
            $table->float('kpi', 8,2)->default(0);
            $table->float('other_allowance', 8,2)->default(0);
            $table->float('payable_gross_salary', 8,2)->default(0);
            $table->float('pf_employee', 8,2)->default(0);
            $table->float('pf_employeer', 8,2)->default(0);
            $table->float('total_pf', 8,2)->default(0);
            $table->float('loan', 8,2)->default(0);
            $table->float('tax', 8,2)->default(0);
            $table->float('mobile_bill_deduction', 8,2)->default(0);
            $table->float('other_deduction', 8,2)->default(0);
            $table->float('total_deduction_amount', 8,2)->default(0);
            $table->integer('total_working_day')->nullable();
            $table->integer('late')->nullable();
            $table->integer('absent')->nullable();
            $table->float('absent_deduction', 8,2)->default(0);
            $table->float('advance', 8,2)->default(0);
            $table->float('over_time', 8,2)->default(0);
            $table->float('net_salary', 8,2)->default(0);
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
        Schema::dropIfExists('payrolls');
    }
}
