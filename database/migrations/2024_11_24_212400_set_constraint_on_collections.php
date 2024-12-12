<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class SetConstraintOnCollections extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cb_collections', function (Blueprint $table) {
            if (Schema::hasIndex('cb_collections', 'cb_chunks_collections_name_unique')) {
                $table->dropUnique('cb_chunks_collections_name_unique');
            }
            if (Schema::hasIndex('cb_collections', 'cb_collections_name_unique')) {
                $table->dropUnique(['name']);
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cb_collections', function (Blueprint $table) {
            $table->unique('name');
        });
    }
}
