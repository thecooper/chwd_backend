<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateElectionsConsolidatedTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('consolidated_elections')) {
            Schema::create('consolidated_elections', function (Blueprint $table) {
                $table->increments('id');
                $table->timestamps();
                $table->string('name');
                $table->string('state_abbreviation', 2);
                $table->string('county')->nullable()->nullable();
                $table->string('district')->nullable()->nullable();
                $table->date('primary_date')->nullable();
                $table->date('general_date')->nullable();
                $table->boolean('is_special')->nullable();
                $table->boolean('is_runoff')->nullable();
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
        Schema::dropIfExists('consolidated_elections');
    }
}
