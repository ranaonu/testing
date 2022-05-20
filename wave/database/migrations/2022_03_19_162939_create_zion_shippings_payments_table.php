<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateZionShippingsPaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('zion_shippings_payments', function (Blueprint $table) {
            $table->id();
            $table->longText('invoice')->nullable();
            $table->string('shipping')->nullable();
            $table->string('customs')->nullable();
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
        Schema::dropIfExists('zion_shippings_payments');
    }
}
