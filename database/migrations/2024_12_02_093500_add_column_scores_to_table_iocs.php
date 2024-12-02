<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnScoresToTableIocs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ynh_iocs', function (Blueprint $table) {

            $table->dropColumn('score');

            $table->integer('count')->default(0);
            $table->double('min')->nullable();
            $table->double('max')->nullable();
            $table->double('sum')->nullable();
            $table->double('product')->nullable();
            $table->double('mean')->nullable();
            $table->double('median')->nullable();
            $table->double('std_dev')->nullable();
            $table->double('variance')->nullable();

            // Raw data points
            $table->json('iocs')->default("[]");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // There is no going back!
    }
}
