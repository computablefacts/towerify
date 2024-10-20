<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnStripeTxIdToUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('stripe_tx_id')->nullable();
            $table->index('stripe_tx_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public
    function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['stripe_tx_id']);
            $table->dropColumn('stripe_tx_id');
        });
    }
}
