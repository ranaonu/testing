<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddShipperPhoneToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('shipper_phone')->nullable();
            $table->string('shipper_country')->nullable();
            $table->string('shipper_address')->nullable();
            $table->string('shipper_zip')->nullable();
            $table->string('shipper_city')->nullable();
            $table->string('shipper_state')->nullable();
            $table->string('consignee_name')->nullable();
            $table->string('consignee_phone')->nullable();
            $table->string('consignee_country')->nullable();
            $table->string('consignee_address')->nullable();
            $table->string('consignee_zip')->nullable();
            $table->string('consignee_city')->nullable();
            $table->string('consignee_state')->nullable();
            $table->string('isreceiver_diff')->nullable();
            $table->string('whatsapp_prefer')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('shipper_phone');
            $table->dropColumn('shipper_country');
            $table->dropColumn('shipper_address');
            $table->dropColumn('shipper_zip');
            $table->dropColumn('shipper_city');
            $table->dropColumn('shipper_state');
            $table->dropColumn('consignee_name');
            $table->dropColumn('consignee_phone');
            $table->dropColumn('consignee_country');
            $table->dropColumn('consignee_address');
            $table->dropColumn('consignee_zip');
            $table->dropColumn('consignee_city');
            $table->dropColumn('consignee_state');
            $table->dropColumn('isreceiver_diff');
            $table->dropColumn('whatsapp_prefer');
        });
    }
}
