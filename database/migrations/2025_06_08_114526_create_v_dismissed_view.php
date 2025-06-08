<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("CREATE OR REPLACE VIEW `v_dismissed` AS select `ynh_osquery`.`id` AS `id`,`ynh_osquery`.`ynh_server_id` AS `ynh_server_id`,`ynh_osquery`.`name` AS `name`,`ynh_osquery`.`action` AS `action`,`ynh_osquery`.`columns_uid` AS `columns_uid`,`ynh_osquery`.`calendar_time` AS `calendar_time` from `ynh_osquery` where `ynh_osquery`.`dismissed` = 1");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("DROP VIEW IF EXISTS `v_dismissed`");
    }
};
