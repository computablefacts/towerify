<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class UpdateDismissViewPackages extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("
            CREATE OR REPLACE VIEW v_packages AS
            SELECT DISTINCT
              ynh_servers.user_id,
              users.customer_id,
              users.tenant_id,
              ynh_osquery.id AS event_id,
              ynh_osquery.ynh_server_id AS server_id,
              ynh_servers.name AS server_name,
              ynh_servers.ip_address AS server_ip_address,
              ynh_osquery.calendar_time AS timestamp,
              CASE _type
                WHEN 1 THEN 'win'
                WHEN 2 THEN 'deb'
                WHEN 3 THEN 'portage'
                WHEN 4 THEN 'npm'
                WHEN 5 THEN 'python'
                WHEN 6 THEN 'rpm'
                WHEN 7 THEN 'homebrew'
                WHEN 8 THEN 'chocolatey'
                ELSE 'n/a'
              END AS type,
              CASE _type
                WHEN 1 THEN json_unquote(json_extract(ynh_osquery.columns, '$.name')) 
                WHEN 2 THEN json_unquote(json_extract(ynh_osquery.columns, '$.name')) 
                WHEN 3 THEN json_unquote(json_extract(ynh_osquery.columns, '$.name')) 
                WHEN 4 THEN json_unquote(json_extract(ynh_osquery.columns, '$.name')) 
                WHEN 5 THEN json_unquote(json_extract(ynh_osquery.columns, '$.name')) 
                WHEN 6 THEN json_unquote(json_extract(ynh_osquery.columns, '$.name')) 
                WHEN 7 THEN json_unquote(json_extract(ynh_osquery.columns, '$.name')) 
                WHEN 8 THEN json_unquote(json_extract(ynh_osquery.columns, '$.name')) 
                ELSE 'n/a'
              END AS package,
              CASE _type
                WHEN 1 THEN json_unquote(json_extract(ynh_osquery.columns, '$.version'))
                WHEN 2 THEN json_unquote(json_extract(ynh_osquery.columns, '$.version')) 
                WHEN 3 THEN json_unquote(json_extract(ynh_osquery.columns, '$.version')) 
                WHEN 4 THEN json_unquote(json_extract(ynh_osquery.columns, '$.version')) 
                WHEN 5 THEN json_unquote(json_extract(ynh_osquery.columns, '$.version')) 
                WHEN 6 THEN json_unquote(json_extract(ynh_osquery.columns, '$.version')) 
                WHEN 7 THEN json_unquote(json_extract(ynh_osquery.columns, '$.version')) 
                WHEN 8 THEN json_unquote(json_extract(ynh_osquery.columns, '$.version')) 
                ELSE 'n/a'
              END AS version,
              ynh_osquery.action,
              ynh_osquery.name,
              ynh_osquery.columns_uid
            FROM (
              SELECT 
                1 AS _type, 
                ynh_osquery_id AS _oid, 
                ynh_server_id AS _sid
              FROM ynh_osquery_latest_events 
              WHERE event_name = 'win_packages' 
              
              UNION
              
              SELECT 
                2 AS _type, 
                ynh_osquery_id AS _oid, 
                ynh_server_id AS _sid
              FROM ynh_osquery_latest_events 
              WHERE event_name = 'deb_packages' 
              
              UNION
              
              SELECT 
                3 AS _type, 
                ynh_osquery_id AS _oid, 
                ynh_server_id AS _sid
              FROM ynh_osquery_latest_events 
              WHERE event_name = 'portage_packages' 
              
              UNION
              
              SELECT 
                4 AS _type, 
                ynh_osquery_id AS _oid, 
                ynh_server_id AS _sid
              FROM ynh_osquery_latest_events 
              WHERE event_name = 'npm_packages' 
              
              UNION
              
              SELECT 
                5 AS _type, 
                ynh_osquery_id AS _oid, 
                ynh_server_id AS _sid
              FROM ynh_osquery_latest_events 
              WHERE event_name = 'python_packages'
              
              UNION
              
              SELECT 
                6 AS _type, 
                ynh_osquery_id AS _oid, 
                ynh_server_id AS _sid
              FROM ynh_osquery_latest_events 
              WHERE event_name = 'rpm_packages'
              
              UNION
              
              SELECT 
                7 AS _type, 
                ynh_osquery_id AS _oid, 
                ynh_server_id AS _sid
              FROM ynh_osquery_latest_events 
              WHERE event_name = 'homebrew_packages'
              
              UNION
              
              SELECT 
                8 AS _type, 
                ynh_osquery_id AS _oid, 
                ynh_server_id AS _sid
              FROM ynh_osquery_latest_events 
              WHERE event_name = 'chocolatey_packages'
            ) AS t
            INNER JOIN ynh_osquery ON ynh_osquery.id = t._oid
            INNER JOIN ynh_servers ON ynh_servers.id = t._sid
            INNER JOIN users ON users.id = ynh_servers.user_id
        ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("
            CREATE OR REPLACE VIEW v_packages AS
            SELECT DISTINCT
              ynh_servers.user_id,
              users.customer_id,
              users.tenant_id,
              ynh_osquery.id AS event_id,
              ynh_osquery.ynh_server_id AS server_id,
              ynh_servers.name AS server_name,
              ynh_servers.ip_address AS server_ip_address,
              ynh_osquery.calendar_time AS timestamp,
              CASE _type
                WHEN 1 THEN 'win'
                WHEN 2 THEN 'deb'
                WHEN 3 THEN 'portage'
                WHEN 4 THEN 'npm'
                WHEN 5 THEN 'python'
                WHEN 6 THEN 'rpm'
                WHEN 7 THEN 'homebrew'
                WHEN 8 THEN 'chocolatey'
                ELSE 'n/a'
              END AS type,
              CASE _type
                WHEN 1 THEN json_unquote(json_extract(ynh_osquery.columns, '$.name')) 
                WHEN 2 THEN json_unquote(json_extract(ynh_osquery.columns, '$.name')) 
                WHEN 3 THEN json_unquote(json_extract(ynh_osquery.columns, '$.name')) 
                WHEN 4 THEN json_unquote(json_extract(ynh_osquery.columns, '$.name')) 
                WHEN 5 THEN json_unquote(json_extract(ynh_osquery.columns, '$.name')) 
                WHEN 6 THEN json_unquote(json_extract(ynh_osquery.columns, '$.name')) 
                WHEN 7 THEN json_unquote(json_extract(ynh_osquery.columns, '$.name')) 
                WHEN 8 THEN json_unquote(json_extract(ynh_osquery.columns, '$.name')) 
                ELSE 'n/a'
              END AS package,
              CASE _type
                WHEN 1 THEN json_unquote(json_extract(ynh_osquery.columns, '$.version'))
                WHEN 2 THEN json_unquote(json_extract(ynh_osquery.columns, '$.version')) 
                WHEN 3 THEN json_unquote(json_extract(ynh_osquery.columns, '$.version')) 
                WHEN 4 THEN json_unquote(json_extract(ynh_osquery.columns, '$.version')) 
                WHEN 5 THEN json_unquote(json_extract(ynh_osquery.columns, '$.version')) 
                WHEN 6 THEN json_unquote(json_extract(ynh_osquery.columns, '$.version')) 
                WHEN 7 THEN json_unquote(json_extract(ynh_osquery.columns, '$.version')) 
                WHEN 8 THEN json_unquote(json_extract(ynh_osquery.columns, '$.version')) 
                ELSE 'n/a'
              END AS version,
              ynh_osquery.action
            FROM (
              SELECT 
                1 AS _type, 
                ynh_osquery_id AS _oid, 
                ynh_server_id AS _sid
              FROM ynh_osquery_latest_events 
              WHERE event_name = 'win_packages' 
              
              UNION
              
              SELECT 
                2 AS _type, 
                ynh_osquery_id AS _oid, 
                ynh_server_id AS _sid
              FROM ynh_osquery_latest_events 
              WHERE event_name = 'deb_packages' 
              
              UNION
              
              SELECT 
                3 AS _type, 
                ynh_osquery_id AS _oid, 
                ynh_server_id AS _sid
              FROM ynh_osquery_latest_events 
              WHERE event_name = 'portage_packages' 
              
              UNION
              
              SELECT 
                4 AS _type, 
                ynh_osquery_id AS _oid, 
                ynh_server_id AS _sid
              FROM ynh_osquery_latest_events 
              WHERE event_name = 'npm_packages' 
              
              UNION
              
              SELECT 
                5 AS _type, 
                ynh_osquery_id AS _oid, 
                ynh_server_id AS _sid
              FROM ynh_osquery_latest_events 
              WHERE event_name = 'python_packages'
              
              UNION
              
              SELECT 
                6 AS _type, 
                ynh_osquery_id AS _oid, 
                ynh_server_id AS _sid
              FROM ynh_osquery_latest_events 
              WHERE event_name = 'rpm_packages'
              
              UNION
              
              SELECT 
                7 AS _type, 
                ynh_osquery_id AS _oid, 
                ynh_server_id AS _sid
              FROM ynh_osquery_latest_events 
              WHERE event_name = 'homebrew_packages'
              
              UNION
              
              SELECT 
                8 AS _type, 
                ynh_osquery_id AS _oid, 
                ynh_server_id AS _sid
              FROM ynh_osquery_latest_events 
              WHERE event_name = 'chocolatey_packages'
            ) AS t
            INNER JOIN ynh_osquery ON ynh_osquery.id = t._oid
            INNER JOIN ynh_servers ON ynh_servers.id = t._sid
            INNER JOIN users ON users.id = ynh_servers.user_id
        ");
    }
}
