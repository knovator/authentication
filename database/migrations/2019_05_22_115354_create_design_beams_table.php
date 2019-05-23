<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDesignBeamsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('design_beams', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('design_id');
            $table->unsignedBigInteger('thread_color_id');
            $table->softDeletes();
        });
        Schema::table('design_beams', function (Blueprint $table) {
            $table->foreign('thread_color_id')->references('id')->on('threads_colors');
            $table->foreign('design_id')->references('id')->on('designs');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('design_beams');
    }
}
