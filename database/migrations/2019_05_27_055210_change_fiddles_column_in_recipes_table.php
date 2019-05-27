<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeFiddlesColumnInRecipesTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('recipes', function (Blueprint $table) {
            $table->renameColumn('fiddles', 'total_fiddles');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('recipes', function (Blueprint $table) {
            $table->renameColumn('total_fiddles', 'fiddles');
        });
    }
}
