<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Class AdditionalDetailsInManufacturingCompaniesTable
 */
class AdditionalDetailsInManufacturingCompaniesTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('manufacturing_companies', function (Blueprint $table) {
            $table->string('address');
            $table->string('country');
            $table->string('state');
            $table->string('state_code');
            $table->string('city');
            $table->string('pin_code');
            $table->string('phone');
            $table->string('gst_no');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('manufacturing_companies', function (Blueprint $table) {
            $table->dropColumn('address');
            $table->dropColumn('country');
            $table->dropColumn('state');
            $table->dropColumn('state_code');
            $table->dropColumn('city');
            $table->dropColumn('pin_code');
            $table->dropColumn('phone');
            $table->dropColumn('gst_no');
        });
    }
}
