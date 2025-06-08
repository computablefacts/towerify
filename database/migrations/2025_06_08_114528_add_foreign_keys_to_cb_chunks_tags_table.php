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
        Schema::table('cb_chunks_tags', function (Blueprint $table) {
            $table->foreign(['chunk_id'])->references(['id'])->on('cb_chunks')->onUpdate('restrict')->onDelete('cascade');
            $table->foreign(['created_by'])->references(['id'])->on('users')->onUpdate('restrict')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cb_chunks_tags', function (Blueprint $table) {
            $table->dropForeign('cb_chunks_tags_chunk_id_foreign');
            $table->dropForeign('cb_chunks_tags_created_by_foreign');
        });
    }
};
