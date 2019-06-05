<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Class CreateThreadsTable
 */
class CreateThreadsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('threads', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->integer('denier');
            $table->unsignedBigInteger('type_id');
            $table->integer('price');
            $table->boolean('is_active');
            $table->timestamps();
            $table->softDeletes();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
        });

        Schema::table('threads', function (Blueprint $table) {
            $table->foreign('type_id')->references('id')->on('masters');
            $table->foreign('created_by')->references('id')->on('users');
            $table->foreign('deleted_by')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('threads');
    }
}
