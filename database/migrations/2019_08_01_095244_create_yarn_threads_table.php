<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Class CreateYarnThreadsTable
 */
class CreateYarnThreadsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('yarn_threads', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('thread_color_id');
            $table->unsignedBigInteger('yarn_order_id');
            $table->decimal('kg_qty');
            $table->decimal('rate');
            $table->softDeletes();
        });
        Schema::table('yarn_threads', function (Blueprint $table) {
            $table->foreign('thread_color_id')->references('id')->on('threads_colors');
            $table->foreign('yarn_order_id')->references('id')->on('yarn_sales_orders');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('yarn_threads');
    }
}
