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
        Schema::create('ynh_permissions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->unsignedBigInteger('ynh_user_id')->index('ynh_permissions_ynh_user_id_foreign');
            $table->unsignedBigInteger('ynh_application_id')->index('ynh_permissions_ynh_application_id_foreign');
            $table->boolean('updated')->default(false);
            $table->timestamps();
            $table->boolean('is_visitors')->default(false);
            $table->boolean('is_all_users')->default(false);
            $table->boolean('is_user_specific')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ynh_permissions');
    }
};
