<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

/**
 * Class AddMetersInDeliveriesTable
 */
class AddMetersInDeliveriesTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('deliveries', function (Blueprint $table) {
            $table->integer('meters')->default(0)->after('status_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('deliveries', function (Blueprint $table) {
            $table->dropColumn('meters');
        });
    }
}
