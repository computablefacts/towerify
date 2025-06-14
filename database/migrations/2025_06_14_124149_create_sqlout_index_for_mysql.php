<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSqloutIndexForMysql extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mysql')->create('searchindex', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('record_type', 191)->index();
            $table->unsignedBigInteger('record_id')->index();
            $table->string('field', 191)->index();
            $table->unsignedSmallInteger('weight')->default(1);
            $table->text('content');
            $table->timestamps();
        });
        $tableName = DB::connection('mysql')->getTablePrefix() . 'searchindex';
        DB::connection('mysql')->statement("ALTER TABLE $tableName ADD FULLTEXT searchindex_content (content)");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('mysql')->dropIfExists('searchindex');
    }
}
