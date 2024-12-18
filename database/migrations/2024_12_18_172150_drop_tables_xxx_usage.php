<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class DropTablesXxxUsage extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('ynh_osquery_disk_usage');
        Schema::dropIfExists('ynh_osquery_memory_usage');
        Schema::dropIfExists('ynh_osquery_processor_usage');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // There is no going back!
    }
}
