<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TableElectionsAddIdColumnBack extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(!Schema::hasColumn('consolidated_elections', 'id')) {
            Schema::table('consolidated_elections', function(Blueprint $table) {
                $table->increments('id')->first();
            });
        }
        
        if(!Schema::hasColumn('elections', 'consolidated_election_id')) {
            Schema::table('elections', function(Blueprint $table) {
                $table->integer('consolidated_election_id')->unsigned()->first();

                $table->foreign('consolidated_election_id')->references('id')->on('consolidated_elections')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if(Schema::hasColumn('elections', 'consolidated_election_id')) {
            Schema::table('elections', function(Blueprint $table) {
                $table->dropColumn('consolidated_election_id');

                // $table->dropForeign(['consolidated_election_id']);
            });
        }

        if(Schema::hasColumn('consolidated_elections', 'id')) {
            Schema::table('consolidated_elections', function(Blueprint $table) {
                $table->dropColumn('id');
            });
        }
    }
}
