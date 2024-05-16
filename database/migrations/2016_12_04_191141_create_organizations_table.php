<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateOrganizationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('organizations', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('tax_nr', 17)->nullable()->comment('Tax/VAT Identification Number'); //https://www.wikiwand.com/en/VAT_identification_number
            $table->string('registration_nr')->nullable()->comment('Company/Trade Registration Number');
            $table->string('email')->nullable();
            $table->string('phone', 22)->nullable();
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
        Schema::drop('organizations');
    }
}
