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
        Schema::create('ynh_ssh_traces', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();
            $table->unsignedBigInteger('user_id')->nullable()->index('ynh_ssh_traces_user_id_foreign');
            $table->unsignedBigInteger('ynh_server_id')->index('ynh_ssh_traces_ynh_server_id_foreign');
            $table->string('uid');
            $table->integer('order');
            $table->enum('state', ['pending', 'in_progress', 'done', 'errored']);
            $table->string('trace', 512);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ynh_ssh_traces');
    }
};
