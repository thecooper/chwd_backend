<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateConsolidatedCandidatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('consolidated_candidates', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->nullable();
            $table->string('election_id')->nullable();
            $table->string('party_affiliation')->nullable();
            $table->string('website_url')->nullable();
            $table->string('donate_url')->nullable();
            $table->string('facebook_profile')->nullable();
            $table->string('twitter_handle')->nullable();
            $table->string('election_status')->nullable();
            $table->string('election_office')->nullable();
            $table->string('is_incumbent')->nullable();
            $table->string('data_source_id');
            $table->string('district_type')->nullable();
            $table->string('district')->nullable();
            $table->string('district_number')->nullable();
            $table->string('office_level')->nullable();
            $table->timestamps();
            // $table->foreign('election_id')->references('id')->on('consolidated_elections');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('consolidated_candidates');
    }
}
