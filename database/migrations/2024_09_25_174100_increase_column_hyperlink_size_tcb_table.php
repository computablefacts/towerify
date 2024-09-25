<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class IncreaseColumnHyperlinkSizeTcbTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ynh_the_cyber_brief', function (Blueprint $table) {
            $table->string('hyperlink', 500)->nullable()->change();
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
            $table->string('hyperlink')->nullable()->change();
        });
    }
}
