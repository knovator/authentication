<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePurchasePartialOrdersTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('purchase_partial_orders', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('purchase_order_thread_id');
            $table->decimal('kg_qty');
            $table->unsignedBigInteger('delivery_id');
        });
        Schema::table('purchase_partial_orders', function (Blueprint $table) {
            $table->foreign('delivery_id')->references('id')->on('purchase_deliveries');
            $table->foreign('purchase_order_thread_id')->references('id')
                  ->on('purchase_order_threads');
        });

        Schema::table('purchase_deliveries', function (Blueprint $table) {
            $table->decimal('total_kg')->after('bill_no');
            $table->dropSoftDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('purchase_partial_orders');

        Schema::table('purchase_deliveries', function (Blueprint $table) {
            $table->dropColumn('kg_qty');
            $table->softDeletes();
        });
    }
}
