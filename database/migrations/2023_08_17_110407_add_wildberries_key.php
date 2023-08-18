<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up()
    {
        Schema::table('ozon_articles', function (Blueprint $table) {
            $table->bigInteger('willdberries_id', false, true)
            ->nullable()
                ->unique()
                ->after('ozon_product_id');
            $table->bigInteger('willdberries_barcode', false, true)
                ->nullable()
                ->unique()
                ->after('willdberries_id');
        });
    }

    public function down()
    {
        Schema::table('ozon_articles', function ($table) {

            $table->dropColumn('willdberries_id');
            $table->dropColumn('willdberries_barcode');
        });
    }
};
