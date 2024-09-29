<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateYnhSummariesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ynh_summaries', function (Blueprint $table) {
            $table->integer('monitored_ips')->default(0)->change();
            $table->integer('monitored_dns')->default(0)->change();
            $table->integer('collected_metrics')->default(0)->change();
            $table->integer('collected_events')->default(0)->change();
        });
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
