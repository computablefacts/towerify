<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableYnhMitreAttck extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ynh_mitre_attck', function (Blueprint $table) {

            $table->id();
            $table->timestamps();

            // Properties
            $table->string('uid');
            $table->string('title');
            $table->json('tactics');
            $table->string('description', 3000);

            // Indexes
            $table->index('uid');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ynh_mitre_attck');
    }
}
