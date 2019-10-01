<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Class AddIsActiveInThreadsColorsTable
 */
class AddIsActiveInThreadsColorsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('threads_colors', function (Blueprint $table) {
            $table->boolean('is_active')->default(1)->after('color_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('threads_colors', function (Blueprint $table) {
            $table->dropColumn('is_active');
        });
    }
}
