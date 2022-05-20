<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCountriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('countries', function (Blueprint $table) {
            $table->id();
            $table->string('country_statut')->default('on')->nullable();
            $table->string('country_name')->nullable();
            $table->string('alpha_2_code')->nullable();
            $table->string('alpha_3_code')->nullable();
            $table->string('numeric_code')->nullable();
            $table->string('dial_code')->nullable();
            $table->string('country_currency_symb')->nullable();
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
        Schema::dropIfExists('countries');
    }
}
