<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ynh_osquery', function (Blueprint $table) {

            $table->id();
            $table->timestamps();

            // The target server
            $table->foreignId('ynh_server_id')->constrained()->cascadeOnDelete();

            // The trace information
            //
            // cat /var/log/osquery/osqueryd.results.log | jq
            // {
            //  "name": "process_events",
            //  "hostIdentifier": "poc-ynh01",
            //  "calendarTime": "Wed Feb 21 15:22:40 2024 UTC",
            //  "unixTime": 1708528960,
            //  "epoch": 0,
            //  "counter": 300,
            //  "numerics": false,
            //  "columns": {
            //    "auid": "4294967295",
            //    "cmdline": "worker_nscd 0",
            //    "ctime": "1689779797",
            //    "cwd": "\"/\"",
            //    "egid": "116",
            //    "euid": "110",
            //    "gid": "116",
            //    "parent": "711",
            //    "path": "/usr/sbin/nscd",
            //    "pid": "2352019",
            //    "time": "1708528959",
            //    "uid": "110"
            //  },
            //  "action": "added"
            // }
            //
            // cat /var/log/osquery/osqueryd.snapshots.log | jq
            // {
            //  "name": "network_interfaces_snapshot",
            //  "hostIdentifier": "poc-ynh01",
            //  "calendarTime": "Wed Feb 21 15:27:48 2024 UTC",
            //  "unixTime": 1708529268,
            //  "epoch": 0,
            //  "counter": 0,
            //  "numerics": false,
            //  "columns": {
            //    "address": "fe80::f0bf:8dff:fe33:1703%veth2722951",
            //    "interface": "veth2722951",
            //    "mac": "f2:bf:8d:33:17:03"
            //  },
            //  "action": "snapshot"
            // }
            $table->bigInteger('row');
            $table->string('name');
            $table->string('host_identifier');
            $table->dateTime('calendar_time');
            $table->bigInteger('unix_time');
            $table->bigInteger('epoch');
            $table->bigInteger('counter');
            $table->boolean('numerics');
            $table->json('columns');
            $table->string('action');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ynh_osquery');
    }
};
