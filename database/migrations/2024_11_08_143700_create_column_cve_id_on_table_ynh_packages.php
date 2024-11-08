<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateColumnCveIdOnTableYnhPackages extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ynh_osquery_packages', function (Blueprint $table) {
            $table->intOrBigIntBasedOnRelated('ynh_cve_id', Schema::connection(null), 'ynh_cves.id')->nullable()->nullOnDelete();
            $table->foreign('ynh_cve_id')->references('id')->on('ynh_cves')->nullable()->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ynh_osquery_packages', function (Blueprint $table) {
            $table->dropForeign(['ynh_cve_id']);
            $table->dropColumn('ynh_cve_id');
        });
    }
}
