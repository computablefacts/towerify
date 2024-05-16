<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up()
    {
        Schema::create('zones', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('scope')->default('shipping');

            $table->timestamps();
        });

        Schema::create('zone_members', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('zone_id');
            $table->string('member_id');
            $table->string('member_type');
            $table->timestamps();

            $table->foreign('zone_id')
                ->references('id')
                ->on('zones')
                ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('zone_members');
        Schema::dropIfExists('zones');
    }
};
