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

        Schema::create('automation_settings', function (Blueprint $table) {
            $table->id();

            // Link back to the property
            $table->foreignId('property_id')->constrained()->onDelete('cascade');

            // Action type (send_email, email_agent, schedule_visit)
            $table->string('action');

            // Optional fields depending on action type
            $table->foreignId('template_id')
                ->nullable()
                ->constrained('email_templates')
                ->nullOnDelete();

            $table->string('agent_email')->nullable();

            // When to send: immediately or manual
            $table->enum('send_method', ['immediate','manual'])->default('immediate');

            $table->timestamps();

            // Index for faster lookups
            $table->index(['property_id', 'action']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('automation_settings');
    }
};
