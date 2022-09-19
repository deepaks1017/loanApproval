<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRepaymentSchedulesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('repayment_schedules', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('loan_id');
            $table->foreign('loan_id')->references('id')->on('loans')->onUpdate('restrict')->onDelete('restrict');
            $table->float('repayment_amount',10,2);
            $table->datetime('repayment_date');
            $table->float('amount_received',10,2)->nullable();
            $table->float('pending_amount',10,2);
            $table->tinyInteger('status')->comment('0 is for pending, 1 is for approved and 2 is for paid')->default(0);
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
        Schema::dropIfExists('repayment_schedules');
    }
}
