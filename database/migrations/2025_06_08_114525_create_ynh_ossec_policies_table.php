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
        Schema::create('ynh_ossec_policies', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();
            $table->string('uid')->index();
            $table->string('name');
            $table->string('description', 1000);
            $table->json('references');
            $table->json('requirements');

            $table->unique(['uid']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ynh_ossec_policies');
    }
};
