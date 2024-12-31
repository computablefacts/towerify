<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnRuleToTableYnhOssec extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ynh_ossec_checks', function (Blueprint $table) {
            $table->string('rule', 1000);
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
            $table->dropColumn('rule');
        });
    }
}
