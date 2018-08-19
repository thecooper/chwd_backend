<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TableCandidatesAddGeographicalFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('candidates', function(Blueprint $table) {
            $table->string('district_type');
            $table->string('district');
            $table->string('district_number');
            $table->string('office_level');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('candidates', function(Blueprint $table) {
            $table->dropColumn([
                'district_type',
                'district',
                'district_number',
                'office_level'
            ]);
        });
    }
}
