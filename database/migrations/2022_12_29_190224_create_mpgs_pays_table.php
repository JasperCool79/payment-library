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
        Schema::create('mpgs_pays', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaction_id')->constrained('transactions');
            $table->string('funding_method');
            $table->string('customer_note');
            $table->text('description')->nullable();
            $table->string('issuer');
            $table->string('name_on_card');
            $table->string('browser');
            $table->string('card_type')->string('brand, scheme');
            $table->string('pan');
            $table->string('ip_address');
            $table->string('total_amount');
            $table->string('currency');
            $table->string('status');
            $table->datetime('creation_time');
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
        Schema::dropIfExists('mpgs_pays');
    }
};