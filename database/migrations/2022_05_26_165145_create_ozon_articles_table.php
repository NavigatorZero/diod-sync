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
            $table->integer('article', false, true);
            $table->integer('ozon_product_id', false, true);
            $table->float('ozon_old_price');
            $table->string('name');
            $table->float('product_volume');
            $table->float('product_weight');
            $table->integer("sima_order_minimum", false, true)->nullable();
            $table->float("sima_price")->nullable();
            $table->float("sima_wholesale_price")->nullable();
            $table->bigInteger("sima_id")->nullable();
            $table->bigInteger("sima_stocks")->nullable();
            $table->boolean('is_synced')->default(false);

            $table->bigInteger("price_id", false, true)->nullable();

            $table->foreign("price_id")->references("id")->on("price")->cascadeOnDelete();

            $table->unique("article");
            $table->unique("price_id");

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
