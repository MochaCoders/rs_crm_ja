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
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            // if appointments belong to a property/listing:
            $table->foreignId('property_id')
                  ->constrained()
                  ->onDelete('cascade');

            $table->string('email');                 // person scheduling the visit
            $table->dateTime('scheduled_at');        // visit date/time (store in UTC ideally)
            $table->string('status')->default('scheduled');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
