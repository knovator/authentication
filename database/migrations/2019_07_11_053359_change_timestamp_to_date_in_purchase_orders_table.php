<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeTimestampToDateInPurchaseOrdersTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        DB::raw('ALTER TABLE purchase_orders CHANGE order_date order_date timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {

    }
}
