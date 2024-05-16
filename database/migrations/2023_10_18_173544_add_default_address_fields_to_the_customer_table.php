<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->intOrBigIntBasedOnRelated('default_billing_address_id', Schema::connection(null), 'addresses.id')->nullable();
            $table->intOrBigIntBasedOnRelated('default_shipping_address_id', Schema::connection(null), 'addresses.id')->nullable();

            $table->foreign('default_billing_address_id')->references('id')->on('addresses')->nullOnDelete();
            $table->foreign('default_shipping_address_id')->references('id')->on('addresses')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            if (!$this->isSqlite()) {
                $table->dropForeign('customers_default_billing_address_id_foreign');
                $table->dropForeign('customers_default_shipping_address_id_foreign');
            }

            $table->dropColumn('default_billing_address_id');
        });
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn('default_shipping_address_id');
        });
    }

    private function isSqlite(): bool
    {
        return 'sqlite' === Schema::connection($this->getConnection())
                ->getConnection()
                ->getPdo()
                ->getAttribute(PDO::ATTR_DRIVER_NAME)
        ;
    }
};
