<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Class CreateAgentsTable
 */
class CreateAgentsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('agents', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('contact_number');
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->unsignedBigInteger('agent_id')->nullable()->after('state_id');
            $table->foreign('agent_id')->references('id')->on('agents');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('agents');
        Schema::table('customers', function (Blueprint $table) {
            $table->dropForeign('customers_agent_id_foreign');
            $table->dropColumn('agent_id');
        });
    }
}
