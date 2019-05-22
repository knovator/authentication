<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Class CreateDesignImagesTable
 */
class CreateDesignImagesTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('design_images', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('design_id');
            $table->unsignedBigInteger('image_id');
            $table->enum('type', ['MAIN', 'SUB']);
            $table->softDeletes();
        });

        Schema::table('design_images', function (Blueprint $table) {
            $table->foreign('design_id')->references('id')->on('designs');
            $table->foreign('image_id')->references('id')->on('files');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('design_images');
    }
}
