<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAdditionalColumnsInPartialOrdersTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('recipes_partial_orders', function (Blueprint $table) {
            $table->integer('pcs')->after('sales_order_recipe_id');
            $table->integer('meters')->after('sales_order_recipe_id');
        });

        Schema::table('sales_orders_quantities', function (Blueprint $table) {
            $table->renameColumn('qty', 'kg_qty');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('recipes_partial_orders', function (Blueprint $table) {
            $table->dropColumn('pcs');
            $table->dropColumn('meters');
        });
        Schema::table('sales_orders_quantities', function (Blueprint $table) {
            $table->renameColumn('kg_qty', 'qty');
        });
    }
}
