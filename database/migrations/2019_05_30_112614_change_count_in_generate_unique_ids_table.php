<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeCountInGenerateUniqueIdsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('generate_unique_ids', function (Blueprint $table) {
            $table->integer('count')->default(0)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('generate_unique_ids', function (Blueprint $table) {
            $table->integer('count')->change();
        });
    }
}
