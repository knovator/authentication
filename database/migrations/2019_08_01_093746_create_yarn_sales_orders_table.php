<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

/**
 * Class CreateYarnSalesOrdersTable
 */
class CreateYarnSalesOrdersTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('yarn_sales_orders', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('order_no');
            $table->dateTime('order_date');
            $table->string('challan_no');
            $table->unsignedBigInteger('customer_id');
            $table->unsignedBigInteger('status_id');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::table('yarn_sales_orders', function (Blueprint $table) {
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
        Schema::dropIfExists('yarn_sales_orders');
    }
}
