<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class CreateViewServices extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
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
              ynh_osquery.action
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
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('DROP VIEW IF EXISTS v_services');
    }
}
