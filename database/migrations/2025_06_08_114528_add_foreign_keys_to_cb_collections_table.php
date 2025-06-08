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
        Schema::table('cb_collections', function (Blueprint $table) {
            $table->foreign(['created_by'], 'cb_chunks_collections_created_by_foreign')->references(['id'])->on('users')->onUpdate('restrict')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cb_collections', function (Blueprint $table) {
            $table->dropForeign('cb_chunks_collections_created_by_foreign');
        });
    }
};
