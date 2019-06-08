<?php


use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Class RemoveMachineIdSaleRecipesTable
 */
class RemoveMachineIdSaleRecipesTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('sales_orders_recipes', function (Blueprint $table) {
            $table->dropForeign('sales_orders_recipes_machine_id_foreign');
            $table->dropColumn('machine_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('sales_orders_recipes', function (Blueprint $table) {
            $table->unsignedBigInteger('machine_id')->after('recipe_id');
            $table->foreign('machine_id')->references('id')->on('machines');
        });
    }
}
