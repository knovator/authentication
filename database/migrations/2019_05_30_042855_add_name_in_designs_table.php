<?php


use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Class AddNameInDesignsTable
 */
class AddNameInDesignsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('designs', function (Blueprint $table) {
            $table->string('name', 60)->after('id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('designs', function (Blueprint $table) {
            $table->dropColumn('name');
        });
    }
}
