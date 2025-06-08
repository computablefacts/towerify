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
        Schema::create('cb_scheduled_tasks', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();
            $table->unsignedBigInteger('created_by')->index('cb_scheduled_tasks_created_by_foreign');
            $table->string('name', 500)->nullable();
            $table->string('cron', 100);
            $table->string('task', 10000);
            $table->dateTime('prev_run_date')->nullable();
            $table->dateTime('next_run_date')->nullable()->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cb_scheduled_tasks');
    }
};
