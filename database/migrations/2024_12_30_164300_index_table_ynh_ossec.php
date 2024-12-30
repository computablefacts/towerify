<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class IndexTableYnhOssec extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ynh_ossec_checks', function (Blueprint $table) {
            $table->fullText(['title', 'description', 'rationale', 'remediation'], 'ynh_ossec_checks_index_all');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ynh_ossec_checks', function (Blueprint $table) {
            $table->dropFullText('ynh_ossec_checks_index_all');
        });
    }
}
