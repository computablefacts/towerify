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
        Schema::create('ynh_backups', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();
            $table->unsignedBigInteger('user_id')->nullable()->index('ynh_backups_user_id_foreign');
            $table->unsignedBigInteger('ynh_server_id')->index('ynh_backups_ynh_server_id_foreign');
            $table->string('name');
            $table->bigInteger('size');
            $table->string('storage_path')->nullable();
            $table->json('result');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ynh_backups');
    }
};
