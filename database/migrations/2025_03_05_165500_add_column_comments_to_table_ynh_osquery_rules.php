<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnCommentsToTableYnhOsqueryRules extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ynh_osquery_rules', function (Blueprint $table) {
            $table->string('comments', 1000)->nullable();
            $table->dropColumn('removed');
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
            $table->boolean('removed')->default(true);
            $table->dropColumn('comments');
        });
    }
}
