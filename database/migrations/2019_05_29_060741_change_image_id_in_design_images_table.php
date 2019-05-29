<?php


use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Class ChangeImageIdInDesignImagesTable
 */
class ChangeImageIdInDesignImagesTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('design_images', function (Blueprint $table) {
            $table->dropForeign('design_images_image_id_foreign');
            $table->dropColumn('image_id');
            $table->unsignedBigInteger('file_id')->after('design_id');
            $table->foreign('file_id')->references('id')->on('files');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('design_images', function (Blueprint $table) {
            $table->unsignedBigInteger('image_id')->after('design_id');
            $table->foreign('image_id')->references('id')->on('files');
            $table->dropForeign('design_images_file_id_foreign');
            $table->dropColumn('file_id');
        });
    }
}
