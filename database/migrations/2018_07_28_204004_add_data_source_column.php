<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDataSourceColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('elections', function (Blueprint $table) {
            $table->integer('data_source_id')->unsigned();
            $table->foreign('data_source_id')->references('id')->on('data_sources');
        });

        Schema::table('candidates', function (Blueprint $table) {
            $table->integer('data_source_id')->unsigned();
            $table->foreign('data_source_id')->references('id')->on('data_sources');
        });

        Schema::table('news', function (Blueprint $table) {
            $table->integer('data_source_id')->unsigned();
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
        if (Schema::hasColumn('elections', 'data_source_id')) {
            Schema::table('elections', function (Blueprint $table) {
                $table->dropForeign('elections_data_source_id_foreign');
                $table->dropColumn('data_source_id');
            });
        }

        if (Schema::hasColumn('candidates', 'data_source_id')) {
            Schema::table('candidates', function (Blueprint $table) {
                $table->dropForeign('candidates_data_source_id_foreign');
                $table->dropColumn('data_source_id');
            });
        }

        if (Schema::hasColumn('news', 'data_source_id')) {
            Schema::table('news', function (Blueprint $table) {
                $table->dropForeign('news_data_source_id_foreign');
                $table->dropColumn('data_source_id');
            });
        }
    }
}
