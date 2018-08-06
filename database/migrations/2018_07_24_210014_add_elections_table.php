<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddElectionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('elections', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('state', 30);
            $table->string('state_abbreviation', 2);
            $table->string('county')->nullable();
            $table->string('district')->nullable();
            $table->date('primary_date');
            $table->date('general_date');
            $table->boolean('is_special');
            $table->boolean('is_runoff');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('elections');
    }
}
