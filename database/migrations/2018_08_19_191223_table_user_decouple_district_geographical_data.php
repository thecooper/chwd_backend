<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TableUserDecoupleDistrictGeographicalData extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function(Blueprint $table) {
            $table->dropColumn([
                'address_line_1',
                'address_line_2',
                'city',
                'zip',
                'state_abbreviation',
                'congressional_district',
                'state_legislative_district',
                'state_house_district'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function(Blueprint $table) {
            $table->string("address_line_1");
            $table->string("address_line_2");
            $table->string("city");
            $table->string("zip");
            $table->string("state_abbreviation", 2);
            $table->integer('congressional_district');
            $table->integer('state_legislative_district');
            $table->integer('state_house_district');
        });
    }
}
