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
        Schema::create('ynh_users', function (Blueprint $table) {
            $table->id();
            $table->string('username');
            $table->string('fullname')->nullable();
            $table->string('email');
            $table->foreignId('ynh_server_id')->constrained();
            $table->boolean('updated')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ynh_users');
    }
};
