<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateTableCbTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cb_tables', function (Blueprint $table) {
            $table->string('credentials', 1000)->nullable();
            $table->boolean('updatable')->default(false);
            $table->json('schema')->default("{}");
            $table->longText('query')->nullable();
            $table->unsignedBigInteger('nb_rows')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cb_tables', function (Blueprint $table) {
            $table->dropColumn('credentials');
            $table->dropColumn('updatable');
            $table->dropColumn('schema');
            $table->dropColumn('query');
            $table->dropColumn('nb_rows');
        });
    }
}
