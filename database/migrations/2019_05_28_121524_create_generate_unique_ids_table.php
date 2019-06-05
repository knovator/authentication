<?php


use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Class CreateGenerateUniqueIdsTable
 */
class CreateGenerateUniqueIdsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('generate_unique_ids', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('code', 60);
            $table->string('prefix', 60);
            $table->integer('count');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('generate_unique_ids');
    }
}
