<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class IndexColumnAssetInTableAssets2 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('am_assets', function (Blueprint $table) {
            $table->dropUnique(['asset', 'created_by']);
            $table->unique(['asset', 'created_by', 'ynh_trial_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('am_assets', function (Blueprint $table) {
            $table->dropUnique(['asset', 'created_by', 'ynh_trial_id']);
        });
    }
}
