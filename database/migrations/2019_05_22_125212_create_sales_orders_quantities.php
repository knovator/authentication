<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSalesOrdersQuantities extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('sales_orders_quantities', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('sales_order_recipe_id');
            $table->unsignedBigInteger('thread_color_id');
            $table->integer('qty');
            $table->integer('fiddle_no');
            $table->softDeletes();
        });

        Schema::table('sales_orders_quantities', function (Blueprint $table) {
            $table->foreign('sales_order_recipe_id')->references('id')->on('sales_orders_recipes');
            $table->foreign('thread_color_id')->references('id')->on('threads_colors');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('sales_orders_quantities');
    }
}
