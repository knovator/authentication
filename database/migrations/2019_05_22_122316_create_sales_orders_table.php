<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSalesOrdersTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('sales_orders', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('order_no');
            $table->date('order_date');
            $table->date('delivery_date');
            $table->string('bill_no');
            $table->unsignedBigInteger('design_id');
            $table->unsignedBigInteger('design_beam_id');
            $table->unsignedBigInteger('customer_id');
            $table->unsignedBigInteger('status_id');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('sales_orders', function (Blueprint $table) {
            $table->foreign('design_id')->references('id')->on('designs');
            $table->foreign('design_beam_id')->references('id')->on('design_beams');
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
        Schema::dropIfExists('sales_orders');
    }
}
