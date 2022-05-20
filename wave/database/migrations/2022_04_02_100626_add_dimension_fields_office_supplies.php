<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDimensionFieldsOfficeSupplies extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('office_supplies', function (Blueprint $table) {
            $table->tinyInteger('status')->default(1);
            $table->float('height')->nullable();
            $table->float('width')->nullable();
            $table->float('length')->nullable();
            $table->float('weight')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('office_supplies', function (Blueprint $table) {
            //
        });
    }
}
