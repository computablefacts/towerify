<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("
            CREATE OR REPLACE VIEW v_scheduled_tasks AS
            SELECT * FROM (
                SELECT DISTINCT
                  ynh_servers.user_id,
                  users.customer_id,
                  users.tenant_id,
                  ynh_osquery.id AS event_id,
                  ynh_osquery.ynh_server_id AS server_id,
                  ynh_servers.name AS server_name,
                  ynh_servers.ip_address AS server_ip_address,
                  ynh_osquery.calendar_time AS timestamp,
                  CASE WHEN _type = 1 THEN json_unquote(json_extract(ynh_osquery.columns, '$.path')) ELSE 'n/a' END AS file,
                  CASE WHEN _type = 1 THEN json_unquote(json_extract(ynh_osquery.columns, '$.command')) ELSE json_unquote(json_extract(ynh_osquery.columns, '$.action')) END AS command,
                  CASE WHEN _type = 1 THEN 'n/a' ELSE DATE_FORMAT(FROM_UNIXTIME(json_unquote(json_extract(ynh_osquery.columns, '$.last_run_time'))), '%Y-%m-%d %H:%i:%s') END AS last_run_time,
                  CASE WHEN _type = 1 THEN 'n/a' ELSE DATE_FORMAT(FROM_UNIXTIME(json_unquote(json_extract(ynh_osquery.columns, '$.next_run_time'))), '%Y-%m-%d %H:%i:%s') END AS next_run_time,
                  CASE WHEN _type = 1 THEN CONCAT(
                    json_unquote(json_extract(ynh_osquery.columns, '$.minute')), ' ', 
                    json_unquote(json_extract(ynh_osquery.columns, '$.hour')), ' ',
                    json_unquote(json_extract(ynh_osquery.columns, '$.day_of_month')), ' ',
                    json_unquote(json_extract(ynh_osquery.columns, '$.month')), ' ',
                    json_unquote(json_extract(ynh_osquery.columns, '$.day_of_week'))
                  ) ELSE 'n/a' END AS cron,
                  CASE 
                    WHEN _type = 1 THEN 'yes' 
                    WHEN json_unquote(json_extract(ynh_osquery.columns, '$.enabled')) = 1 THEN 'yes' 
                    ELSE 'no'
                  END AS enabled,
                  ynh_osquery.action,
                  ynh_osquery.name,
                  ynh_osquery.columns_uid
                FROM (
                  SELECT
                    1 AS _type,
                    ynh_osquery_id AS _oid,
                    ynh_server_id AS _sid
                  FROM ynh_osquery_latest_events
                  WHERE event_name = 'crontab'
                  
                  UNION DISTINCT
                  
                  SELECT
                    2 AS _type,
                    ynh_osquery_id AS _oid,
                    ynh_server_id AS _sid
                  FROM ynh_osquery_latest_events
                  WHERE event_name = 'scheduled_tasks'
                ) AS t
                INNER JOIN ynh_osquery ON ynh_osquery.id = t._oid
                INNER JOIN ynh_servers ON ynh_servers.id = t._sid
                INNER JOIN users ON users.id = ynh_servers.user_id
            ) AS t
            WHERE t.command NOT LIKE '%performa%'
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // There is no going back!
    }
};
