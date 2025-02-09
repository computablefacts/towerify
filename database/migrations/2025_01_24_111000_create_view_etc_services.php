<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class CreateViewEtcServices extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
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
              ynh_osquery.action
            FROM ynh_osquery
            INNER JOIN ynh_servers ON ynh_servers.id = ynh_osquery.ynh_server_id
            INNER JOIN users ON users.id = ynh_servers.user_id
            WHERE ynh_osquery.name = 'etc_services'
        ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('DROP VIEW IF EXISTS v_etc_services');
    }
}
