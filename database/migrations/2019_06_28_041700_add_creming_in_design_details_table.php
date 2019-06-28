<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Class AddCremingInDesignsTable
 */
class AddCremingInDesignDetailsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('design_details', function (Blueprint $table) {
            $table->boolean('creming')->after('designer_no');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('design_details', function (Blueprint $table) {
            $table->dropColumn('creming');
        });
    }
}
