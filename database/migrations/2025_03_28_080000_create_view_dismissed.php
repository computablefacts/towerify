<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class CreateViewDismissed extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        DB::statement("
            CREATE OR REPLACE VIEW v_dismissed AS
            SELECT
              id,
              ynh_server_id,
              name,
              action,
              columns_uid,
              calendar_time
            FROM ynh_osquery
            WHERE dismissed = TRUE
        ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        DB::statement('DROP VIEW IF EXISTS v_dismissed');
    }
}
