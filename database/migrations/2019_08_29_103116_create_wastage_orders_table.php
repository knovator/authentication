<?php


use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Class CreateWastageOrdersTable
 */
class CreateWastageOrdersTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('wastage_orders', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('order_no');
            $table->dateTime('order_date')->nullable();
            $table->dateTime('delivery_date')->nullable();
            $table->string('challan_no')->nullable();
            $table->integer('total_fiddles');
            $table->unsignedBigInteger('beam_id');
            $table->unsignedBigInteger('status_id');
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->string('customer_po_number')->nullable();
            $table->unsignedBigInteger('manufacturing_company_id')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::table('wastage_orders', function (Blueprint $table) {
            $table->foreign('beam_id')->references('id')->on('threads_colors');
            $table->foreign('customer_id')->references('id')->on('customers');
            $table->foreign('status_id')->references('id')->on('masters');
            $table->foreign('manufacturing_company_id')->references('id')
                  ->on('manufacturing_companies');
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
        Schema::dropIfExists('yarn_sales_orders');
    }
}
