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
        Schema::create('saml2_tenants', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();
            $table->unsignedBigInteger('tenant_id')->nullable()->index('saml2_tenants_tenant_id_foreign');
            $table->unsignedInteger('customer_id')->nullable()->index('saml2_tenants_customer_id_foreign');
            $table->string('domain')->default('');
            $table->string('alt_domain1')->default('');
            $table->char('uuid', 36);
            $table->string('key')->nullable();
            $table->string('idp_entity_id');
            $table->string('idp_login_url');
            $table->string('idp_logout_url');
            $table->text('idp_x509_cert');
            $table->string('relay_state_url')->nullable();
            $table->string('name_id_format')->default('persistent');
            $table->json('metadata');
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('saml2_tenants');
    }
};
