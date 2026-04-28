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
        Schema::create('task_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_completion_id')->constrained()->onDelete('cascade');
            $table->foreignId('task_assignment_id')->constrained()->onDelete('cascade');
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            $table->foreignId('reviewed_by')->constrained('users')->onDelete('cascade');
            $table->enum('status', ['pending', 'approved', 'declined'])->default('pending');
            $table->text('note')->nullable(); // catatan HRD saat decline
            $table->date('review_date');
            $table->timestamp('reviewed_at')->nullable();
            // Kalau decline, task revisi dibuat di hari ini
            $table->date('revision_due_date')->nullable();
            $table->foreignId('revision_assignment_id')
                ->nullable()->constrained('task_assignments')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('task_reviews');
    }
};
