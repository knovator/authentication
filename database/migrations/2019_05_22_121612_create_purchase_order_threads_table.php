<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePurchaseOrderThreadsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void`
     */
    public function up() {
        Schema::create('purchase_order_threads', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('thread_color_id');
            $table->unsignedBigInteger('purchase_order_id');
            $table->integer('qty');
            $table->softDeletes();
        });

        Schema::table('purchase_order_threads', function (Blueprint $table) {
            $table->foreign('thread_color_id')->references('id')->on('threads_colors');
            $table->foreign('purchase_order_id')->references('id')->on('purchase_orders');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('purchase_order_threads');
    }
}
