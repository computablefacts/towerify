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
            CREATE OR REPLACE VIEW v_etc_hosts AS
            SELECT DISTINCT
              ynh_servers.user_id,
              users.customer_id,
              users.tenant_id,
              ynh_osquery.id AS event_id,
              ynh_osquery.ynh_server_id AS server_id,
              ynh_servers.name AS server_name,
              ynh_servers.ip_address AS server_ip_address,
              ynh_osquery.calendar_time AS timestamp,
              json_unquote(json_extract(ynh_osquery.columns, '$.address')) AS address,
              json_unquote(json_extract(ynh_osquery.columns, '$.hostnames')) AS hostnames,
              ynh_osquery.action,
              ynh_osquery.name,
              ynh_osquery.columns_uid
            FROM (
              SELECT
                ynh_osquery_id AS _oid,
                ynh_server_id AS _sid
              FROM ynh_osquery_latest_events
              WHERE event_name = 'etc_hosts'
            ) AS t
            INNER JOIN ynh_osquery ON ynh_osquery.id = t._oid
            INNER JOIN ynh_servers ON ynh_servers.id = t._sid
            INNER JOIN users ON users.id = ynh_servers.user_id
        ");
        DB::statement("
            CREATE OR REPLACE VIEW v_etc_services AS
            SELECT DISTINCT
              ynh_servers.user_id,
              users.customer_id,
              users.tenant_id,
              ynh_osquery.id AS event_id,
              ynh_osquery.ynh_server_id AS server_id,
              ynh_servers.name AS server_name,
              ynh_servers.ip_address AS server_ip_address,
              ynh_osquery.calendar_time AS timestamp,
              json_unquote(json_extract(ynh_osquery.columns, '$.name')) AS name,
              json_unquote(json_extract(ynh_osquery.columns, '$.port')) AS port,
              json_unquote(json_extract(ynh_osquery.columns, '$.protocol')) AS protocol,
              json_unquote(json_extract(ynh_osquery.columns, '$.comment')) AS comment,
              ynh_osquery.action,
              ynh_osquery.name AS name2,
              ynh_osquery.columns_uid
            FROM (
              SELECT
                ynh_osquery_id AS _oid,
                ynh_server_id AS _sid
              FROM ynh_osquery_latest_events
              WHERE event_name = 'etc_services'
            ) AS t
            INNER JOIN ynh_osquery ON ynh_osquery.id = t._oid
            INNER JOIN ynh_servers ON ynh_servers.id = t._sid
            INNER JOIN users ON users.id = ynh_servers.user_id
        ");
        DB::statement("
            CREATE OR REPLACE VIEW v_network_interfaces AS
            SELECT DISTINCT
              ynh_servers.user_id,
              users.customer_id,
              users.tenant_id,
              ynh_osquery.id AS event_id,
              ynh_osquery.ynh_server_id AS server_id,
              ynh_servers.name AS server_name,
              ynh_servers.ip_address AS server_ip_address,
              ynh_osquery.calendar_time AS timestamp,
              json_unquote(json_extract(ynh_osquery.columns, '$.address')) AS address,
              json_unquote(json_extract(ynh_osquery.columns, '$.broadcast')) AS broadcast,
              json_unquote(json_extract(ynh_osquery.columns, '$.interface')) AS interface,
              json_unquote(json_extract(ynh_osquery.columns, '$.mask')) AS mask,
              json_unquote(json_extract(ynh_osquery.columns, '$.point_to_point')) AS point_to_point,
              json_unquote(json_extract(ynh_osquery.columns, '$.type')) AS type,
              ynh_osquery.action,
              ynh_osquery.name,
              ynh_osquery.columns_uid
            FROM (
              SELECT
                ynh_osquery_id AS _oid,
                ynh_server_id AS _sid
              FROM ynh_osquery_latest_events
              WHERE event_name = 'interface_addresses'
            ) AS t
            INNER JOIN ynh_osquery ON ynh_osquery.id = t._oid
            INNER JOIN ynh_servers ON ynh_servers.id = t._sid
            INNER JOIN users ON users.id = ynh_servers.user_id
        ");
        DB::statement("
            CREATE OR REPLACE VIEW v_startup_items AS
            SELECT DISTINCT
              ynh_servers.user_id,
              users.customer_id,
              users.tenant_id,
              ynh_osquery.id AS event_id,
              ynh_osquery.ynh_server_id AS server_id,
              ynh_servers.name AS server_name,
              ynh_servers.ip_address AS server_ip_address,
              ynh_osquery.calendar_time AS timestamp,
              json_unquote(json_extract(ynh_osquery.columns, '$.name')) AS name,
              json_unquote(json_extract(ynh_osquery.columns, '$.type')) AS type,
              json_unquote(json_extract(ynh_osquery.columns, '$.status')) AS status,
              json_unquote(json_extract(ynh_osquery.columns, '$.path')) AS path,
              ynh_osquery.action,
              ynh_osquery.name AS name2,
              ynh_osquery.columns_uid
            FROM (
              SELECT
                ynh_osquery_id AS _oid,
                ynh_server_id AS _sid
              FROM ynh_osquery_latest_events
              WHERE event_name = 'startup_items'
            ) AS t
            INNER JOIN ynh_osquery ON ynh_osquery.id = t._oid
            INNER JOIN ynh_servers ON ynh_servers.id = t._sid
            INNER JOIN users ON users.id = ynh_servers.user_id
        ");
        DB::statement("
            CREATE OR REPLACE VIEW v_shell_history AS
            SELECT DISTINCT
              ynh_servers.user_id,
              users.customer_id,
              users.tenant_id,
              ynh_osquery.id AS event_id,
              ynh_osquery.ynh_server_id AS server_id,
              ynh_servers.name AS server_name,
              ynh_servers.ip_address AS server_ip_address,
              ynh_osquery.calendar_time AS timestamp,
              json_unquote(json_extract(ynh_osquery.columns, '$.username')) AS username,
              json_unquote(json_extract(ynh_osquery.columns, '$.shell')) AS shell,
              json_unquote(json_extract(ynh_osquery.columns, '$.command')) AS command,
              ynh_osquery.action,
              ynh_osquery.name,
              ynh_osquery.columns_uid
            FROM (
              SELECT
                ynh_osquery_id AS _oid,
                ynh_server_id AS _sid
              FROM ynh_osquery_latest_events
              WHERE event_name = 'shell_history'
            ) AS t
            INNER JOIN ynh_osquery ON ynh_osquery.id = t._oid
            INNER JOIN ynh_servers ON ynh_servers.id = t._sid
            INNER JOIN users ON users.id = ynh_servers.user_id
        ");
        DB::statement("
            CREATE OR REPLACE VIEW v_services AS
            SELECT DISTINCT
              ynh_servers.user_id,
              users.customer_id,
              users.tenant_id,
              ynh_osquery.id AS event_id,
              ynh_osquery.ynh_server_id AS server_id,
              ynh_servers.name AS server_name,
              ynh_servers.ip_address AS server_ip_address,
              ynh_osquery.calendar_time AS timestamp,
              CASE WHEN _type = 1 THEN json_unquote(json_extract(ynh_osquery.columns, '$.name')) ELSE json_unquote(json_extract(ynh_osquery.columns, '$.name')) END AS name,
              CASE WHEN _type = 1 THEN json_unquote(json_extract(ynh_osquery.columns, '$.path')) ELSE json_unquote(json_extract(ynh_osquery.columns, '$.path')) END AS path,
              CASE WHEN _type = 1 THEN json_unquote(json_extract(ynh_osquery.columns, '$.type')) ELSE json_unquote(json_extract(ynh_osquery.columns, '$.service_type')) END AS type,
              CASE WHEN _type = 1 THEN json_unquote(json_extract(ynh_osquery.columns, '$.status')) ELSE json_unquote(json_extract(ynh_osquery.columns, '$.status')) END AS status,
              CASE WHEN _type = 1 THEN json_unquote(json_extract(ynh_osquery.columns, '$.username')) ELSE json_unquote(json_extract(ynh_osquery.columns, '$.user_account')) END AS user,
              ynh_osquery.action,
              ynh_osquery.name AS name2,
              ynh_osquery.columns_uid
            FROM (
              SELECT 
                1 AS _type,
                ynh_osquery_id AS _oid,
                ynh_server_id AS _sid
              FROM ynh_osquery_latest_events
              WHERE event_name = 'startup_items'
              
              UNION DISTINCT
              
              SELECT 
                2 AS _type,
                ynh_osquery_id AS _oid,
                ynh_server_id AS _sid
              FROM ynh_osquery_latest_events
              WHERE event_name = 'services'
            ) AS t
            INNER JOIN ynh_osquery ON ynh_osquery.id = t._oid
            INNER JOIN ynh_servers ON ynh_servers.id = t._sid
            INNER JOIN users ON users.id = ynh_servers.user_id
        ");
        DB::statement("
            CREATE OR REPLACE VIEW v_scheduled_tasks AS
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
        ");
        DB::statement("
            CREATE OR REPLACE VIEW v_processes_with_open_network_sockets AS
            SELECT DISTINCT
              ynh_servers.user_id,
              users.customer_id,
              users.tenant_id,
              ynh_osquery.id AS event_id,
              ynh_osquery.ynh_server_id AS server_id,
              ynh_servers.name AS server_name,
              ynh_servers.ip_address AS server_ip_address,
              ynh_osquery.calendar_time AS timestamp,
              json_unquote(json_extract(ynh_osquery.columns, '$.pid')) AS pid,
              json_unquote(json_extract(ynh_osquery.columns, '$.path')) AS path,
              json_unquote(json_extract(ynh_osquery.columns, '$.local_address')) AS local_address,
              json_unquote(json_extract(ynh_osquery.columns, '$.local_port')) AS local_port,
              json_unquote(json_extract(ynh_osquery.columns, '$.remote_address')) AS remote_address,
              json_unquote(json_extract(ynh_osquery.columns, '$.remote_port')) AS remote_port,
              ynh_osquery.action,
              ynh_osquery.name AS name2,
              ynh_osquery.columns_uid
            FROM (
              SELECT
                ynh_osquery_id AS _oid,
                ynh_server_id AS _sid
              FROM ynh_osquery_latest_events
              WHERE event_name = 'open_sockets'
            ) AS t
            INNER JOIN ynh_osquery ON ynh_osquery.id = t._oid
            INNER JOIN ynh_servers ON ynh_servers.id = t._sid
            INNER JOIN users ON users.id = ynh_servers.user_id
            HAVING TRIM(path) <> '' OR TRIM(remote_address) <> ''
        ");
        DB::statement("
            CREATE OR REPLACE VIEW v_processes_with_bound_network_sockets AS
            SELECT DISTINCT
              ynh_servers.user_id,
              users.customer_id,
              users.tenant_id,
              ynh_osquery.id AS event_id,
              ynh_osquery.ynh_server_id AS server_id,
              ynh_servers.name AS server_name,
              ynh_servers.ip_address AS server_ip_address,
              ynh_osquery.calendar_time AS timestamp,
              json_unquote(json_extract(ynh_osquery.columns, '$.path')) AS path,
              json_unquote(json_extract(ynh_osquery.columns, '$.address')) AS local_address,
              json_unquote(json_extract(ynh_osquery.columns, '$.port')) AS local_port,
              ynh_osquery.action,
              ynh_osquery.name,
              ynh_osquery.columns_uid
            FROM (
              SELECT
                ynh_osquery_id AS _oid,
                ynh_server_id AS _sid
              FROM ynh_osquery_latest_events
              WHERE event_name = 'process_listening_port'
            ) AS t
            INNER JOIN ynh_osquery ON ynh_osquery.id = t._oid
            INNER JOIN ynh_servers ON ynh_servers.id = t._sid
            INNER JOIN users ON users.id = ynh_servers.user_id
        ");
        DB::statement("
            CREATE OR REPLACE VIEW v_processes AS
            SELECT DISTINCT
              ynh_servers.user_id,
              users.customer_id,
              users.tenant_id,
              ynh_osquery.id AS event_id,
              ynh_osquery.ynh_server_id AS server_id,
              ynh_servers.name AS server_name,
              ynh_servers.ip_address AS server_ip_address,
              ynh_osquery.calendar_time AS timestamp,
              json_unquote(json_extract(ynh_osquery.columns, '$.pid')) AS pid,
              json_unquote(json_extract(ynh_osquery.columns, '$.name')) AS name,
              json_unquote(json_extract(ynh_osquery.columns, '$.cmdline')) AS command,
              json_unquote(json_extract(ynh_osquery.columns, '$.path')) AS path,
              ynh_osquery.action,
              ynh_osquery.name AS name2,
              ynh_osquery.columns_uid
            FROM (
              SELECT
                ynh_osquery_id AS _oid,
                ynh_server_id AS _sid
              FROM ynh_osquery_latest_events
              WHERE event_name = 'processes'
            ) AS t
            INNER JOIN ynh_osquery ON ynh_osquery.id = t._oid
            INNER JOIN ynh_servers ON ynh_servers.id = t._sid
            INNER JOIN users ON users.id = ynh_servers.user_id
            WHERE json_unquote(json_extract(ynh_osquery.columns, '$.cmdline')) NOT LIKE '%performa%'
            AND json_unquote(json_extract(ynh_osquery.columns, '$.cmdline')) NOT LIKE '%logalert%'
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
