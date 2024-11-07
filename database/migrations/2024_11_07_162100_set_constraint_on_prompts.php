<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class SetConstraintOnPrompts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cb_prompts', function (Blueprint $table) {
            if (Schema::hasIndex('cb_prompts', 'cb_prompts_name_unique')) {
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
        Schema::table('cb_prompts', function (Blueprint $table) {
            $table->unique('name');
        });
    }
}
