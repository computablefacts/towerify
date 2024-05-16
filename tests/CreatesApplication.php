<?php

namespace Tests;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;

/**
 * To create the test database:
 * <pre>
 *     CREATE DATABASE tw_testdb DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
 * </pre>
 *
 * To create the test user:
 * <pre>
 *     CREATE USER 'tw_testuser'@'localhost' IDENTIFIED BY 'z0rglub';
 *     GRANT ALL ON tw_testdb.* TO 'tw_testuser'@'localhost';
 *     FLUSH PRIVILEGES;
 * </pre>
 *
 * See https://dwij.net/how-to-speed-up-laravel-unit-tests-using-schemadump/
 */
trait CreatesApplication
{
    /**
     * Creates the application.
     *
     * @return \Illuminate\Foundation\Application
     */
    public function createApplication()
    {
        $app = require __DIR__ . '/../bootstrap/app.php';

        $app->make(Kernel::class)->bootstrap();

        // Check if we should use the schema dump
        if (env('USE_SCHEMA_DUMP')) {
            $this->loadSchemaDump();
        } else {
            $this->runDatabaseMigrations();
        }
        return $app;
    }

    protected function loadSchemaDump()
    {
        // Turn off foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Get all table names
        $tables = DB::select('SHOW TABLES');

        // Drop all tables
        foreach ($tables as $table) {
            $tableName = $table->Tables_in_tw_testdb;
            DB::statement("DROP TABLE IF EXISTS {$tableName}");
        }

        // Turn foreign key checks back on
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Load the schema dump generated via 'php artisan schema:dump'
        DB::unprepared(file_get_contents(database_path('schema/mysql-schema.dmp')));
    }

    protected function runDatabaseMigrations()
    {
        $this->artisan('migrate');
    }
}
