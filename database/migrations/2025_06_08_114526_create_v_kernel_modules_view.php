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
        DB::statement("CREATE OR REPLACE VIEW `v_kernel_modules` AS select distinct `ynh_servers`.`user_id` AS `user_id`,`users`.`customer_id` AS `customer_id`,`users`.`tenant_id` AS `tenant_id`,`ynh_osquery`.`id` AS `event_id`,`ynh_osquery`.`ynh_server_id` AS `server_id`,`ynh_servers`.`name` AS `server_name`,`ynh_servers`.`ip_address` AS `server_ip_address`,`ynh_osquery`.`calendar_time` AS `timestamp`,json_unquote(json_extract(`ynh_osquery`.`columns`,'$.name')) AS `name`,`ynh_osquery`.`action` AS `action`,`ynh_osquery`.`name` AS `ynh_osquery_name`,`ynh_osquery`.`columns_uid` AS `columns_uid` from ((((select `ynh_osquery_latest_events`.`ynh_osquery_id` AS `_oid`,`ynh_osquery_latest_events`.`ynh_server_id` AS `_sid` from `ynh_osquery_latest_events` where `ynh_osquery_latest_events`.`event_name` = 'kernel_modules') `t` join `ynh_osquery` on(`ynh_osquery`.`id` = `t`.`_oid`)) join `ynh_servers` on(`ynh_servers`.`id` = `t`.`_sid`)) join `users` on(`users`.`id` = `ynh_servers`.`user_id`))");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("DROP VIEW IF EXISTS `v_kernel_modules`");
    }
};
