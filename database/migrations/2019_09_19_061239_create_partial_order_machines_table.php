<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Class CreatePartialOrderMachinesTable
 */
class CreatePartialOrderMachinesTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('partial_order_machines', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->integer('reed');
            $table->integer('panno');
            $table->unsignedBigInteger('machine_id');
            $table->unsignedBigInteger('partial_order_id');
            $table->unsignedBigInteger('sales_order_id');
        });

        Schema::table('partial_order_machines', function (Blueprint $table) {
            $table->foreign('partial_order_id')->references('id')->on('recipes_partial_orders');
            $table->foreign('machine_id')->references('id')->on('machines');
            $table->foreign('sales_order_id')->references('id')->on('sales_orders');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('partial_order_machines');
    }
}
