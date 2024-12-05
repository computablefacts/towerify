<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnIsIocToTableOsqueryRules extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ynh_osquery_rules', function (Blueprint $table) {
            $table->dropColumn(['value']);
            $table->boolean('is_ioc')->default(false);
            $table->double('score')->default(0.0);
            $table->string('query', 3000)->change();
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
            $table->string('query', 1000)->change();
            $table->dropColumn(['score']);
            $table->dropColumn(['is_ioc']);
            $table->string('value', 255)->nullable();
        });
    }
}
