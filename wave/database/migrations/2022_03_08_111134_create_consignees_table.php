<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateConsigneesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('consignees', function (Blueprint $table) {
            $table->increments('consignee_id');
            $table->integer('user_id');
            $table->string('consignee_name')->nullable();
            $table->string('consignee_phone')->nullable();
            $table->string('consignee_homephone')->nullable();
            $table->string('consignee_address_country')->nullable();
            $table->string('consignee_address_city')->nullable();
            $table->string('consignee_address_state')->nullable();
            $table->string('consignee_address_zip')->nullable();
            $table->string('consignee_address')->nullable();
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
        Schema::dropIfExists('consignees');
    }
}
