<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class NewTableUserBallots extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_ballots', function(Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned();
            $table->string("address_line_1")->nullable();
            $table->string("address_line_2")->nullable();
            $table->string("city");
            $table->string("zip");
            $table->string("state_abbreviation", 2);
            $table->integer('congressional_district')->nullable();
            $table->integer('state_legislative_district')->nullable();
            $table->integer('state_house_district')->nullable();

            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_ballots');
    }
}
