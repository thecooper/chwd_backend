<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeys extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('candidates', function (Blueprint $table) {
            $table->foreign('election_id')->references('id')->on('elections');
        });

        Schema::table('news', function (Blueprint $table) {
            $table->foreign('candidate_id')->references('id')->on('candidates');
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
            $table->dropForeign('candidates_election_id_foreign');
        });

        Schema::table('news', function (Blueprint $table) {
            $table->dropForeign('news_candidate_id_foreign');
        });
    }
}
