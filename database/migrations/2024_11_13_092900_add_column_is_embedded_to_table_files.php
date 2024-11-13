<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnIsEmbeddedToTableFiles extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cb_files', function (Blueprint $table) {
            $table->boolean('is_embedded')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cb_files', function (Blueprint $table) {
            $table->dropColumn(['is_embedded']);
        });
    }
}
