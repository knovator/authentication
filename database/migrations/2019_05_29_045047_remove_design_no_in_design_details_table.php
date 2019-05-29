<?php


use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RemoveDesignNoInDesignDetailsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('design_details', function (Blueprint $table) {
            $table->dropColumn('design_no');
        });

        Schema::table('designs', function (Blueprint $table) {
            $table->string('design_no', 60)->after('id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('design_details', function (Blueprint $table) {
            $table->string('design_no', 60);
        });

        Schema::table('designs', function (Blueprint $table) {
            $table->dropColumn('design_no');
        });
    }
}
