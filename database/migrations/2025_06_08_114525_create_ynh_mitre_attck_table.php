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
        Schema::create('ynh_mitre_attck', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();
            $table->string('uid')->index();
            $table->string('title');
            $table->json('tactics');
            $table->string('description', 3000);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ynh_mitre_attck');
    }
};
