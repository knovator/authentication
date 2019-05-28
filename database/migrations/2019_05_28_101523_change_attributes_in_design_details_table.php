<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Class ChangeAttributesInDesignDetailsTable
 */
class ChangeAttributesInDesignDetailsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('design_details', function (Blueprint $table) {
            $table->renameColumn('pick', 'avg_pick');
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('design_details', function (Blueprint $table) {
            $table->renameColumn('avg_pick', 'pick');
            $table->dropSoftDeletes();
        });
    }
}
