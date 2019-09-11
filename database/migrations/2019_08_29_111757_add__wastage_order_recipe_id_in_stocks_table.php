<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Class AddWastageOrderRecipeIdInStocksTable
 */
class AddWastageOrderRecipeIdInStocksTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('stocks', function (Blueprint $table) {
            $table->unsignedBigInteger('wastage_recipe_id')->nullable()->after('purchased_thread_id');
            $table->foreign('wastage_recipe_id')->references('id')->on('wastage_order_recipes');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('stocks', function (Blueprint $table) {
            $table->dropForeign('stocks_wastage_recipe_id_foreign');
            $table->dropColumn('wastage_recipe_id');
        });
    }
}
