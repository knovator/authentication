<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeDecimalKgQtyInPurchaseOrderTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('purchase_order_threads', function (Blueprint $table) {
            $table->decimal('kg_qty')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('purchase_order_threads', function (Blueprint $table) {
            $table->integer('kg_qty')->change();
        });
    }
}
