<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ynh_disk_usage', function (Blueprint $table) {

            $table->id();
            $table->timestamps();

            // Server
            $table->foreignId('ynh_server_id')->constrained()->cascadeOnDelete();

            // Metrics
            $table->dateTime('timestamp');
            $table->decimal('percent_available', 10, 2);
            $table->decimal('percent_used', 10, 2);
            $table->decimal('space_left_gb', 10, 2);
            $table->decimal('total_space_gb', 10, 2);
            $table->decimal('used_space_gb', 10, 2);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ynh_disk_usage');
    }
};
