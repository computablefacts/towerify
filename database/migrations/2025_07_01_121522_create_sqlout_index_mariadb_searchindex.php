<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::connection('mariadb')->create('searchindex', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('record_type', 191)->index();
            $table->unsignedBigInteger('record_id')->index();
            $table->string('field', 191)->index();
            $table->unsignedSmallInteger('weight')->default(1);
            $table->text('content');
            $table->timestamps();
        });
        $tableName = DB::connection('mariadb')->getTablePrefix() . 'searchindex';
        DB::connection('mariadb')->statement("ALTER TABLE $tableName ADD FULLTEXT searchindex_content (content)");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('mariadb')->dropIfExists('searchindex');
    }
};
