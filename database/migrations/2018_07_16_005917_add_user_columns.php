<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUserColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string("address_line_1");
            $table->string("address_line_2");
            $table->string("city");
            $table->string("state");
            $table->string("zip");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn("address_line_1");
            $table->dropColumn("address_line_2");
            $table->dropColumn("city");
            $table->dropColumn("state");
            $table->dropColumn("zip");
        });
    }
}
