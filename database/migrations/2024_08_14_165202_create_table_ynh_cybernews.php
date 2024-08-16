<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ynh_the_cyber_brief', function (Blueprint $table) {

            $table->id();
            $table->timestamps();

            // The news source and language
            $table->text('news');
            $table->enum('news_language', [
                \App\Enums\LanguageEnum::ENGLISH->value,
                \App\Enums\LanguageEnum::FRENCH->value,
            ])->default('fr');
            
            // The news summary
            $table->string('hyperlink')->nullable();
            $table->string('website')->nullable();
            $table->string('teaser', 140)->nullable(); // old twitter
            $table->string('opener', 280)->nullable(); // new twitter
            $table->string('why_it_matters', 1000)->nullable();
            $table->text('go_deeper')->nullable();

            // The news status
            $table->boolean('is_published')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ynh_the_cyber_brief');
    }
};
