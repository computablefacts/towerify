<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Konekt\Address\Models\GenderProxy;
use Konekt\Address\Models\NameOrderProxy;

class CreatePersonsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('persons', function (Blueprint $table) {
            $table->increments('id');
            $table->string('firstname')->comment('First name');
            $table->string('lastname')->comment('Last Name');
            $table->string('email')->nullable();
            $table->string('phone', 22)->nullable();
            $table->date('birthdate')->nullable();
            $table->enum('gender', GenderProxy::values())->nullable();
            $table->string('nin', 21)->nullable()->comment('National Identification Number');

            $table->enum('nameorder', NameOrderProxy::values())
                ->default(NameOrderProxy::defaultValue())
                ->comment('western: First Last, eastern: Last First');

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
        Schema::drop('persons');
    }
}
