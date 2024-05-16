<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tw_yunohost_applications', function (Blueprint $table) {
            $table->unsignedInteger('tw_yunohost_servers_id')->nullable();
            $table->foreign('tw_yunohost_servers_id')
                ->references('id')->on('tw_yunohost_servers')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tw_yunohost_applications', function (Blueprint $table) {
            $table->dropForeign(['tw_yunohost_servers_id']);
            $table->dropColumn('tw_yunohost_servers_id');
        });
    }
};
