<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableYnhIocs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ynh_iocs', function (Blueprint $table) {

            $table->id();
            $table->timestamps();

            // The target server
            $table->foreignId('ynh_server_id')->constrained()->cascadeOnDelete();

            // Properties
            $table->dateTime('date_min');
            $table->dateTime('date_max');
            $table->double('score');

            // Indexes
            $table->index('date_min');
            $table->index('date_max');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ynh_iocs');
    }
}
