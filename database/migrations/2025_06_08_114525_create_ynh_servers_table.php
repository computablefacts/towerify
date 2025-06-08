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
        Schema::create('ynh_servers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('version')->nullable();
            $table->string('ip_address')->nullable()->index();
            $table->integer('ssh_port')->nullable();
            $table->string('ssh_username')->nullable();
            $table->longText('ssh_public_key')->nullable();
            $table->longText('ssh_private_key')->nullable();
            $table->unsignedBigInteger('user_id')->nullable()->index('ynh_servers_user_id_foreign');
            $table->boolean('updated')->default(false);
            $table->timestamps();
            $table->boolean('is_ready')->default(false);
            $table->string('secret')->nullable()->index();
            $table->string('ip_address_v6')->nullable()->index();
            $table->boolean('is_frozen')->default(false);
            $table->boolean('added_with_curl')->default(false);
            $table->enum('platform', ['darwin', 'linux', 'posix', 'windows', 'ubuntu', 'centos', 'gentoo'])->default('linux');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ynh_servers');
    }
};
