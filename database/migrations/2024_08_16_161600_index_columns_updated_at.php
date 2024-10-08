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
        Schema::table('ynh_osquery', function (Blueprint $table) {
            $table->index('updated_at');
        });
        Schema::table('ynh_the_cyber_brief', function (Blueprint $table) {
            $table->index('updated_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ynh_the_cyber_brief', function (Blueprint $table) {
            $table->dropIndex(['updated_at']);
        });
        Schema::table('ynh_osquery', function (Blueprint $table) {
            $table->dropIndex(['updated_at']);
        });
    }
};
