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
        Schema::create('ynh_osquery_rules', function (Blueprint $table) {

            $table->id();
            $table->timestamps();

            // Query configuration
            // See https://osquery.readthedocs.io/en/stable/deployment/configuration/ for details
            $table->string('name');
            $table->string('description', 255);
            $table->string('query', 1000);
            $table->integer('interval')->default(3600);
            $table->boolean('removed')->default(true);
            $table->boolean('snapshot')->default(false);
            $table->enum('platform', [
                \App\Enums\OsqueryPlatformEnum::DARWIN->value,
                \App\Enums\OsqueryPlatformEnum::LINUX->value,
                \App\Enums\OsqueryPlatformEnum::POSIX->value,
                \App\Enums\OsqueryPlatformEnum::WINDOWS->value,
                \App\Enums\OsqueryPlatformEnum::UBUNTU->value,
                \App\Enums\OsqueryPlatformEnum::CENTOS->value,
                \App\Enums\OsqueryPlatformEnum::ALL->value,
            ])->default(\App\Enums\OsqueryPlatformEnum::ALL->value);
        });
        Schema::create('ynh_osquery_rules_scope_customer', function (Blueprint $table) {

            $table->id();
            $table->timestamps();

            // Link a rule to a customer
            $table->intOrBigIntBasedOnRelated('rule_id', Schema::connection(null), 'ynh_osquery_rules.id')->cascadeOnDelete();
            $table->foreign('rule_id')->references('id')->on('ynh_osquery_rules')->cascadeOnDelete();

            $table->intOrBigIntBasedOnRelated('customer_id', Schema::connection(null), 'customers.id')->cascadeOnDelete();
            $table->foreign('customer_id')->references('id')->on('customers')->cascadeOnDelete();
        });
        Schema::create('ynh_osquery_rules_scope_tenant', function (Blueprint $table) {

            $table->id();
            $table->timestamps();

            // Link a rule to a tenant
            $table->intOrBigIntBasedOnRelated('rule_id', Schema::connection(null), 'ynh_osquery_rules.id')->cascadeOnDelete();
            $table->foreign('rule_id')->references('id')->on('ynh_osquery_rules')->cascadeOnDelete();

            $table->intOrBigIntBasedOnRelated('tenant_id', Schema::connection(null), 'tenants.id')->cascadeOnDelete();
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ynh_osquery_rules_scope_tenant');
        Schema::dropIfExists('ynh_osquery_rules_scope_customer');
        Schema::dropIfExists('ynh_osquery_rules');
    }
};
