<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnCompletedToTableTrials extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ynh_trials', function (Blueprint $table) {
            $table->boolean('completed')->default(false);
        });
        Schema::table('am_assets', function (Blueprint $table) {
            $table->foreignId('ynh_trial_id')->nullable()->constrained()->nullOnDelete();
        });
        Schema::table('cb_templates', function (Blueprint $table) {
            $table->dropUnique(['name']);
            $table->unique(['name', 'created_by']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ynh_trials', function (Blueprint $table) {
            $table->dropColumn('completed');
        });
        Schema::table('am_assets', function (Blueprint $table) {
            $table->dropForeign(['ynh_trial_id']);
            $table->dropColumn('ynh_trial_id');
        });
    }
}
