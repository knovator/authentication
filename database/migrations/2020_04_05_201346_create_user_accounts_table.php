<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Class CreateUserAccountsTable
 */
class CreateUserAccountsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('user_accounts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->boolean('default')->default(0);
            $table->boolean('is_verified')->default(0);
            $table->string('email_verification_key', 60)->nullable();
            $table->timestamps();
        });

        Schema::table('user_accounts', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('user_accounts');
    }
}
