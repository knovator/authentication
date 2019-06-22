<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Class AddOrderRecipeIdInStocksTable
 */
class AddOrderRecipeIdInStocksTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('stocks', function (Blueprint $table) {
            $table->unsignedBigInteger('order_recipe_id')->nullable()->after('order_id');
        });

        Schema::table('stocks', function (Blueprint $table) {
            $table->foreign('order_recipe_id')->references('id')->on('sales_orders_recipes');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('stocks', function (Blueprint $table) {
            $table->dropForeign('stocks_order_recipe_id_foreign');
        });
    }
}
