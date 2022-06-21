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
        Schema::create('price', function (Blueprint $table) {
            $table->id();
            $table->float("commision")->nullable();
            $table->float("price_after")->nullable();
            $table->float("fbs")->nullable();
            $table->float("min_price")->nullable();
            $table->float("last_mile")->nullable();

            $table->integer("article_id", false, true)->nullable();

            $table->foreign("article_id")->references("id")->on("ozon_articles")->cascadeOnDelete();
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
        Schema::dropIfExists('price');
    }
};
