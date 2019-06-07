<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFiddleNoInRecipesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('recipes_fiddles', function (Blueprint $table) {
            $table->tinyInteger('fiddle_no');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('recipes_fiddles', function (Blueprint $table) {
            $table->dropColumn('fiddle_no');
        });
    }
}
