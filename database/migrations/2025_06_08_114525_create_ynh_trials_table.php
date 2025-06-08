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
        Schema::create('ynh_trials', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();
            $table->unsignedBigInteger('created_by')->nullable()->index('ynh_trials_created_by_foreign');
            $table->string('hash', 256)->index();
            $table->string('domain', 100)->nullable();
            $table->json('subdomains')->nullable();
            $table->boolean('honeypots')->default(false);
            $table->string('email', 100)->nullable();
            $table->boolean('completed')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ynh_trials');
    }
};
