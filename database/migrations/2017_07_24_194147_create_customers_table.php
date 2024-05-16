<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Konekt\Customer\Models\CustomerTypeProxy;

class CreateCustomersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->increments('id');
            $table->string('type', 24)->default(CustomerTypeProxy::defaultValue());
            $table->string('email')->nullable();
            $table->string('phone', 22)->nullable();
            $table->string('firstname')->nullable()->comment('First name');
            $table->string('lastname')->nullable()->comment('Last name');
            $table->string('company_name')->nullable();
            $table->string('tax_nr', 17)->nullable()->comment('Tax/VAT Identification Number'); //https://www.wikiwand.com/en/VAT_identification_number
            $table->string('registration_nr')->nullable()->comment('Company/Trade Registration Number');
            $table->boolean('is_active')->default(true);
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
        Schema::drop('customers');
    }
}
