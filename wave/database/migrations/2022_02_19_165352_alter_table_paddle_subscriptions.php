<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTablePaddleSubscriptions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('paddle_subscriptions', function (Blueprint $table) {
            $table->string('subscription_id')->change();
            $table->string('plan_id')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('paddle_subscriptions', function (Blueprint $table) {
            $table->int('subscription_id')->change();
            $table->int('plan_id')->change();
        });
    }
}
