<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('t_items_items', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();
            $table->unsignedBigInteger('from_item_id')->index('t_items_items_from_item_id_foreign');
            $table->unsignedBigInteger('to_item_id')->index('t_items_items_to_item_id_foreign');
            $table->string('type')->index();

            $table->unique(['type', 'from_item_id', 'to_item_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_items_items');
    }
};
