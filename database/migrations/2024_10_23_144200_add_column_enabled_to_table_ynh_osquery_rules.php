<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnEnabledToTableYnhOsqueryRules extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ynh_osquery_rules', function (Blueprint $table) {
            $table->boolean('enabled')->default(false);
            $table->string('attck', 500)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ynh_osquery_rules', function (Blueprint $table) {
            $table->dropColumn('attck');
            $table->dropColumn(['enabled']);
        });
    }
}
