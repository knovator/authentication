<?php


use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddManufacturingCompanyIdInSalesOrdersTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('sales_orders', function (Blueprint $table) {
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
        Schema::table('sales_orders', function (Blueprint $table) {
            $table->dropForeign('sales_orders_manufacturing_company_id_foreign');
            $table->dropColumn('manufacturing_company_id');
        });
    }
}
