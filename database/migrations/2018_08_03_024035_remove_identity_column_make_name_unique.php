<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveIdentityColumnMakeNameUnique extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('candidates', function (Blueprint $table) {
            $table->dropForeign('candidates_election_id_foreign');
        });

        Schema::table('elections', function (Blueprint $table) {
            $table->dropColumn('id');
            $table->primary(['name', 'data_source_id'], 'name_data_source_id_pk');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('elections', function (Blueprint $table) {
            $table->dropPrimary('name_data_source_id_pk');
        });

        Schema::table('elections', function (Blueprint $table) {
            $table->increments('id');
        });

        Schema::table('candidates', function (Blueprint $table) {
            $table->foreign('election_id')->references('id')->on('elections');
        });
    }
}
