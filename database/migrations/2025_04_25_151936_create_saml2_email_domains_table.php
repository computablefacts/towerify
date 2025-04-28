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
        Schema::create('saml2_email_domains', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->string('domain');

            // The SAML Tenant Id
            $table->intOrBigIntBasedOnRelated('saml2_tenant_id', Schema::connection(null), 'saml2_tenants.id');
            $table->foreign('saml2_tenant_id')->references('id')->on('saml2_tenants')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('saml2_email_domains');
    }
};
