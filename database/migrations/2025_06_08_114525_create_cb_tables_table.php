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
        Schema::create('cb_tables', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->string('name', 100);
            $table->string('description', 2000);
            $table->boolean('copied');
            $table->boolean('deduplicated');
            $table->dateTime('started_at')->nullable();
            $table->dateTime('finished_at')->nullable();
            $table->text('last_error')->nullable();
            $table->string('credentials', 1000)->nullable();
            $table->boolean('updatable')->default(false);
            $table->json('schema')->default('{}');
            $table->longText('query')->nullable();
            $table->unsignedBigInteger('nb_rows')->default(0);

            $table->unique(['created_by', 'name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cb_tables');
    }
};
