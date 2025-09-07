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
        Schema::table('lead_questions', function (Blueprint $table) {
            // Modify enum values
            DB::statement("ALTER TABLE lead_questions MODIFY type ENUM('input', 'email', 'textarea', 'checkbox', 'radio') NOT NULL");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lead_questions', function (Blueprint $table) {
            // Revert to original enum values
            DB::statement("ALTER TABLE lead_questions MODIFY type ENUM('input', 'textarea', 'checkbox', 'radio') NOT NULL");
        });
    }
};
