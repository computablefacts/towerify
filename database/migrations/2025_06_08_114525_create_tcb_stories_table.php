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
        Schema::create('tcb_stories', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable()->index('ynh_the_cyber_brief_updated_at_index');
            $table->text('news');
            $table->enum('news_language', ['en', 'fr'])->default('fr');
            $table->string('hyperlink', 500)->nullable();
            $table->string('website')->nullable();
            $table->string('teaser', 140)->nullable();
            $table->string('opener', 280)->nullable();
            $table->string('why_it_matters', 1000)->nullable();
            $table->text('go_deeper')->nullable();
            $table->boolean('is_published')->default(false);
            $table->string('teaser_fr', 140)->nullable();
            $table->string('opener_fr', 280)->nullable();
            $table->string('why_it_matters_fr', 1000)->nullable();
            $table->text('go_deeper_fr')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tcb_stories');
    }
};
