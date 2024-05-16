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
        Schema::create('ynh_nginx_logs', function (Blueprint $table) {

            $table->id();
            $table->timestamps();
            $table->boolean('updated')->default(false);

            // The source & target servers as ids (if available)
            $table->intOrBigIntBasedOnRelated('from_ynh_server_id', Schema::connection(null), 'ynh_servers.id')->nullable()->onDeleteCascade();
            $table->foreign('from_ynh_server_id')->references('id')->on('ynh_servers')->nullable()->onDeleteCascade();

            $table->intOrBigIntBasedOnRelated('to_ynh_server_id', Schema::connection(null), 'ynh_servers.id')->nullable()->onDeleteCascade();
            $table->foreign('to_ynh_server_id')->references('id')->on('ynh_servers')->nullable()->onDeleteCascade();

            // The source & target servers as ip addresses
            $table->string('from_ip_address');
            $table->string('to_ip_address');

            // The service hit
            $table->string('service', 256);

            // A weight i.e. the number of time source hit target
            $table->bigInteger('weight');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ynh_nginx_logs');
    }
};
