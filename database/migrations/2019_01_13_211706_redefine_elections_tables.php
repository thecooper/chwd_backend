<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RedefineElectionsTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      if(Schema::hasTable('elections') && !Schema::hasTable('election_fragments')) {
        Schema::rename('elections', 'election_fragments');
      }

      if(Schema::hasTable('consolidated_elections')) {
        Schema::rename('consolidated_elections', 'elections');
      }
      
      if(Schema::hasTable('election_fragments')) {
        Schema::table('election_fragments', function (Blueprint $table) {
          if(!Schema::hasColumn('election_fragments', 'id')) {
            $table->increments('id');
          }
        });

        Schema::table('election_fragments', function (Blueprint $table) {
          if(Schema::hasColumn('election_fragments', 'consolidated_election_id')) {
            $table->renameColumn('consolidated_election_id', 'election_id');
          }
        });

        Schema::table('election_fragments', function (Blueprint $table) {
          if(Schema::hasColumn('election_fragments', 'election_id')) {
            $table->unsignedInteger('election_id')
              ->nullable()
              ->change();
          }
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
      //   $table->dropForeign('candidates_election_id_foreign');
      // });

      Schema::dropIfExists('consolidated_elections');
      
      if(Schema::hasTable('election_fragments')) {
        Schema::table('election_fragments', function (Blueprint $table) {
          if(Schema::hasColumn('election_fragments', 'election_id')) {
            $table->integer('election_id')->unsigned()->after('data_source_id')->change();
            $table->renameColumn('election_id', 'consolidated_election_id');
          }

          if(Schema::hasColumn('election_fragments', 'id')) {
            $table->dropColumn('id');
          }
        });
      }
      
      if(Schema::hasTable('elections')) {
        Schema::rename('elections', 'consolidated_elections');
      }

      if(Schema::hasTable('election_fragments')) {
        Schema::rename('election_fragments', 'elections');
      }

      // Schema::table('candidates', function(Blueprint $table) {
      //   $table->foreign('election_id')->references('id')->on('consolidated_elections');
      // });
    }
}
