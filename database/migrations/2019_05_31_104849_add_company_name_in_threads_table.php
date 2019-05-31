<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Class AddCompanyNameInThreadsTable
 */
class AddCompanyNameInThreadsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('threads', function (Blueprint $table) {
            $table->string('company_name')->after('denier');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('threads', function (Blueprint $table) {
            $table->dropColumn('company_name');
        });
    }
}
