<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mpu_pays', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaction_id')->constrained('transactions');
            $table->string('pan');
            $table->string('amount')->nullable();
            $table->string('invoiceNo')->nullable();
            $table->string('tranRef')->nullable();
            $table->string('approvalCode')->nullable();
            $table->string('dateTime')->nullable();
            $table->string('status')->nullable();
            $table->string('failReason')->nullable();
            $table->string('userDefined1')->nullable();
            $table->string('userDefined2')->nullable();
            $table->string('userDefined3')->nullable();
            $table->string('categoryCode')->nullable();
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
        Schema::dropIfExists('mpu_pays');
    }
};