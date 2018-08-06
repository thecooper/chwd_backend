<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CandidatesWebsiteDonateNullable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('candidates', function (Blueprint $table) {
            $table->string('website_url')->nullable()->change();
            $table->string('donate_url')->nullable()->change();
            $table->string('facebook_profile')->nullable()->change();
            $table->string('twitter_handle')->nullable()->change();
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
            $table->string('website_url')->nullable(false)->change();
            $table->string('donate_url')->nullable(false)->change();
            $table->string('facebook_profile')->nullable(false)->change();
            $table->string('twitter_handle')->nullable(false)->change();
        });
    }
}
