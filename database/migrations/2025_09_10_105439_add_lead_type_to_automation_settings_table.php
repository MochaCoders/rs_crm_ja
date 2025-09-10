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
        Schema::table('automation_settings', function (Blueprint $table) {
            // Add the new enum column
            $table->enum('lead_type', ['qualified', 'unqualified'])
                  ->default('qualified')
                  ->after('send_method'); // places it after send_method for clarity
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('automation_settings', function (Blueprint $table) {
            $table->dropColumn('lead_type');
        });
    }
};
