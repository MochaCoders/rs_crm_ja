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
        Schema::create('lead_actions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')
                              ->constrained()
                              ->onDelete('cascade');
            $table->string('trigger');       // e.g. 'qualifies' or 'not_qualify'
            $table->string('action_type');   // e.g. 'email', or other action options
            $table->foreignId('template_id') // only if action_type==='email'
                  ->nullable()
                  ->constrained('email_templates')
                  ->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lead_actions');
    }
};
