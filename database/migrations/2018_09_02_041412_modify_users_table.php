<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('polling_location_address_1')->nullable();
            $table->string('polling_location_address_2')->nullable();
            $table->string('polling_location_city')->nullable();
            $table->string('polling_location_state')->nullable();
            $table->string('polling_location_zip')->nullable();
            $table->time('polling_location_time_open')->nullable();
            $table->time('polling_location_time_closed')->nullable();
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
