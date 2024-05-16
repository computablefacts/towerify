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
        Schema::create('tw_yunohost_ssh_traces', function (Blueprint $table) {

            $table->id();
            $table->timestamps();

            // The user who triggered the trace
            $table->intOrBigIntBasedOnRelated('user_id', Schema::connection(null), 'users.id')->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();

            // The server this trace is linked to
            $table->intOrBigIntBasedOnRelated('tw_yunohost_servers_id', Schema::connection(null), 'tw_yunohost_servers.id')->cascadeOnDelete();
            $table->foreign('tw_yunohost_servers_id')->references('id')->on('tw_yunohost_servers')->cascadeOnDelete();

            // The application this trace is linked to (if any)
            $table->intOrBigIntBasedOnRelated('tw_yunohost_applications_id', Schema::connection(null), 'tw_yunohost_applications.id')->nullable()->cascadeOnDelete();
            $table->foreign('tw_yunohost_applications_id')->references('id')->on('tw_yunohost_applications')->cascadeOnDelete();

            // The trace information
            $table->string('uid');
            $table->integer('order');
            $table->enum('state', [
                \App\Enums\SshTraceStateEnum::PENDING->value,
                \App\Enums\SshTraceStateEnum::IN_PROGRESS->value,
                \App\Enums\SshTraceStateEnum::DONE->value,
                \App\Enums\SshTraceStateEnum::ERRORED->value,
            ]);
            $table->string('trace');
        });
        Schema::table('users', function (Blueprint $table) {
            $table->string('am_api_token')->default(null);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('am_api_token');
        });
        Schema::dropIfExists('tw_yunohost_ssh_traces');
    }
};
