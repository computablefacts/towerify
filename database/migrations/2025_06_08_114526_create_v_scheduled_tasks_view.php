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
        DB::statement("CREATE OR REPLACE VIEW `v_scheduled_tasks` AS select `t`.`user_id` AS `user_id`,`t`.`customer_id` AS `customer_id`,`t`.`tenant_id` AS `tenant_id`,`t`.`event_id` AS `event_id`,`t`.`server_id` AS `server_id`,`t`.`server_name` AS `server_name`,`t`.`server_ip_address` AS `server_ip_address`,`t`.`timestamp` AS `timestamp`,`t`.`file` AS `file`,`t`.`command` AS `command`,`t`.`last_run_time` AS `last_run_time`,`t`.`next_run_time` AS `next_run_time`,`t`.`cron` AS `cron`,`t`.`enabled` AS `enabled`,`t`.`action` AS `action`,`t`.`name` AS `name`,`t`.`columns_uid` AS `columns_uid` from (select distinct `ynh_servers`.`user_id` AS `user_id`,`users`.`customer_id` AS `customer_id`,`users`.`tenant_id` AS `tenant_id`,`ynh_osquery`.`id` AS `event_id`,`ynh_osquery`.`ynh_server_id` AS `server_id`,`ynh_servers`.`name` AS `server_name`,`ynh_servers`.`ip_address` AS `server_ip_address`,`ynh_osquery`.`calendar_time` AS `timestamp`,case when `t`.`_type` = 1 then json_unquote(json_extract(`ynh_osquery`.`columns`,'$.path')) else 'n/a' end AS `file`,case when `t`.`_type` = 1 then json_unquote(json_extract(`ynh_osquery`.`columns`,'$.command')) else json_unquote(json_extract(`ynh_osquery`.`columns`,'$.action')) end AS `command`,case when `t`.`_type` = 1 then 'n/a' else date_format(from_unixtime(json_unquote(json_extract(`ynh_osquery`.`columns`,'$.last_run_time'))),'%Y-%m-%d %H:%i:%s') end AS `last_run_time`,case when `t`.`_type` = 1 then 'n/a' else date_format(from_unixtime(json_unquote(json_extract(`ynh_osquery`.`columns`,'$.next_run_time'))),'%Y-%m-%d %H:%i:%s') end AS `next_run_time`,case when `t`.`_type` = 1 then concat(json_unquote(json_extract(`ynh_osquery`.`columns`,'$.minute')),' ',json_unquote(json_extract(`ynh_osquery`.`columns`,'$.hour')),' ',json_unquote(json_extract(`ynh_osquery`.`columns`,'$.day_of_month')),' ',json_unquote(json_extract(`ynh_osquery`.`columns`,'$.month')),' ',json_unquote(json_extract(`ynh_osquery`.`columns`,'$.day_of_week'))) else 'n/a' end AS `cron`,case when `t`.`_type` = 1 then 'yes' when json_unquote(json_extract(`ynh_osquery`.`columns`,'$.enabled')) = 1 then 'yes' else 'no' end AS `enabled`,`ynh_osquery`.`action` AS `action`,`ynh_osquery`.`name` AS `name`,`ynh_osquery`.`columns_uid` AS `columns_uid` from ((((select 1 AS `_type`,`ynh_osquery_latest_events`.`ynh_osquery_id` AS `_oid`,`ynh_osquery_latest_events`.`ynh_server_id` AS `_sid` from `ynh_osquery_latest_events` where `ynh_osquery_latest_events`.`event_name` = 'crontab' union select 2 AS `_type`,`ynh_osquery_latest_events`.`ynh_osquery_id` AS `_oid`,`ynh_osquery_latest_events`.`ynh_server_id` AS `_sid` from `ynh_osquery_latest_events` where `ynh_osquery_latest_events`.`event_name` = 'scheduled_tasks') `t` join `ynh_osquery` on(`ynh_osquery`.`id` = `t`.`_oid`)) join `ynh_servers` on(`ynh_servers`.`id` = `t`.`_sid`)) join `users` on(`users`.`id` = `ynh_servers`.`user_id`))) `t` where `t`.`command`  not like '%performa%'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("DROP VIEW IF EXISTS `v_scheduled_tasks`");
    }
};
