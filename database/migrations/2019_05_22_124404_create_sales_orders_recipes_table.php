<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSalesOrdersRecipesTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('sales_orders_recipes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('sales_order_id');
            $table->integer('pcs');
            $table->integer('meters');
            $table->integer('total_meters');
            $table->unsignedBigInteger('recipe_id');
            $table->unsignedBigInteger('machine_id');
            $table->softDeletes();
        });

        Schema::table('sales_orders_recipes', function (Blueprint $table) {
            $table->foreign('sales_order_id')->references('id')->on('sales_orders');
            $table->foreign('recipe_id')->references('id')->on('recipes');
            $table->foreign('machine_id')->references('id')->on('machines');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('sales_orders_recipes');
    }
}
