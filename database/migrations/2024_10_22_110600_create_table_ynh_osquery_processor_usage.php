<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableYnhOsqueryProcessorUsage extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ynh_osquery_processor_usage', function (Blueprint $table) {

            $table->id();
            $table->timestamps();

            // The target server
            $table->foreignId('ynh_server_id')->constrained()->cascadeOnDelete();

            // The point in time
            $table->dateTime('timestamp');
            $table->index('timestamp');

            // The usage
            $table->decimal('system_workloads_pct', 10);
            $table->decimal('user_workloads_pct', 10);
            $table->decimal('idle_pct', 10);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ynh_osquery_processor_usage');
    }
}
