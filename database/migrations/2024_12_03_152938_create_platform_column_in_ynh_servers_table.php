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
        Schema::table('ynh_servers', function (Blueprint $table) {
            $table->enum('platform', [
                \App\Enums\OsqueryPlatformEnum::DARWIN->value,
                \App\Enums\OsqueryPlatformEnum::LINUX->value,
                \App\Enums\OsqueryPlatformEnum::POSIX->value,
                \App\Enums\OsqueryPlatformEnum::WINDOWS->value,
                \App\Enums\OsqueryPlatformEnum::UBUNTU->value,
                \App\Enums\OsqueryPlatformEnum::CENTOS->value,
                \App\Enums\OsqueryPlatformEnum::GENTOO->value,
            ])->default(\App\Enums\OsqueryPlatformEnum::LINUX->value);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ynh_servers', function (Blueprint $table) {
            $table->dropColumn('platform');
        });
    }
};
