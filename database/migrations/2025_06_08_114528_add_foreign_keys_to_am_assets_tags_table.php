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
        Schema::table('am_assets_tags', function (Blueprint $table) {
            $table->foreign(['asset_id'])->references(['id'])->on('am_assets')->onUpdate('restrict')->onDelete('cascade');
            $table->foreign(['created_by'])->references(['id'])->on('users')->onUpdate('restrict')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('am_assets_tags', function (Blueprint $table) {
            $table->dropForeign('am_assets_tags_asset_id_foreign');
            $table->dropForeign('am_assets_tags_created_by_foreign');
        });
    }
};
