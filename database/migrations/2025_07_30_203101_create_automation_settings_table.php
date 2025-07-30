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

            // What action to take (only email for now)
            $table->string('action')->default('email');

            // Which email template to use
            $table->foreignId('template_id')->constrained('email_templates')->onDelete('cascade');

            // When to send: immediately or manual
            $table->enum('send_method', ['immediate','manual'])->default('immediate');

            $table->timestamps();

            // One setting per property
            $table->unique('property_id');

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
