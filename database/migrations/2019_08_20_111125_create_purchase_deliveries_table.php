<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Class CreatePurchaseDeliveriesTable
 */
class CreatePurchaseDeliveriesTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('purchase_deliveries', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('delivery_no', 60);
            $table->date('delivery_date');
            $table->unsignedBigInteger('purchase_order_id');
            $table->unsignedBigInteger('status_id');
            $table->string('bill_no');
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::table('purchase_deliveries', function (Blueprint $table) {
            $table->foreign('status_id')->references('id')->on('masters');
            $table->foreign('purchase_order_id')->references('id')->on('purchase_orders');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('purchase_deliveries');
    }
}
