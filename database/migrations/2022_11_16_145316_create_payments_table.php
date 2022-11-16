<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->enum('payment_type', ['first', 'second']);
            $table->integer('merchant_id')->nullable();
            $table->integer('payment_id')->nullable();
            $table->string('status')->nullable();
            $table->integer('amount')->nullable();
            $table->integer('amount_paid')->nullable();
            $table->integer('project')->nullable();
            $table->integer('invoice')->nullable();
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
        Schema::dropIfExists('payments');
    }
}
