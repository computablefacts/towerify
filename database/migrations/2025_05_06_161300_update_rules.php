<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('ynh_osquery_rules', function (Blueprint $table) {
            $table->index('name');
            $table->index('enabled');
            $table->index('score');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ynh_osquery_rules', function (Blueprint $table) {
            $table->dropIndex(['name']);
            $table->dropIndex(['enabled']);
            $table->dropIndex(['score']);
        });
    }
};
