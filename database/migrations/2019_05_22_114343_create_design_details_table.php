<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Class CreateDesignDetailsTable
 */
class CreateDesignDetailsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('design_details', function (Blueprint $table) {
            $table->unsignedBigInteger('design_id');
            $table->string('design_no');
            $table->string('designer_no');
            $table->float('pick');
            $table->float('pick_on_loom');
            $table->integer('panno');
            $table->integer('additional_panno')->default(0);
            $table->string('reed');
        });

        Schema::table('design_details', function (Blueprint $table) {
            $table->foreign('design_id')->references('id')->on('designs');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('design_details');
    }
}
