<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Class CreateRecipesPartialOrdersTable
 */
class CreateRecipesPartialOrdersTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('recipes_partial_orders', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('sales_order_recipe_id');
            $table->integer('total_meters');
            $table->unsignedBigInteger('status_id');
            $table->unsignedBigInteger('machine_id')->nullable();
            $table->softDeletes();
        });

        Schema::table('recipes_partial_orders', function (Blueprint $table) {
            $table->foreign('sales_order_recipe_id')->references('id')->on('customers');
            $table->foreign('status_id')->references('id')->on('masters');
            $table->foreign('machine_id')->references('id')->on('machines');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('recipes_partial_orders');
    }
}
