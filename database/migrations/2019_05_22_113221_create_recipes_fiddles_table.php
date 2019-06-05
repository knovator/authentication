<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Class CreateRecipesFiddlesTable
 */
class CreateRecipesFiddlesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('recipes_fiddles', function (Blueprint $table) {
            $table->unsignedBigInteger('recipe_id');
            $table->unsignedBigInteger('thread_color_id');
        });

        Schema::table('recipes_fiddles', function (Blueprint $table) {
            $table->foreign('thread_color_id')->references('id')->on('threads_colors');
            $table->foreign('recipe_id')->references('id')->on('recipes');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('recipes_fiddles');
    }
}
