<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('t_facts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();
            $table->unsignedBigInteger('owned_by')->index('t_facts_owned_by_foreign');
            $table->string('attribute')->index();
            $table->enum('type', ['string', 'number', 'timestamp', 'boolean'])->index();
            $table->string('value', 10000)->nullable()->index();
            $table->double('numerical_value')->nullable()->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_facts');
    }
};
