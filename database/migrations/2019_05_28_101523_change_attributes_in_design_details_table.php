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

        Schema::table('designs', function (Blueprint $table) {
            $table->dropForeign('designs_type_id_foreign');
            $table->dropColumn('type_id');
            $table->string('type',60);
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

        Schema::table('design_details', function (Blueprint $table) {
            $table->dropColumn('type');
            $table->unsignedBigInteger('type_id')->after('quality_name');
            $table->foreign('type_id')->references('id')->on('masters');
        });
    }
}
