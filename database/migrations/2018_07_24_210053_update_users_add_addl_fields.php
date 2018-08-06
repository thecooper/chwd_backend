<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateUsersAddAddlFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->integer('congressional_district');
            $table->integer('state_legislative_district');
            $table->integer('state_house_district');
            $table->string('polling_location_address_1');
            $table->string('polling_location_address_2');
            $table->string('polling_location_city');
            $table->string('polling_location_state');
            $table->string('polling_location_zip');
            $table->time('polling_location_time_open');
            $table->time('polling_location_time_closed');
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
            $table->dropColumn([
                'congressional_district',
                'state_legislative_district',
                'state_house_district',
                'polling_location_address_1',
                'polling_location_address_2',
                'polling_location_city',
                'polling_location_state',
                'polling_location_zip',
                'polling_location_time_open',
                'polling_location_time_closed',
            ]);
        });
    }
}
