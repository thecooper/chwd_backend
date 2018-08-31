<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TableCandidatesRenameIdCol extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('candidates', function(Blueprint $table) {
            $table->renameColumn('id', 'consolidated_candidate_id');
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
            $table->renameColumn('consolidated_candidate_id', 'id');
        });
    }
}
