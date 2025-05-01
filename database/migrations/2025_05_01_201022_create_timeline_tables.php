<?php

use App\Models\TimelineFact;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('t_items', function (Blueprint $table) {

            $table->id();
            $table->timestamps();

            // The item owner
            $table->intOrBigIntBasedOnRelated('owned_by', Schema::connection(null), 'users.id');
            $table->foreign('owned_by')->references('id')->on('users')->cascadeOnDelete();

            // The item attributes
            $table->string('type');
            $table->dateTime('timestamp');
            $table->integer('flags')->default(0);

            // Indexes
            $table->index(['type']);
            $table->index(['timestamp']);
        });
        Schema::create('t_facts', function (Blueprint $table) {

            $table->id();
            $table->timestamps();

            // The fact owner
            $table->intOrBigIntBasedOnRelated('owned_by', Schema::connection(null), 'users.id');
            $table->foreign('owned_by')->references('id')->on('users')->cascadeOnDelete();

            // The fact attributes
            $table->string('attribute');
            $table->enum('type', [
                TimelineFact::TYPE_STRING,
                TimelineFact::TYPE_NUMBER,
                TimelineFact::TYPE_TIMESTAMP,
                TimelineFact::TYPE_BOOLEAN,
            ]);
            $table->string('value')->nullable();
            $table->double('numerical_value')->nullable();

            // Indexes
            $table->index(['attribute']);
            $table->index(['type']);
            $table->index(['value']);
            $table->index(['numerical_value']);
        });
        Schema::create('t_facts_items', function (Blueprint $table) {

            $table->id();
            $table->timestamps();

            // Relations
            $table->foreignIdFor(\App\Models\TimelineItem::class, 'item_id')->constrained()->cascadeOnDelete();
            $table->foreignIdFor(\App\Models\TimelineFact::class, 'fact_id')->constrained()->cascadeOnDelete();

            // Constraints
            $table->unique(['item_id', 'fact_id']);
        });
        Schema::create('t_items_items', function (Blueprint $table) {

            $table->id();
            $table->timestamps();

            // Relations
            $table->foreignIdFor(\App\Models\TimelineItem::class, 'from_item_id')->constrained()->cascadeOnDelete();
            $table->foreignIdFor(\App\Models\TimelineItem::class, 'to_item_id')->constrained()->cascadeOnDelete();

            // Indexes
            $table->string('type');
            $table->index(['type']);

            // Constraints
            $table->unique(['type', 'from_item_id', 'to_item_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_items_items');
        Schema::dropIfExists('t_facts_items');
        Schema::dropIfExists('t_facts');
        Schema::dropIfExists('t_items');
    }
};
