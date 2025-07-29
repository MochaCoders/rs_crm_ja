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

        Schema::create('lead_responses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('submission_id')->nullable()->index();
            $table->foreignId('lead_question_id')->constrained()->onDelete('cascade');
            $table->text('response');
            $table->timestamps();

            $table->foreign('submission_id')->references('id')->on('submissions')->onDelete('cascade');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lead_responses');
    }
};
