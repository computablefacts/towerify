<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFrColumnsInTcbTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ynh_the_cyber_brief', function (Blueprint $table) {
            $table->string('teaser_fr', 140)->nullable(); // old twitter
            $table->string('opener_fr', 280)->nullable(); // new twitter
            $table->string('why_it_matters_fr', 1000)->nullable();
            $table->text('go_deeper_fr')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ynh_the_cyber_brief', function (Blueprint $table) {
            $table->dropColumn('teaser_fr');
            $table->dropColumn('opener_fr');
            $table->dropColumn('why_it_matters_fr');
            $table->dropColumn('go_deeper_fr');
        });
    }
}
