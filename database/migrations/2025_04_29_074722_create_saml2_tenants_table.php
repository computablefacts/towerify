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
        Schema::create('saml2_tenants', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            // The associated Cywise tenant
            $table->intOrBigIntBasedOnRelated('tenant_id', Schema::connection(null), 'tenants.id')->nullable();
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();

            // The associated customer
            $table->intOrBigIntBasedOnRelated('customer_id', Schema::connection(null), 'customers.id')->unsigned()->nullable();
            $table->foreign('customer_id')->references('id')->on('customers')->cascadeOnDelete();

            // Associated email domains
            $table->string('domain')->default('');
            $table->string('alt_domain1')->default('');

            $table->uuid();
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
