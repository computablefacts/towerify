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
        Schema::table('t_facts_items', function (Blueprint $table) {
            $table->foreign(['fact_id'])->references(['id'])->on('t_facts')->onUpdate('restrict')->onDelete('cascade');
            $table->foreign(['item_id'])->references(['id'])->on('t_items')->onUpdate('restrict')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_facts_items', function (Blueprint $table) {
            $table->dropForeign('t_facts_items_fact_id_foreign');
            $table->dropForeign('t_facts_items_item_id_foreign');
        });
    }
};
