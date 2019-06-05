<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePurchaseOrdersTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('order_no');
            $table->timestamp('order_date');
            $table->unsignedBigInteger('customer_id');
            $table->unsignedBigInteger('status_id');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->foreign('customer_id')->references('id')->on('users');
            $table->foreign('status_id')->references('id')->on('masters');
            $table->foreign('created_by')->references('id')->on('users');
            $table->foreign('deleted_by')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('purchase_orders');
    }
}
