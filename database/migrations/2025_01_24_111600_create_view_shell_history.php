<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class CreateViewShellHistory extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
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
              ynh_osquery.action
            FROM ynh_osquery
            INNER JOIN ynh_servers ON ynh_servers.id = ynh_osquery.ynh_server_id
            INNER JOIN users ON users.id = ynh_servers.user_id
            WHERE ynh_osquery.name = 'shell_history'
        ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('DROP VIEW IF EXISTS v_shell_history');
    }
}
