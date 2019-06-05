<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Class CreateCustomersTable
 */
class CreateCustomersTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('customers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('first_name');
            $table->string('last_name');
            $table->string('slug')->nullable();
            $table->string('email');
            $table->string('phone');
            $table->string('gst_no')->nullable();
            $table->text('city_name')->nullable();
            $table->unsignedBigInteger('state_id')->nullable();
            $table->text('address')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->foreign('state_id')->references('id')->on('states');
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
        Schema::dropIfExists('customers');
    }
}
