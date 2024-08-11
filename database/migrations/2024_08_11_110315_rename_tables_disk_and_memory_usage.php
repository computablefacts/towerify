<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::rename('ynh_disk_usage', 'ynh_osquery_disk_usage');
        Schema::rename('ynh_memory_usage', 'ynh_osquery_memory_usage');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::rename('ynh_osquery_disk_usage', 'ynh_disk_usage');
        Schema::rename('ynh_osquery_memory_usage', 'ynh_memory_usage');
    }
};
