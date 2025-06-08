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
        DB::statement("CREATE OR REPLACE VIEW `v_packages` AS select distinct `ynh_servers`.`user_id` AS `user_id`,`users`.`customer_id` AS `customer_id`,`users`.`tenant_id` AS `tenant_id`,`ynh_osquery`.`id` AS `event_id`,`ynh_osquery`.`ynh_server_id` AS `server_id`,`ynh_servers`.`name` AS `server_name`,`ynh_servers`.`ip_address` AS `server_ip_address`,`ynh_osquery`.`calendar_time` AS `timestamp`,case `t`.`_type` when 1 then 'win' when 2 then 'deb' when 3 then 'portage' when 4 then 'npm' when 5 then 'python' when 6 then 'rpm' when 7 then 'homebrew' when 8 then 'chocolatey' else 'n/a' end AS `type`,case `t`.`_type` when 1 then json_unquote(json_extract(`ynh_osquery`.`columns`,'$.name')) when 2 then json_unquote(json_extract(`ynh_osquery`.`columns`,'$.name')) when 3 then json_unquote(json_extract(`ynh_osquery`.`columns`,'$.name')) when 4 then json_unquote(json_extract(`ynh_osquery`.`columns`,'$.name')) when 5 then json_unquote(json_extract(`ynh_osquery`.`columns`,'$.name')) when 6 then json_unquote(json_extract(`ynh_osquery`.`columns`,'$.name')) when 7 then json_unquote(json_extract(`ynh_osquery`.`columns`,'$.name')) when 8 then json_unquote(json_extract(`ynh_osquery`.`columns`,'$.name')) else 'n/a' end AS `package`,case `t`.`_type` when 1 then json_unquote(json_extract(`ynh_osquery`.`columns`,'$.version')) when 2 then json_unquote(json_extract(`ynh_osquery`.`columns`,'$.version')) when 3 then json_unquote(json_extract(`ynh_osquery`.`columns`,'$.version')) when 4 then json_unquote(json_extract(`ynh_osquery`.`columns`,'$.version')) when 5 then json_unquote(json_extract(`ynh_osquery`.`columns`,'$.version')) when 6 then json_unquote(json_extract(`ynh_osquery`.`columns`,'$.version')) when 7 then json_unquote(json_extract(`ynh_osquery`.`columns`,'$.version')) when 8 then json_unquote(json_extract(`ynh_osquery`.`columns`,'$.version')) else 'n/a' end AS `version`,`ynh_osquery`.`action` AS `action`,`ynh_osquery`.`name` AS `name`,`ynh_osquery`.`columns_uid` AS `columns_uid` from ((((select 1 AS `_type`,`ynh_osquery_latest_events`.`ynh_osquery_id` AS `_oid`,`ynh_osquery_latest_events`.`ynh_server_id` AS `_sid` from `ynh_osquery_latest_events` where `ynh_osquery_latest_events`.`event_name` = 'win_packages' union select 2 AS `_type`,`ynh_osquery_latest_events`.`ynh_osquery_id` AS `_oid`,`ynh_osquery_latest_events`.`ynh_server_id` AS `_sid` from `ynh_osquery_latest_events` where `ynh_osquery_latest_events`.`event_name` = 'deb_packages' union select 3 AS `_type`,`ynh_osquery_latest_events`.`ynh_osquery_id` AS `_oid`,`ynh_osquery_latest_events`.`ynh_server_id` AS `_sid` from `ynh_osquery_latest_events` where `ynh_osquery_latest_events`.`event_name` = 'portage_packages' union select 4 AS `_type`,`ynh_osquery_latest_events`.`ynh_osquery_id` AS `_oid`,`ynh_osquery_latest_events`.`ynh_server_id` AS `_sid` from `ynh_osquery_latest_events` where `ynh_osquery_latest_events`.`event_name` = 'npm_packages' union select 5 AS `_type`,`ynh_osquery_latest_events`.`ynh_osquery_id` AS `_oid`,`ynh_osquery_latest_events`.`ynh_server_id` AS `_sid` from `ynh_osquery_latest_events` where `ynh_osquery_latest_events`.`event_name` = 'python_packages' union select 6 AS `_type`,`ynh_osquery_latest_events`.`ynh_osquery_id` AS `_oid`,`ynh_osquery_latest_events`.`ynh_server_id` AS `_sid` from `ynh_osquery_latest_events` where `ynh_osquery_latest_events`.`event_name` = 'rpm_packages' union select 7 AS `_type`,`ynh_osquery_latest_events`.`ynh_osquery_id` AS `_oid`,`ynh_osquery_latest_events`.`ynh_server_id` AS `_sid` from `ynh_osquery_latest_events` where `ynh_osquery_latest_events`.`event_name` = 'homebrew_packages' union select 8 AS `_type`,`ynh_osquery_latest_events`.`ynh_osquery_id` AS `_oid`,`ynh_osquery_latest_events`.`ynh_server_id` AS `_sid` from `ynh_osquery_latest_events` where `ynh_osquery_latest_events`.`event_name` = 'chocolatey_packages') `t` join `ynh_osquery` on(`ynh_osquery`.`id` = `t`.`_oid`)) join `ynh_servers` on(`ynh_servers`.`id` = `t`.`_sid`)) join `users` on(`users`.`id` = `ynh_servers`.`user_id`))");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("DROP VIEW IF EXISTS `v_packages`");
    }
};
