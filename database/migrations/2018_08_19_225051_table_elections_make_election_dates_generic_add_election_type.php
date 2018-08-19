<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TableElectionsMakeElectionDatesGenericAddElectionType extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('elections', function(Blueprint $table) {
            $table->dropColumn('primary_date');
            $table->string('election_type');
            $table->renameColumn('general_date', 'election_date');
        });

        Schema::table('consolidated_elections', function(Blueprint $table) {
            $table->dropColumn('primary_date');
            $table->string('election_type');
            $table->renameColumn('general_date', 'election_date');
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
            $table->date('primary_date');
            $table->dropColumn('election_type');
            $table->renameColumn('election_date', 'general_date');
        });

        Schema::table('consolidated_elections', function(Blueprint $table) {
            $table->date('primary_date');
            $table->dropColumn('election_type');
            $table->renameColumn('election_date', 'general_date');
        });
    }
}
