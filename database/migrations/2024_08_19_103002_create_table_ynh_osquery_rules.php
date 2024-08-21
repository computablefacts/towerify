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
        Schema::create('ynh_osquery_rules', function (Blueprint $table) {

            $table->id();
            $table->timestamps();

            // Query configuration
            // See https://osquery.readthedocs.io/en/stable/deployment/configuration/ for details
            $table->string('name');
            $table->string('description', 255);
            $table->string('query', 1000);
            $table->integer('interval')->default(3600);
            $table->boolean('removed')->default(true);
            $table->boolean('snapshot')->default(false);
            $table->enum('platform', [
                \App\Enums\OsqueryPlatformEnum::DARWIN->value,
                \App\Enums\OsqueryPlatformEnum::LINUX->value,
                \App\Enums\OsqueryPlatformEnum::POSIX->value,
                \App\Enums\OsqueryPlatformEnum::WINDOWS->value,
                \App\Enums\OsqueryPlatformEnum::UBUNTU->value,
                \App\Enums\OsqueryPlatformEnum::CENTOS->value,
                \App\Enums\OsqueryPlatformEnum::ALL->value,
            ])->default(\App\Enums\OsqueryPlatformEnum::ALL->value);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ynh_osquery_rules');
    }
};
