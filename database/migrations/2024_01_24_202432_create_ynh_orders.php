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
        Schema::create('ynh_orders', function (Blueprint $table) {
            $table->id();
            $table->intOrBigIntBasedOnRelated('order_id', Schema::connection(null), 'orders.id')->cascadeOnDelete();
            $table->foreign('order_id')->references('id')->on('orders')->cascadeOnDelete();
            $table->intOrBigIntBasedOnRelated('order_item_id', Schema::connection(null), 'order_items.id')->cascadeOnDelete();
            $table->foreign('order_item_id')->references('id')->on('order_items')->cascadeOnDelete();
            $table->enum('product_type', [
                \App\Enums\ProductTypeEnum::APPLICATION->value,
                \App\Enums\ProductTypeEnum::SERVER->value,
            ]);
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
        Schema::dropIfExists('ynh_orders');
    }
};
