<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('am_honeypots_events', function (Blueprint $table) {
            $table->foreign(['attacker_id'])->references(['id'])->on('am_attackers')->onUpdate('restrict')->onDelete('set null');
            $table->foreign(['honeypot_id'])->references(['id'])->on('am_honeypots')->onUpdate('restrict')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('am_honeypots_events', function (Blueprint $table) {
            $table->dropForeign('am_honeypots_events_attacker_id_foreign');
            $table->dropForeign('am_honeypots_events_honeypot_id_foreign');
        });
    }
};
