<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Class AddIsDemandedInThreadsColorsTable
 */
class AddIsDemandedInThreadsColorsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('threads_colors', function (Blueprint $table) {
            $table->boolean('is_demanded')->default(0)->after('is_active');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('threads_colors', function (Blueprint $table) {
            $table->dropColumn('is_demanded');
        });
    }
}
