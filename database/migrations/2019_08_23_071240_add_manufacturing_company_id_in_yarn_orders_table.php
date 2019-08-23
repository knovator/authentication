<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddManufacturingCompanyIdInYarnOrdersTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('yarn_sales_orders', function (Blueprint $table) {
            $table->unsignedBigInteger('manufacturing_company_id')->nullable()->after('status_id');
            $table->foreign('manufacturing_company_id')->references('id')
                  ->on('manufacturing_companies');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('yarn_sales_orders', function (Blueprint $table) {
            $table->dropForeign('yarn_sales_orders_manufacturing_company_id_foreign');
            $table->dropColumn('manufacturing_company_id');
        });
    }
}
