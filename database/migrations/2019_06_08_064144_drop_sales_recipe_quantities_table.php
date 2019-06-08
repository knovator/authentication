<?php


use Illuminate\Database\Migrations\Migration;

class DropSalesRecipeQuantitiesTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::drop('sales_orders_quantities');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        //
    }
}
