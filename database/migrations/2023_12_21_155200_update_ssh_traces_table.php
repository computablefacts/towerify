<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tw_yunohost_ssh_traces', function (Blueprint $table) {

            $table->dropForeign(['tw_yunohost_servers_id']);
            $table->dropColumn('tw_yunohost_servers_id');

            $table->dropForeign(['tw_yunohost_applications_id']);
            $table->dropColumn('tw_yunohost_applications_id');

            $table->string('ip');
            $table->integer('port');
            $table->string('username');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tw_yunohost_ssh_traces', function (Blueprint $table) {

            $table->dropColumn('ip');
            $table->dropColumn('port');
            $table->dropColumn('username');

            // The server this trace is linked to
            $table->intOrBigIntBasedOnRelated('tw_yunohost_servers_id', Schema::connection(null), 'tw_yunohost_servers.id')->cascadeOnDelete();
            $table->foreign('tw_yunohost_servers_id')->references('id')->on('tw_yunohost_servers')->cascadeOnDelete();

            // The application this trace is linked to (if any)
            $table->intOrBigIntBasedOnRelated('tw_yunohost_applications_id', Schema::connection(null), 'tw_yunohost_applications.id')->nullable()->cascadeOnDelete();
            $table->foreign('tw_yunohost_applications_id')->references('id')->on('tw_yunohost_applications')->cascadeOnDelete();
        });
    }
};
