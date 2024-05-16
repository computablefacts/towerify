<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Konekt\Customer\Models\CustomerProxy;

return new class () extends Migration {
    public function up(): void
    {
        $customerClass = morph_type_of(CustomerProxy::modelClass());
        DB::table('customer_addresses')
            ->whereNull('deleted_at')
            ->select(['id', 'customer_id', 'address_id'])
            ->chunkById(1000, function ($customerAddresses) use ($customerClass) {
                foreach ($customerAddresses as $customerAddress) {
                    DB::table('addresses')
                        ->where('id', $customerAddress->address_id)
                        ->update([
                            'model_type' => $customerClass,
                            'model_id' => $customerAddress->customer_id,
                        ]);
                }
            });

        Schema::drop('customer_addresses');
    }

    public function down(): void
    {
        Schema::create('customer_addresses', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('customer_id')->unsigned();
            $table->integer('address_id')->unsigned();
            $table->timestamps();
            $table->softDeletes();
        });

        $customerClass = morph_type_of(CustomerProxy::modelClass());
        DB::table('addresses')
            ->where('model_type', $customerClass)
            ->select(['id', 'model_id', 'created_at'])
            ->chunkById(1000, function ($addresses) {
                foreach ($addresses as $address) {
                    DB::table('customer_addresses')
                        ->insert([
                            'address_id' => $address->id,
                            'customer_id' => $address->model_id,
                            'created_at' => $address->created_at,
                            'updated_at' => now(),
                        ]);
                }
            });
    }
};
