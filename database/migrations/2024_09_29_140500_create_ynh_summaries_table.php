<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateYnhSummariesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ynh_summaries', function (Blueprint $table) {

            $table->id();
            $table->timestamps();

            // Scope summaries
            $table->intOrBigIntBasedOnRelated('created_by', Schema::connection(null), 'users.id')->cascadeOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->cascadeOnDelete();

            // The summaries properties
            $table->integer('monitored_ips');
            $table->integer('monitored_dns');
            $table->integer('collected_metrics');
            $table->integer('collected_events');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ynh_summaries');
    }
}
