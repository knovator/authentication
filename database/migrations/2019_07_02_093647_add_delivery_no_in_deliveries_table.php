<?php


use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDeliveryNoInDeliveriesTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('deliveries', function (Blueprint $table) {
            $table->string('delivery_no')->after('id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('deliveries', function (Blueprint $table) {
            $table->dropColumn('delivery_no');
        });
    }
}
