<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TableElectionsRemoveGeographicalFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('elections', function(Blueprint $table) {
            $table->dropColumn(['county', 'district']);
            $table->string('state_abbreviation', 2)->nullable()->change();
        });

        Schema::table('consolidated_elections', function(Blueprint $table) {
            $table->dropColumn(['county', 'district']);
            $table->string('state_abbreviation', 2)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('elections', function(Blueprint $table) {
            $table->string('county')->nullable();
            $table->string('district')->nullable();
        });

        Schema::table('consolidated_elections', function(Blueprint $table) {
            $table->string('county')->nullable();
            $table->string('district')->nullable();
        });
    }
}
