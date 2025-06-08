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
        Schema::create('ynh_users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('username');
            $table->string('fullname')->nullable();
            $table->string('email');
            $table->unsignedBigInteger('ynh_server_id')->index('ynh_users_ynh_server_id_foreign');
            $table->boolean('updated')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ynh_users');
    }
};
