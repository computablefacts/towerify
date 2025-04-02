<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableYnhTrials extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ynh_trials', function (Blueprint $table) {

            $table->id();
            $table->timestamps();

            // The user who created the table
            $table->intOrBigIntBasedOnRelated('created_by', Schema::connection(null), 'users.id')->nullable()->nullOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->nullable()->nullOnDelete();

            // Properties
            $table->string('hash', 256);
            $table->string('domain', 100)->nullable();
            $table->json('subdomains')->nullable();
            $table->boolean('honeypots')->default(false);
            $table->string('email', 100)->nullable();
            
            $table->index(['hash']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ynh_trials');
    }
}
