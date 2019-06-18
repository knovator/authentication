<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RemoveOrderRecipeIdRecipesPartialOrdersTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('recipes_partial_orders', function (Blueprint $table) {
            $table->dropForeign('recipes_partial_orders_sales_order_recipe_id_foreign');
            $table->dropColumn('sales_order_recipe_id');
        });

        Schema::table('recipes_partial_orders', function (Blueprint $table) {
            $table->unsignedBigInteger('sales_order_recipe_id');
            $table->foreign('sales_order_recipe_id')->references('id')->on('sales_orders_recipes');
        });


    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('recipes_partial_orders', function (Blueprint $table) {
            $table->dropForeign('recipes_partial_orders_sales_order_recipe_id_foreign');
            $table->dropColumn('sales_order_recipe_id');
        });

        Schema::table('recipes_partial_orders', function (Blueprint $table) {
            $table->unsignedBigInteger('sales_order_recipe_id');
            $table->foreign('sales_order_recipe_id')->references('id')->on('customers');
        });
    }
}
