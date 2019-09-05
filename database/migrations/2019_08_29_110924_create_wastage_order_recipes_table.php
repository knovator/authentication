<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWastageOrderRecipesTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('wastage_order_recipes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('wastage_order_id');
            $table->integer('pcs');
            $table->integer('meters');
            $table->integer('total_meters');
            $table->unsignedBigInteger('recipe_id');
            $table->softDeletes();
        });

        Schema::table('wastage_order_recipes', function (Blueprint $table) {
            $table->foreign('wastage_order_id')->references('id')->on('wastage_orders');
            $table->foreign('recipe_id')->references('id')->on('recipes');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('wastage_order_recipes');
    }
}
