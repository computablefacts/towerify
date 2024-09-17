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
        Schema::create('am_hidden_alerts', function (Blueprint $table) {

            $table->id();
            $table->timestamps();

            // The marker properties
            $table->string('uid')->nullable();
            $table->string('type')->nullable();
            $table->string('title')->nullable();
        });
        Schema::table('am_assets', function (Blueprint $table) {
            $table->renameColumn('asset_type', 'type');
            $table->dropUnique(['asset', 'user_id', 'customer_id', 'tenant_id']);
            $table->dropColumn('tenant_id');
            $table->dropColumn('customer_id');
            $table->dropColumn('user_id');
        });
        Schema::table('am_honeypots', function (Blueprint $table) {
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
        Schema::table('am_honeypots', function (Blueprint $table) {
            // There is no going back!
        });
        Schema::table('am_assets', function (Blueprint $table) {
            // There is no going back!
        });
        Schema::dropIfExists('am_hidden_alerts');
    }
}
