<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TableCandidatesDropColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(Schema::hasColumn('candidates', 'gender')) {
            Schema::table('candidates', function(Blueprint $table) {
                $table->dropColumn('gender');
            });
        }

        if(Schema::hasColumn('candidates', 'birthdate')) {
            Schema::table('candidates', function(Blueprint $table) {
                $table->dropColumn('birthdate');
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
        // Do nothing
    }
}
