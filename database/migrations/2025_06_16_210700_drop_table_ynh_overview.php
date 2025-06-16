<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::dropIfExists('ynh_overview');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // There is no going back!
    }
};
