<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Class ChangeKgQtyInStocksTable
 */
class ChangeKgQtyInStocksTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('stocks', function (Blueprint $table) {
            $table->decimal('kg_qty')->change();
        });

        Schema::table('sales_orders_quantities', function (Blueprint $table) {
            $table->decimal('kg_qty')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('stocks', function (Blueprint $table) {
            $table->integer('kg_qty')->change();
        });

        Schema::table('sales_orders_quantities', function (Blueprint $table) {
            $table->integer('kg_qty')->change();
        });
    }
}
