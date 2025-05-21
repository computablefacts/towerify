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
        Schema::table('cb_prompts', function (Blueprint $table) {
            $table->string('template', 10000)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cb_prompts', function (Blueprint $table) {
            $table->string('template', 5000)->change();
        });
    }
};
