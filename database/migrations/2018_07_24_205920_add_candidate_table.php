<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCandidateTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('candidates', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 100);
            $table->unsignedInteger('election_id');
            $table->string('party_affiliation', 100);
            $table->string('website_url');
            $table->string('donate_url');
            $table->string('facebook_profile');
            $table->string('twitter_handle');
            $table->string('election_status');
            $table->string('gender', 1);
            $table->date('birthdate');
            $table->string('election_office');
            $table->boolean('is_incumbent');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('candidates');
    }
}
