<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('task_completions', function (Blueprint $table) {
            // Untuk upload file
            $table->string('attachment_path')->nullable()->after('report');
            $table->string('attachment_name')->nullable()->after('attachment_path');
            // Untuk link Google Drive / URL
            $table->string('attachment_url')->nullable()->after('attachment_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('task_completions', function (Blueprint $table) {
            //
        });
    }
};
