<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ynh_ossec_checks', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();
            $table->unsignedBigInteger('ynh_ossec_policy_id')->index('ynh_ossec_checks_ynh_ossec_policy_id_foreign');
            $table->integer('uid')->index();
            $table->string('title', 500);
            $table->string('description', 5000);
            $table->string('rationale', 2000);
            $table->string('impact', 2000);
            $table->string('remediation', 3000);
            $table->json('references');
            $table->json('compliance');
            $table->json('requirements');
            $table->longText('rule');

            $table->fullText(['title', 'description', 'rationale', 'remediation'], 'ynh_ossec_checks_index_all');
            $table->unique(['uid']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ynh_ossec_checks');
    }
};
