<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Class AddRecipeTypeInRecipesTable
 */
class AddRecipeTypeInRecipesTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('recipes', function (Blueprint $table) {
            $table->enum('type', ['normal', 'wastage'])->default('normal')->after('name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('recipes', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
}
