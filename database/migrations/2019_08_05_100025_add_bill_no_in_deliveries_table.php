<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Class AddBillNoInDeliveriesTable
 */
class AddBillNoInDeliveriesTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('deliveries', function (Blueprint $table) {
            $table->string('bill_no')->after('status_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('deliveries', function (Blueprint $table) {
            $table->dropColumn('bill_no');
        });
    }
}
