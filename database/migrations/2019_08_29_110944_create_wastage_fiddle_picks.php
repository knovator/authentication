<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Class CreateWastageFiddlePicks
 */
class CreateWastageFiddlePicks extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('wastage_fiddle_picks', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('wastage_order_id');
            $table->integer('pick');
            $table->integer('fiddle_no');
            $table->softDeletes();
        });

        Schema::table('wastage_fiddle_picks', function (Blueprint $table) {
            $table->foreign('wastage_order_id')->references('id')->on('wastage_orders');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('wastage_fiddle_picks');
    }
}
