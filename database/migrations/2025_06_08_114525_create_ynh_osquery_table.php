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
        Schema::create('ynh_osquery', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable()->index();
            $table->unsignedBigInteger('ynh_server_id')->index('ynh_osquery_ynh_server_id_foreign');
            $table->bigInteger('row');
            $table->string('name')->index();
            $table->string('host_identifier');
            $table->dateTime('calendar_time')->index();
            $table->bigInteger('unix_time');
            $table->bigInteger('epoch');
            $table->bigInteger('counter');
            $table->boolean('numerics');
            $table->json('columns');
            $table->string('action');
            $table->boolean('packed')->default(true);
            $table->boolean('dismissed')->default(false);
            $table->string('columns_uid')->nullable()->index();
            $table->unsignedBigInteger('ynh_osquery_rule_id')->nullable()->index('ynh_osquery_ynh_osquery_rule_id_foreign');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ynh_osquery');
    }
};
