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
        Schema::create('tw_yunohost_permissions', function (Blueprint $table) {

            $table->id();
            $table->timestamps();
            $table->string('permission');

            // The associated user
            $table->intOrBigIntBasedOnRelated('user_id', Schema::connection(null), 'users.id')->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();

            // The associated YunoHost application
            $table->intOrBigIntBasedOnRelated('tw_yunohost_applications_id', Schema::connection(null), 'tw_yunohost_applications.id')->cascadeOnDelete();
            $table->foreign('tw_yunohost_applications_id')->references('id')->on('tw_yunohost_applications')->cascadeOnDelete();

            // The associated client
            $table->intOrBigIntBasedOnRelated('client_id', Schema::connection(null), 'tw_clients.id')->cascadeOnDelete();
            $table->foreign('client_id')->references('id')->on('tw_clients')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tw_yunohost_permissions');
    }
};
