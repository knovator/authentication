<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Class CreateDeliveriesTable
 */
class CreateDeliveriesTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('deliveries', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->date('delivery_date');
            $table->unsignedBigInteger('status_id');
            $table->timestamps();
        });
        Schema::table('deliveries', function (Blueprint $table) {
            $table->foreign('status_id')->references('id')->on('masters');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('deliveries');
    }
}
