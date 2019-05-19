<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddBallotpediaLinkingTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ballotpedia_candidates', function(Blueprint $table) {
          $table->integer('ballotpedia_candidate_id');
          $table->integer('candidate_id');
          $table->timestamps();

          $table->index(['ballotpedia_candidate_id', 'candidate_id'], 'ballotpedia_lookup_index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ballotpedia_candidates');
    }
}
