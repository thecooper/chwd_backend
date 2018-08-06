<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDataSourcePriorityTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('data_source_priorities', function (Blueprint $table) {
            $table->string('destination_table');
            $table->unsignedInteger('data_source_id');
            $table->unsignedInteger('priority');

            $table->index(['destination_table']);

            $table->foreign('data_source_id')->references('id')->on('data_sources');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('data_source_priorities');
    }
}
