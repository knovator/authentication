<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Class ChangeDataTypeDesignDetailsTable
 */
class ChangeDataTypeDesignDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('design_details', function (Blueprint $table) {
            $table->integer('avg_pick')->change();
            $table->integer('pick_on_loom')->change();
            $table->integer('panno')->change();
            $table->integer('additional_panno')->change();
            $table->integer('reed')->change();
        });

        Schema::table('design_fiddle_picks', function (Blueprint $table) {
            $table->integer('pick')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('design_details', function (Blueprint $table) {
            $table->float('avg_pick')->change();
            $table->float('pick_on_loom')->change();
            $table->float('panno')->change();
            $table->float('additional_panno')->change();
            $table->float('reed')->change();
        });

        Schema::table('design_fiddle_picks', function (Blueprint $table) {
            $table->float('pick')->change();
        });


    }
}
