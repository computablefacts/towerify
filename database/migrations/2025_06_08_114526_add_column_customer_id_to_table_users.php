<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('tenant_id')->nullable();
            $table->foreign('tenant_id')->references('id')->on('tenants')->nullOnDelete();
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->string('am_api_token')->nullable()->default(null);
            $table->string('se_api_token')->nullable()->default(null);
            $table->boolean('gets_audit_report')->default(true);
            $table->string('performa_domain')->nullable()->default(null);
            $table->string('performa_secret')->nullable()->default(null);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['performa_domain', 'performa_secret']);
            $table->dropColumn('gets_audit_report');
            $table->dropColumn('am_api_token');
            $table->dropColumn('customer_id');
            $table->dropForeign(['tenant_id']);
            $table->dropColumn('tenant_id');
        });
    }
};
