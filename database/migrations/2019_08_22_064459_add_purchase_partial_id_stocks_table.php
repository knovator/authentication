<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Class AddPurchasePartialIdStocksTable
 */
class AddPurchasePartialIdStocksTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {

        Schema::table('stocks', function (Blueprint $table) {
            $table->dropForeign('stocks_partial_order_id_foreign');
        });

        Schema::table('stocks', function (Blueprint $table) {
            $table->dropIndex('stocks_partial_order_id_foreign');
        });

        Schema::table('stocks', function (Blueprint $table) {
            $table->string('partial_order_type')->nullable()->after('partial_order_id');
        });

        Schema::table('stocks', function (Blueprint $table) {
            $table->index(['partial_order_id', 'partial_order_type']);
        });


        Schema::table('stocks', function (Blueprint $table) {
            $table->unsignedBigInteger('purchased_thread_id')->nullable()
                  ->after('partial_order_type');
            $table->foreign('purchased_thread_id')->references('id')->on('purchase_order_threads');
        });

        DB::statement("UPDATE `stocks` SET partial_order_type = 'sales_partial' WHERE partial_order_id IS NOT NULL");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {

    }
}
