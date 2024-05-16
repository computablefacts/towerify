<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Konekt\Address\Models\ProvinceTypeProxy;

class CreateProvincesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        /** based on ISO 3166-2 https://en.wikipedia.org/wiki/ISO_3166-2 */
        Schema::create('provinces', function (Blueprint $table) {
            $table->increments('id');
            $table->char('country_id', 2);
            $table->enum('type', ProvinceTypeProxy::values())->default(ProvinceTypeProxy::defaultValue());
            $table->string('code', 16)->nullable()->comment('National identification code');
            $table->string('name');

            $table->foreign('country_id')
                ->references('id')
                ->on('countries');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('provinces');
    }
}
