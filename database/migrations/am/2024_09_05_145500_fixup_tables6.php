<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class FixupTables6 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('hidden_alerts', function (Blueprint $table) {

            $table->id();
            $table->timestamps();

            // The marker properties
            $table->string('uid')->nullable();
            $table->string('type')->nullable();
            $table->string('title')->nullable();
        });
        Schema::table('assets', function (Blueprint $table) {
            $table->renameColumn('asset_type', 'type');
            $table->dropUnique(['asset', 'user_id', 'customer_id', 'tenant_id']);
            $table->dropColumn('tenant_id');
            $table->dropColumn('customer_id');
            $table->dropColumn('user_id');
        });
        Schema::table('honeypots', function (Blueprint $table) {
            $table->dropUnique(['dns', 'user_id', 'customer_id', 'tenant_id']);
            $table->dropColumn('tenant_id');
            $table->dropColumn('customer_id');
            $table->dropColumn('user_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('honeypots', function (Blueprint $table) {
            // There is no going back!
        });
        Schema::table('assets', function (Blueprint $table) {
            // There is no going back!
        });
        Schema::dropIfExists('hidden_alerts');
    }
}
