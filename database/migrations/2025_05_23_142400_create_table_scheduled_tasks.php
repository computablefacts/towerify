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
        Schema::create('cb_scheduled_tasks', function (Blueprint $table) {

            $table->id();
            $table->timestamps();

            // The item owner
            $table->intOrBigIntBasedOnRelated('created_by', Schema::connection(null), 'users.id');
            $table->foreign('created_by')->references('id')->on('users')->cascadeOnDelete();

            // Properties
            $table->string('name', 500)->nullable();
            $table->string('cron', 100);
            $table->string('task', 10000);
            $table->dateTime('prev_run_date')->nullable();
            $table->dateTime('next_run_date')->nullable();

            // Indexes
            $table->index('next_run_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::drop('cb_scheduled_tasks');
    }
};
