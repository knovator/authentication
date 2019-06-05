<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeQualityIdToNameInDesignsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('designs', function (Blueprint $table) {
            $table->string('quality_name')->after('id');
            $table->unsignedBigInteger('type_id')->after('quality_name');
            $table->foreign('type_id')->references('id')->on('masters');
            $table->dropForeign('designs_quality_id_foreign');
            $table->dropColumn('quality_id');
            $table->dropColumn('type');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('designs', function (Blueprint $table) {
            $table->dropColumn('quality_name');
            $table->dropForeign('designs_type_id_foreign');
            $table->dropColumn('type_id');
            $table->unsignedBigInteger('quality_id')->after('id');
            $table->foreign('quality_id')->references('id')->on('masters');
            $table->string('type')->after('id');
        });
    }
}
