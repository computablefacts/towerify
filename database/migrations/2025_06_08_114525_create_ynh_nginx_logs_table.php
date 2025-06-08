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
        Schema::create('ynh_nginx_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();
            $table->boolean('updated')->default(false);
            $table->unsignedBigInteger('from_ynh_server_id')->nullable()->index('ynh_nginx_logs_from_ynh_server_id_foreign');
            $table->unsignedBigInteger('to_ynh_server_id')->nullable()->index('ynh_nginx_logs_to_ynh_server_id_foreign');
            $table->string('from_ip_address');
            $table->string('to_ip_address');
            $table->string('service', 256);
            $table->bigInteger('weight');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ynh_nginx_logs');
    }
};
