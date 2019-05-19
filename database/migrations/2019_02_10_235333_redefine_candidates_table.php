<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RedefineCandidatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      if(Schema::hasTable('candidates') && !Schema::hasTable('candidate_fragments')) {
        Schema::rename('candidates', 'candidate_fragments');
      }

      if(Schema::hasTable('consolidated_candidates')) {
        Schema::rename('consolidated_candidates', 'candidates');
      }
      
      if(Schema::hasTable('candidate_fragments')) {
        Schema::table('candidate_fragments', function (Blueprint $table) {
          if(!Schema::hasColumn('candidate_fragments', 'id')) {
            $table->increments('id');
          }
        });

        Schema::table('candidate_fragments', function (Blueprint $table) {
          if(Schema::hasColumn('candidate_fragments', 'consolidated_candidate_id')) {
            $table->renameColumn('consolidated_candidate_id', 'candidate_id');
          }
        });

        Schema::table('candidate_fragments', function (Blueprint $table) {
          if(Schema::hasColumn('candidate_fragments', 'candidate_id')) {
            $table->unsignedInteger('candidate_id')
              ->nullable()
              ->change();
          }

          Schema::table('candidate_fragments', function(Blueprint $table) {
            $table->index('name');
            $table->index(['candidate_id', 'data_source_id']);
          });
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
      // Schema::table('candidates', function(Blueprint $table) {
      //   $table->dropForeign('candidates_candidate_id_foreign');
      // });

      Schema::dropIfExists('consolidated_candidates');
      
      if(Schema::hasTable('candidate_fragments')) {
        Schema::table('candidate_fragments', function (Blueprint $table) {
          if(Schema::hasColumn('candidate_fragments', 'candidate_id')) {
            $table->integer('candidate_id')->unsigned()->after('data_source_id')->change();
            $table->renameColumn('candidate_id', 'consolidated_candidate_id');
          }

          if(Schema::hasColumn('candidate_fragments', 'id')) {
            $table->dropColumn('id');
          }

          Schema::table('candidate_fragments', function(Blurprint $table) {
            $table->dropIndex('candidate_fragments_name_index');
            $table->dropIndex('candidate_fragments_data_source_id_index');
          });
        });
      }
      
      if(Schema::hasTable('candidates')) {
        Schema::rename('candidates', 'consolidated_candidates');
      }

      if(Schema::hasTable('candidate_fragments')) {
        Schema::rename('candidate_fragments', 'candidates');
      }

      // Schema::table('candidates', function(Blueprint $table) {
      //   $table->foreign('candidate_id')->references('id')->on('consolidated_candidates');
      // });
    }
}
