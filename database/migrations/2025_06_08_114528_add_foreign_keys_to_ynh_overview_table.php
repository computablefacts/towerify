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
        Schema::table('ynh_overview', function (Blueprint $table) {
            $table->foreign(['created_by'], 'ynh_summaries_created_by_foreign')->references(['id'])->on('users')->onUpdate('restrict')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ynh_overview', function (Blueprint $table) {
            $table->dropForeign('ynh_summaries_created_by_foreign');
        });
    }
};
