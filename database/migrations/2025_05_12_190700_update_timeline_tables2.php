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
        Schema::table('t_facts', function (Blueprint $table) {
            $table->string('value', 5000)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_facts', function (Blueprint $table) {
            $table->string('value', 1000)->nullable()->change();
        });
    }
};
