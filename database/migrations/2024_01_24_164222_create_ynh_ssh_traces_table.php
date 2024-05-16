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
        Schema::create('ynh_ssh_traces', function (Blueprint $table) {

            $table->id();
            $table->timestamps();

            // The user who triggered the trace (if any)
            $table->intOrBigIntBasedOnRelated('user_id', Schema::connection(null), 'users.id')->nullable()->nullOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->nullable()->nullOnDelete();

            // The target server
            $table->foreignId('ynh_server_id')->constrained();

            // The trace information
            $table->string('uid');
            $table->integer('order');
            $table->enum('state', [
                \App\Enums\SshTraceStateEnum::PENDING->value,
                \App\Enums\SshTraceStateEnum::IN_PROGRESS->value,
                \App\Enums\SshTraceStateEnum::DONE->value,
                \App\Enums\SshTraceStateEnum::ERRORED->value,
            ]);
            $table->string('trace', 512);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ynh_ssh_traces');
    }
};
