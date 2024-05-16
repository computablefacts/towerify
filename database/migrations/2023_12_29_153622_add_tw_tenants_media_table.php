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
        Schema::create('tw_tenants_media', function (Blueprint $table) {
            $table->intOrBigIntBasedOnRelated('client_id', Schema::connection(null), 'tw_tenants.id')->cascadeOnDelete();
            $table->intOrBigIntBasedOnRelated('model_id', Schema::connection(null), 'media.id')->cascadeOnDelete();
            $table->foreign('client_id')->references('id')->on('tw_tenants')->cascadeOnDelete();
            $table->foreign('model_id')->references('id')->on('media')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tw_tenants_media');
    }
};
