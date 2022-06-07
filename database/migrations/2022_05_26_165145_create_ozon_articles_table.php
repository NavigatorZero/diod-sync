<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ozon_articles', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('article', false, true);
            $table->string('name');
            $table->float('product_volume');
            $table->float('product_weight');
            $table->integer("sima_order_minimum", false, true)->nullable();
            $table->float("sima_price")->nullable();
            $table->float("sima_wholesale_price")->nullable();
            $table->bigInteger("sima_id")->nullable();
            $table->bigInteger("sima_stocks")->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ozon_articles');
    }
};
