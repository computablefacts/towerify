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
        DB::statement("CREATE OR REPLACE VIEW `v_authorized_keys` AS select distinct `ynh_servers`.`user_id` AS `user_id`,`users`.`customer_id` AS `customer_id`,`users`.`tenant_id` AS `tenant_id`,`ynh_osquery`.`id` AS `event_id`,`ynh_osquery`.`ynh_server_id` AS `server_id`,`ynh_servers`.`name` AS `server_name`,`ynh_servers`.`ip_address` AS `server_ip_address`,`ynh_osquery`.`calendar_time` AS `timestamp`,json_unquote(json_extract(`ynh_osquery`.`columns`,'$.key_file')) AS `key_file`,concat(left(json_unquote(json_extract(`ynh_osquery`.`columns`,'$.key')),15),'[...]',right(json_unquote(json_extract(`ynh_osquery`.`columns`,'$.key')),15)) AS `key`,case when json_unquote(json_extract(`ynh_osquery`.`columns`,'$.comment')) = 'null' then NULL else json_unquote(json_extract(`ynh_osquery`.`columns`,'$.comment')) end AS `key_comment`,json_unquote(json_extract(`ynh_osquery`.`columns`,'$.algorithm')) AS `algorithm`,`ynh_osquery`.`action` AS `action`,`ynh_osquery`.`name` AS `name`,`ynh_osquery`.`columns_uid` AS `columns_uid` from ((`ynh_osquery` join `ynh_servers` on(`ynh_servers`.`id` = `ynh_osquery`.`ynh_server_id`)) join `users` on(`users`.`id` = `ynh_servers`.`user_id`)) where `ynh_osquery`.`name` = 'authorized_keys'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("DROP VIEW IF EXISTS `v_authorized_keys`");
    }
};
