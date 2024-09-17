<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class FixupTables4 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('am_assets', function (Blueprint $table) {

            // Deal with ranges
            $table->enum('asset_type', [
                \App\Modules\AdversaryMeter\Enums\AssetTypesEnum::DNS->value,
                \App\Modules\AdversaryMeter\Enums\AssetTypesEnum::IP->value,
                \App\Modules\AdversaryMeter\Enums\AssetTypesEnum::RANGE->value,
            ])->change();

            // Scope assets
            $table->bigInteger('tenant_id')->unsigned()->nullable();
            $table->bigInteger('customer_id')->unsigned()->nullable();
            $table->bigInteger('user_id')->unsigned()->nullable();
            $table->unique(['asset', 'user_id', 'customer_id', 'tenant_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('am_assets', function (Blueprint $table) {
            // There is no going back!
        });
    }
}
