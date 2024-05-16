<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Konekt\Address\Models\AddressTypeProxy;

class CreateAddressesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('addresses', function (Blueprint $table) {
            $table->increments('id');
            $table->enum('type', AddressTypeProxy::values())->nullable()->default(AddressTypeProxy::defaultValue());
            $table->string('name');
            $table->char('country_id', 2);
            $table->integer('province_id')->unsigned()->nullable();
            $table->string('postalcode', 12)->nullable(); //12 because: http://stackoverflow.com/a/29280718/1016746
            $table->string('city')->nullable();
            $table->string('address', 384);

            $table->timestamps();
            $table->softDeletes();

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
        Schema::drop('addresses');
    }
}
