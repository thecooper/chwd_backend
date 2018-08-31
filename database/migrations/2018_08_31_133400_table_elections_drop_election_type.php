<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TableElectionsDropElectionType extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(Schema::hasColumn('elections', 'election_type')) {
            Schema::table('elections', function(Blueprint $table) {
                $table->dropColumn('election_type');
            });
        }

        if(Schema::hasColumn('consolidated_elections', 'election_type')) {
            Schema::table('consolidated_elections', function(Blueprint $table) {
                $table->dropColumn('election_type');
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
        //
    }
}
