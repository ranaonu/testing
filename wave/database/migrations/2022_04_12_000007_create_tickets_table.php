<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTicketsTable extends Migration
{
    public function up()
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->increments('id');

            $table->string('title');

            $table->longText('content')->nullable();

            $table->string('author_name')->nullable();

            $table->string('author_email')->nullable();
            $table->integer('status_id')->nullable();
            $table->integer('category_id')->nullable();
            $table->string('priority_id')->nullable();
            $table->string('assigned_to_user_id')->nullable();

            $table->timestamps();

            $table->softDeletes();
        });
    }
}
