<?php


use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeQtyInPurchaseOrderThreadsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('purchase_order_threads', function (Blueprint $table) {
            $table->renameColumn('qty', 'kg_qty');
        });

        Schema::table('stocks', function (Blueprint $table) {
            $table->renameColumn('qty', 'kg_qty');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('purchase_order_threads', function (Blueprint $table) {
            $table->renameColumn('kg_qty', 'qty');
        });
        Schema::table('stocks', function (Blueprint $table) {
            $table->renameColumn('qty', 'kg_qty');
        });
    }
}
