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
        Schema::table('cb_chunks', function (Blueprint $table) {
            $table->foreign(['collection_id'])->references(['id'])->on('cb_collections')->onUpdate('restrict')->onDelete('cascade');
            $table->foreign(['created_by'])->references(['id'])->on('users')->onUpdate('restrict')->onDelete('cascade');
            $table->foreign(['file_id'])->references(['id'])->on('cb_files')->onUpdate('restrict')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cb_chunks', function (Blueprint $table) {
            $table->dropForeign('cb_chunks_collection_id_foreign');
            $table->dropForeign('cb_chunks_created_by_foreign');
            $table->dropForeign('cb_chunks_file_id_foreign');
        });
    }
};
