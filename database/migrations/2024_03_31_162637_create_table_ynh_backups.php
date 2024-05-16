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
        Schema::create('ynh_backups', function (Blueprint $table) {

            $table->id();
            $table->timestamps();

            // The user who triggered the backup
            $table->intOrBigIntBasedOnRelated('user_id', Schema::connection(null), 'users.id')->nullable()->nullOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->nullable()->nullOnDelete();;

            // The target server
            $table->foreignId('ynh_server_id')->constrained()->cascadeOnDelete();

            // The backups infos
            $table->string('name');
            $table->bigInteger('size');
            $table->string('storage_path')->nullable();
            $table->json('result');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ynh_backups');
    }
};
