<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyElectionsColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('elections', function (Blueprint $table) {
            $table->dropColumn('state');

            $table->timestamps();
            $table->date('primary_date')->nullable()->change();
            $table->date('general_date')->nullable()->change();
            $table->boolean('is_special')->nullable()->change();
            $table->boolean('is_runoff')->nullable()->change();
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
            $table->string('state', 30);

            $table->dropTimestamps();
            $table->date('primary_date')->nullable(true)->change();
            $table->date('general_date')->nullable(true)->change();
            $table->boolean('is_special')->nullable(true)->change();
            $table->boolean('is_runoff')->nullable(true)->change();
        });
    }
}
