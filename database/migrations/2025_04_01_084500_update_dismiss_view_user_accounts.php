<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class UpdateDismissViewUserAccounts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("
            CREATE OR REPLACE VIEW v_user_accounts AS
            SELECT DISTINCT
              ynh_servers.user_id,
              users.customer_id,
              users.tenant_id,
              ynh_osquery.id AS event_id,
              ynh_osquery.ynh_server_id AS server_id,
              ynh_servers.name AS server_name,
              ynh_servers.ip_address AS server_ip_address,
              ynh_osquery.calendar_time AS timestamp,
              json_unquote(json_extract(ynh_osquery.columns, '$.uid')) AS user,    
              json_unquote(json_extract(ynh_osquery.columns, '$.gid')) AS `group`,
              json_unquote(json_extract(ynh_osquery.columns, '$.username')) AS username,
              json_unquote(json_extract(ynh_osquery.columns, '$.directory')) AS home_directory,    
              json_unquote(json_extract(ynh_osquery.columns, '$.shell')) AS default_shell,
              ynh_osquery.action,
              ynh_osquery.name,
              ynh_osquery.columns_uid
            FROM ynh_osquery
            INNER JOIN ynh_osquery_latest_events ON ynh_osquery_latest_events.ynh_osquery_id = ynh_osquery.id
              AND ynh_osquery_latest_events.event_name = 'users'
            INNER JOIN ynh_servers ON ynh_servers.id = ynh_osquery_latest_events.ynh_server_id
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
        DB::statement('DROP VIEW IF EXISTS v_user_accounts');
    }
}
