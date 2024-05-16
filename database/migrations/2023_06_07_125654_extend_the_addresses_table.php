<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('addresses', function (Blueprint $table) {
            $table->string('lastname')->nullable()->after('name');
            $table->string('firstname')->nullable()->after('name');
            $table->string('company_name')->nullable()->after('name');
            $table->string('access_code')->nullable()->after('address');
            $table->string('registration_nr')->nullable()->after('address');
            $table->string('tax_nr')->nullable()->after('address');
            $table->string('email')->nullable()->after('address');
            $table->string('phone')->nullable()->after('address');
            $table->string('address2')->nullable()->after('address');
        });
    }

    public function down(): void
    {
        Schema::table('addresses', function (Blueprint $table) {
            $table->dropColumn([
                'firstname',
                'lastname',
                'company_name',
                'access_code',
                'email',
                'phone',
                'address2',
                'tax_nr',
                'registration_nr',
            ]);
        });
    }
};
