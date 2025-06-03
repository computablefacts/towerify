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
        Schema::table('ynh_osquery', function (Blueprint $table) {
            $table->foreignId('ynh_osquery_rule_id')->nullable()->constrained()->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ynh_osquery', function (Blueprint $table) {
            $table->dropColumn('ynh_osquery_rule_id');
        });
    }
};
