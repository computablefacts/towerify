<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class CreateViewAuthorizedKeys extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("
            CREATE OR REPLACE VIEW v_authorized_keys AS
            SELECT DISTINCT
              ynh_servers.user_id,
              users.customer_id,
              users.tenant_id,
              ynh_osquery.id AS event_id,
              ynh_osquery.ynh_server_id AS server_id,
              ynh_servers.name AS server_name,
              ynh_servers.ip_address AS server_ip_address,
              ynh_osquery.calendar_time AS timestamp,
              json_unquote(json_extract(ynh_osquery.columns, '$.key_file')) AS key_file,
              CONCAT(LEFT(json_unquote(json_extract(ynh_osquery.columns, '$.key')), 15), '[...]', RIGHT(json_unquote(json_extract(ynh_osquery.columns, '$.key')), 15)) AS `key`,
              CASE
                WHEN json_unquote(json_extract(ynh_osquery.columns, '$.comment')) = 'null' THEN NULL
                ELSE json_unquote(json_extract(ynh_osquery.columns, '$.comment'))
              END AS key_comment,
              json_unquote(json_extract(ynh_osquery.columns, '$.algorithm')) AS algorithm,
              ynh_osquery.action
            FROM ynh_osquery
            INNER JOIN ynh_servers ON ynh_servers.id = ynh_osquery.ynh_server_id
            INNER JOIN users ON users.id = ynh_servers.user_id
            WHERE ynh_osquery.name = 'authorized_keys'
        ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('DROP VIEW IF EXISTS v_authorized_keys');
    }
}
