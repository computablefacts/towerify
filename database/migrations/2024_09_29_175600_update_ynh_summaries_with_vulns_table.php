<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateYnhSummariesWithVulnsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ynh_summaries', function (Blueprint $table) {
            $table->integer('vulns_high')->default(0);
            $table->integer('vulns_high_unverified')->default(0);
            $table->integer('vulns_medium')->default(0);
            $table->integer('vulns_low')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ynh_summaries', function (Blueprint $table) {
            $table->dropColumn('vulns_high');
            $table->dropColumn('vulns_high_unverified');
            $table->dropColumn('vulns_medium');
            $table->dropColumn('vulns_low');
        });
    }
}
