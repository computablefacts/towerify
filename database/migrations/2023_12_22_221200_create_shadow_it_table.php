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
        Schema::create('tw_yunohost_shadow_it', function (Blueprint $table) {

            $table->id();
            $table->timestamps();
            $table->string('url');
            $table->boolean('is_visible')->default(false);

            // The associated client
            $table->intOrBigIntBasedOnRelated('client_id', Schema::connection(null), 'tw_clients.id')->cascadeOnDelete();
            $table->foreign('client_id')->references('id')->on('tw_clients')->cascadeOnDelete();

            // The server this app is linked to
            $table->intOrBigIntBasedOnRelated('tw_yunohost_servers_id', Schema::connection(null), 'tw_yunohost_servers.id')->nullable()->cascadeOnDelete();
            $table->foreign('tw_yunohost_servers_id')->references('id')->on('tw_yunohost_servers')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tw_yunohost_shadow_it');
    }
};
