<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TableUsersMakeColumnsNullable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('polling_location_address_1')->nullable()->change();
            $table->string('polling_location_address_2')->nullable()->change();
            $table->string('polling_location_city')->nullable()->change();
            $table->string('polling_location_state')->nullable()->change();
            $table->string('polling_location_zip')->nullable()->change();
            $table->time('polling_location_time_open')->nullable()->change();
            $table->time('polling_location_time_closed')->nullable()->change();
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
            $table->string('polling_location_address_1')->nullable(false)->change();
            $table->string('polling_location_address_2')->nullable(false)->change();
            $table->string('polling_location_city')->nullable(false)->change();
            $table->string('polling_location_state')->nullable(false)->change();
            $table->string('polling_location_zip')->nullable(false)->change();
            $table->time('polling_location_time_open')->nullable(false)->change();
            $table->time('polling_location_time_closed')->nullable(false)->change();
        });
    }
}
