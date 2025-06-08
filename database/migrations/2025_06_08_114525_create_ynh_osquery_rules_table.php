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
        Schema::create('ynh_osquery_rules', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();
            $table->string('name')->index();
            $table->string('description', 255);
            $table->string('query', 3000);
            $table->string('version', 10)->nullable();
            $table->integer('interval')->default(3600);
            $table->boolean('snapshot')->default(false);
            $table->enum('platform', ['darwin', 'linux', 'posix', 'windows', 'ubuntu', 'centos', 'gentoo', 'all'])->default('all');
            $table->string('category')->nullable();
            $table->boolean('enabled')->default(false)->index();
            $table->string('attck', 500)->nullable();
            $table->boolean('is_ioc')->default(false);
            $table->double('score')->default(0)->index();
            $table->string('comments', 1000)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ynh_osquery_rules');
    }
};
