<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Class CreateDesignsTable
 */
class CreateDesignsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('designs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('quality_id');
            $table->string('type');
            $table->integer('fiddles');
            $table->boolean('is_active')->default(1);
            $table->boolean('is_approved')->default(0);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('designs', function (Blueprint $table) {
            $table->foreign('quality_id')->references('id')->on('masters');
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
        Schema::dropIfExists('designs');
    }
}
