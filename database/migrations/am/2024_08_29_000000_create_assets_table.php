<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAssetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('scans', function (Blueprint $table) {

            $table->id();
            $table->timestamps();

            // The scans internal ids
            $table->string('ports_scan_id')->nullable();
            $table->string('vulns_scan_id')->nullable();

            // The scans beginning and end dates
            $table->timestamp('ports_scan_begins_at')->nullable();
            $table->timestamp('ports_scan_ends_at')->nullable();

            $table->timestamp('vulns_scan_begins_at')->nullable();
            $table->timestamp('vulns_scan_ends_at')->nullable();
        });
        Schema::create('assets', function (Blueprint $table) {

            $table->id();
            $table->timestamps();

            // The asset name and type (DNS, IP, etc.)
            $table->string('asset');
            $table->enum('asset_type', [
                \App\Modules\AdversaryMeter\Enums\AssetTypesEnum::DNS->value,
                \App\Modules\AdversaryMeter\Enums\AssetTypesEnum::IP->value,
            ]);

            // If the asset type is DNS, the TLD
            $table->string('tld')->nullable();

            // The previous, current and next scans ids
            $table->bigInteger('prev_scan_id')->unsigned()->nullable();
            $table->foreign('prev_scan_id')->references('id')->on('scans')->nullOnDelete();

            $table->bigInteger('cur_scan_id')->unsigned()->nullable();
            $table->foreign('cur_scan_id')->references('id')->on('scans')->nullOnDelete();

            $table->bigInteger('next_scan_id')->unsigned()->nullable();
            $table->foreign('next_scan_id')->references('id')->on('scans')->nullOnDelete();
        });
        Schema::create('assets_tags', function (Blueprint $table) {

            $table->id();
            $table->timestamps();

            // The asset id
            $table->bigInteger('asset_id')->unsigned()->unique();
            $table->foreign('asset_id')->references('id')->on('assets')->cascadeOnDelete();

            // The tag
            $table->string('tag');
        });
        Schema::create('ports', function (Blueprint $table) {

            $table->id();
            $table->timestamps();

            // The scan id
            $table->bigInteger('scan_id')->unsigned()->unique();
            $table->foreign('scan_id')->references('id')->on('scans')->cascadeOnDelete();

            // The port properties
            $table->string('hostname');
            $table->string('ip');
            $table->integer('port');
            $table->string('protocol');
            $table->string('country')->nullable();

            // The hosting service properties
            $table->string('hosting_service_description')->nullable();
            $table->string('hosting_service_registry')->nullable();
            $table->string('hosting_service_asn')->nullable();
            $table->string('hosting_service_cidr')->nullable();
            $table->string('hosting_service_country_code')->nullable();
            $table->string('hosting_service_date')->nullable();

            // The service, product & SSL properties
            $table->string('service')->nullable();
            $table->string('product')->nullable();
            $table->boolean('ssl')->nullable();
        });
        Schema::create('ports_tags', function (Blueprint $table) {

            $table->id();
            $table->timestamps();

            // The port id
            $table->bigInteger('port_id')->unsigned()->unique();
            $table->foreign('port_id')->references('id')->on('ports')->cascadeOnDelete();

            // The tag
            $table->string('tag');
        });
        Schema::create('alerts', function (Blueprint $table) {

            $table->id();
            $table->timestamps();

            // The port id
            $table->bigInteger('port_id')->unsigned()->unique();
            $table->foreign('port_id')->references('id')->on('ports')->cascadeOnDelete();

            // The alert properties
            $table->string('type');
            $table->string('vulnerability')->nullable();
            $table->string('remediation')->nullable();
            $table->string('level')->nullable();
            $table->string('uid')->nullable();
            $table->string('cve_id')->nullable();
            $table->string('cve_cvss')->nullable(); // CVE ONLY
            $table->string('cve_vendor')->nullable(); // CVE ONLY
            $table->string('cve_product')->nullable(); // CVE ONLY
            $table->string('title')->nullable();
            $table->string('flarum_slug')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('alerts');
        Schema::dropIfExists('ports_tags');
        Schema::dropIfExists('ports');
        Schema::dropIfExists('assets_tags');
        Schema::dropIfExists('assets');
        Schema::dropIfExists('scans');
    }
}
