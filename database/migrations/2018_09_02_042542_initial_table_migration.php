<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class InitialTableMigration extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(!Schema::hasTable('data_sources')) {
            Schema::create('data_sources', function (Blueprint $table) {
                $table->increments('id');
                $table->timestamps();
                $table->string('name');

                $table->index('name');
            });
        }
        
        if(!Schema::hasTable('data_source_priorities')) {
            Schema::create('data_source_priorities', function (Blueprint $table) {
                $table->string('destination_table');
                $table->unsignedInteger('data_source_id');
                $table->unsignedInteger('priority');
                $table->timestamps();

                $table->index(['destination_table']);

                $table->foreign('data_source_id')->references('id')->on('data_sources');
            });
        }

        if(!Schema::hasTable('consolidated_elections')) {
            Schema::create('consolidated_elections', function (Blueprint $table) {
                $table->increments('id');
                
                $table->string('name');
                $table->string('state_abbreviation', 2);
                $table->date('primary_election_date')->nullable();
                $table->date('general_election_date')->nullable();
                $table->date('runoff_election_date')->nullable();
                $table->timestamps();
            });
        }

        if(!Schema::hasTable('elections')) {
            Schema::create('elections', function (Blueprint $table) {
                $table->integer('consolidated_election_id')->unsigned();
                $table->string('name');
                $table->string('state_abbreviation', 2);
                $table->date('primary_election_date')->nullable();
                $table->date('general_election_date')->nullable();
                $table->date('runoff_election_date')->nullable();
                $table->integer('data_source_id')->unsigned();
                $table->timestamps();

                $table->foreign('data_source_id')->references('id')->on('data_sources');
            });
        }
        
        if(!Schema::hasTable('consolidated_candidates')) {
            Schema::create('consolidated_candidates', function (Blueprint $table) {
                $table->increments('id');
                
                $table->string('name', 100);
                $table->integer('election_id')->unsigned();
                $table->string('party_affiliation');
                $table->string('election_status');
                $table->string('office');
                $table->string('office_level');
                $table->boolean('is_incumbent');
                $table->string('district_type');
                $table->string('district');
                $table->string('district_identifier', 4)->nullable();
                $table->string('ballotpedia_url')->nullable();
                $table->string('website_url')->nullable();
                $table->string('donate_url')->nullable();
                $table->string('facebook_profile', 200)->nullable();
                $table->string('twitter_handle')->nullable();
                $table->timestamps();
            });
        }

        if(!Schema::hasTable('candidates')) {
            Schema::create('candidates', function (Blueprint $table) {
                $table->integer('consolidated_candidate_id')->unsigned();
                
                $table->string('name', 100);
                $table->integer('election_id')->unsigned()->nullable();
                $table->string('party_affiliation')->nullable();
                $table->string('election_status')->nullable();
                $table->string('office')->nullable();
                $table->string('office_level')->nullable();
                $table->boolean('is_incumbent')->nullable();
                $table->string('district_type')->nullable();
                $table->string('district')->nullable();
                $table->string('district_identifier', 4)->nullable();
                $table->string('ballotpedia_url')->nullable();
                $table->string('website_url')->nullable();
                $table->string('donate_url')->nullable();
                $table->string('facebook_profile', 200)->nullable();
                $table->string('twitter_handle')->nullable();
                $table->integer('data_source_id')->unsigned();
                $table->timestamps();

                
                $table->foreign('election_id')->references('id')->on('consolidated_elections');
                $table->foreign('data_source_id')->references('id')->on('data_sources');
            });
        }

        if(!Schema::hasTable('news')) {
            Schema::create('news', function (Blueprint $table) {
                $table->increments('id');
                $table->string('url');
                $table->string('thumbnail_url');
                $table->string('title');
                $table->text('description');
                $table->unsignedInteger('candidate_id')->nullable();
                $table->dateTime('publish_date');
                $table->timestamps();

                $table->foreign('candidate_id')->references('id')->on('consolidated_candidates');
            });
        }

        if(!Schema::hasTable('user_ballots')) {
            Schema::create('user_ballots', function(Blueprint $table) {
                $table->increments('id');
                $table->integer('user_id')->unsigned();
                $table->string("address_line_1")->nullable();
                $table->string("address_line_2")->nullable();
                $table->string("city");
                $table->string("zip");
                $table->string("county");
                $table->string("state_abbreviation", 2);
                $table->integer('congressional_district')->nullable();
                $table->string('state_legislative_district')->nullable();
                $table->string('state_house_district')->nullable();
                $table->timestamps();

                $table->foreign('user_id')->references('id')->on('users');
            });
        }

        if(!Schema::hasTable('user_news')) {
            Schema::create('user_news', function(Blueprint $table) {
                $table->integer('user_id')->unsigned();
                $table->integer('news_id')->unsigned();

                $table->primary(['user_id', 'news_id']);
                
                $table->foreign('user_id')->references('id')->on('users');
                $table->foreign('news_id')->references('id')->on('news');
            });
        }

        if(!Schema::hasTable('user_ballot_candidates')) {
            Schema::create('user_ballot_candidates', function(Blueprint $table) {
                $table->integer('user_ballot_id')->unsigned();
                $table->integer('candidate_id')->unsigned();

                $table->primary(['user_ballot_id', 'candidate_id']);
                
                $table->foreign('user_ballot_id')->references('id')->on('user_ballots')->onDelete('cascade');
                $table->foreign('candidate_id')->references('id')->on('consolidated_candidates')->onDelete('cascade');
            });
        }

        if(!Schema::hasTable('candidate_news_imports')) {
            Schema::create('candidate_news_imports', function(Blueprint $table) {
                $table->integer('candidate_id')->unsigned();
                $table->datetime('last_updated_timestamp')->nullable();

                $table->index('candidate_id');
                
                $table->foreign('candidate_id')->references('id')->on('consolidated_candidates')->onDelete('cascade');
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
        // Schema::dropIfExists('candidate_news_imports');
        // Schema::dropIfExists('user_ballot_candidates');
        // Schema::dropIfExists('user_news');
        // Schema::dropIfExists('user_ballots');
        // Schema::dropIfExists('news');
        // Schema::dropIfExists('candidates');
        // Schema::dropIfExists('consolidated_candidates');
        // Schema::dropIfExists('elections');
        // Schema::dropIfExists('consolidated_elections');
        // Schema::dropIfExists('data_source_priorities');
        // Schema::dropIfExists('data_sources');
    }
}
