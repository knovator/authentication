<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDesignFiddlePicksTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('design_fiddle_picks', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('design_id');
            $table->float('pick');
            $table->integer('fiddle_no');
            $table->softDeletes();
        });

        Schema::table('design_fiddle_picks', function (Blueprint $table) {
            $table->foreign('design_id')->references('id')->on('designs');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('design_fiddle_picks');
    }
}
