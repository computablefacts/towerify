<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class UpdateDismissViewLdPreload extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("
            CREATE OR REPLACE VIEW v_ld_preload AS
            SELECT DISTINCT
              ynh_servers.user_id,
              users.customer_id,
              users.tenant_id,
              ynh_osquery.id AS event_id,
              ynh_osquery.ynh_server_id AS server_id,
              ynh_servers.name AS server_name,
              ynh_servers.ip_address AS server_ip_address,
              ynh_osquery.calendar_time AS timestamp,
              json_unquote(json_extract(ynh_osquery.columns, '$.path')) AS program,
              json_unquote(json_extract(ynh_osquery.columns, '$.value')) AS ld_preload_value,
              ynh_osquery.action,
              ynh_osquery.name,
              ynh_osquery.columns_uid
            FROM (
              SELECT
                ynh_osquery_id AS _oid,
                ynh_server_id AS _sid
              FROM ynh_osquery_latest_events
              WHERE event_name = 'ld_preload'
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
            CREATE OR REPLACE VIEW v_ld_preload AS
            SELECT DISTINCT
              ynh_servers.user_id,
              users.customer_id,
              users.tenant_id,
              ynh_osquery.id AS event_id,
              ynh_osquery.ynh_server_id AS server_id,
              ynh_servers.name AS server_name,
              ynh_servers.ip_address AS server_ip_address,
              ynh_osquery.calendar_time AS timestamp,
              json_unquote(json_extract(ynh_osquery.columns, '$.value')) AS program,
              ynh_osquery.action
            FROM (
              SELECT
                ynh_osquery_id AS _oid,
                ynh_server_id AS _sid
              FROM ynh_osquery_latest_events
              WHERE event_name = 'ld_preload'
            ) AS t
            INNER JOIN ynh_osquery ON ynh_osquery.id = t._oid
            INNER JOIN ynh_servers ON ynh_servers.id = t._sid
            INNER JOIN users ON users.id = ynh_servers.user_id
        ");
    }
}
