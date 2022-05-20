<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddShipperEmailShippingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('shippings', function (Blueprint $table) {
            //
            $table->string('shipper_email')->nullable()->after('response');
            $table->string('shipper_phone')->nullable()->after('shipper_email');
            $table->string('consignee_phone')->nullable()->after('shipper_phone');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('shippings', function (Blueprint $table) {
            //
            $table->dropColumn('shipper_email');
            $table->dropColumn('shipper_phone');
            $table->dropColumn('consignee_phone');
        });
    }
}
