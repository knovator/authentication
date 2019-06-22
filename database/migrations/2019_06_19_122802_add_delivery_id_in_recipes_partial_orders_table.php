<?php


use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDeliveryIdInRecipesPartialOrdersTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('recipes_partial_orders', function (Blueprint $table) {
            $table->unsignedBigInteger('delivery_id');
            $table->dropForeign('recipes_partial_orders_status_id_foreign');
            $table->dropColumn('status_id');
        });

        Schema::table('recipes_partial_orders', function (Blueprint $table) {
            $table->foreign('delivery_id')->references('id')->on('deliveries');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('recipes_partial_orders', function (Blueprint $table) {
            $table->unsignedBigInteger('status_id');
            $table->dropForeign('recipes_partial_orders_delivery_id_foreign');
            $table->dropColumn('delivery_id');
        });

        Schema::table('recipes_partial_orders', function (Blueprint $table) {
            $table->foreign('status_id')->references('id')->on('masters');
        });
    }
}
