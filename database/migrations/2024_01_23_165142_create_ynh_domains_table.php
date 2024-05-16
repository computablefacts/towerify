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
        Schema::create('ynh_domains', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->boolean('is_principal');
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
        Schema::dropIfExists('ynh_domains');
    }
};
