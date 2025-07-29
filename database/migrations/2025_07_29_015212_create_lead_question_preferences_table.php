<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('lead_question_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')
                  ->constrained()
                  ->onDelete('cascade');
            $table->json('selected_headings');
            $table->timestamps();

            $table->unique('property_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lead_question_preferences');
    }
};
