<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCandidateIndexes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('candidates', function (Blueprint $table) {
          $table->unique(['name', 'district'], 'candidate_name_district');
        });

        Schema::table('candidate_fragments', function (Blueprint $table) {
          $table->unique(['name', 'district'], 'candidate_fragments_name_district');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('candidates', function (Blueprint $table) {
          $table->dropUnique('candidate_name_district');
        });

        Schema::table('candidate_fragments', function (Blueprint $table) {
          $table->dropUnique('candidate_fragments_name_district');
        });
    }
}
