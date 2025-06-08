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
        DB::statement("CREATE OR REPLACE VIEW `v_processes_with_open_network_sockets` AS select distinct `ynh_servers`.`user_id` AS `user_id`,`users`.`customer_id` AS `customer_id`,`users`.`tenant_id` AS `tenant_id`,`ynh_osquery`.`id` AS `event_id`,`ynh_osquery`.`ynh_server_id` AS `server_id`,`ynh_servers`.`name` AS `server_name`,`ynh_servers`.`ip_address` AS `server_ip_address`,`ynh_osquery`.`calendar_time` AS `timestamp`,json_unquote(json_extract(`ynh_osquery`.`columns`,'$.pid')) AS `pid`,json_unquote(json_extract(`ynh_osquery`.`columns`,'$.path')) AS `path`,json_unquote(json_extract(`ynh_osquery`.`columns`,'$.local_address')) AS `local_address`,json_unquote(json_extract(`ynh_osquery`.`columns`,'$.local_port')) AS `local_port`,json_unquote(json_extract(`ynh_osquery`.`columns`,'$.remote_address')) AS `remote_address`,json_unquote(json_extract(`ynh_osquery`.`columns`,'$.remote_port')) AS `remote_port`,`ynh_osquery`.`action` AS `action`,`ynh_osquery`.`name` AS `name2`,`ynh_osquery`.`columns_uid` AS `columns_uid` from ((((select `ynh_osquery_latest_events`.`ynh_osquery_id` AS `_oid`,`ynh_osquery_latest_events`.`ynh_server_id` AS `_sid` from `ynh_osquery_latest_events` where `ynh_osquery_latest_events`.`event_name` = 'open_sockets') `t` join `ynh_osquery` on(`ynh_osquery`.`id` = `t`.`_oid`)) join `ynh_servers` on(`ynh_servers`.`id` = `t`.`_sid`)) join `users` on(`users`.`id` = `ynh_servers`.`user_id`)) having trim(`path`) <> '' or trim(`remote_address`) <> ''");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("DROP VIEW IF EXISTS `v_processes_with_open_network_sockets`");
    }
};
