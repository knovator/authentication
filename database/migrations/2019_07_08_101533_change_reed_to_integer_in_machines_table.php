<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeReedToIntegerInMachinesTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('machines', function (Blueprint $table) {
            $table->integer('reed')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('machines', function (Blueprint $table) {
            $table->string('reed')->change();
        });
    }
}
