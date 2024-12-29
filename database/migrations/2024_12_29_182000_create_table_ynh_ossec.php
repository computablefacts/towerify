<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableYnhOssec extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ynh_ossec_policies', function (Blueprint $table) {

            $table->id();
            $table->timestamps();

            // Properties
            $table->string('uid')->unique();
            $table->string('name');
            $table->string('description', 1000);
            $table->json('references');
            $table->json('requirements');

            // Indexes
            $table->index('uid');
        });
        Schema::create('ynh_ossec_checks', function (Blueprint $table) {

            $table->id();
            $table->timestamps();

            // Policy
            $table->intOrBigIntBasedOnRelated('ynh_ossec_policy_id', Schema::connection(null), 'ynh_ossec_policies.id');
            $table->foreign('ynh_ossec_policy_id')->references('id')->on('ynh_ossec_policies');

            // Properties
            $table->integer('uid')->unique();
            $table->string('title', 500);
            $table->string('description', 5000);
            $table->string('rationale', 2000);
            $table->string('impact', 2000);
            $table->string('remediation', 3000);
            $table->json('references');
            $table->json('compliance');
            $table->json('requirements');

            // Indexes
            $table->index('uid');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ynh_ossec_checks');
        Schema::dropIfExists('ynh_ossec_policies');
    }
}
