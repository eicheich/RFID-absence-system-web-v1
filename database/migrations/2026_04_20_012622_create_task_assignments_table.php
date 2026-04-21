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
        Schema::create('task_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_template_id')->constrained()->onDelete('cascade');
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            // Tanggal aktual task ini harus dikerjakan karyawan ini
            // (bisa berbeda dari template kalau di-override)
            $table->date('scheduled_date');
            // Override khusus per karyawan
            $table->boolean('report_required')->nullable(); // null = ikut template
            $table->boolean('carry_over')->nullable(); // null = ikut template
            $table->boolean('is_carry_over')->default(false); // ini carry-over dari hari sebelumnya?
            $table->foreignId('original_assignment_id')  // referensi task asli kalau carry-over
                ->nullable()->constrained('task_assignments')->onDelete('set null');
            $table->enum('status', ['pending', 'done', 'carried_over'])->default('pending');
            $table->timestamps();

            $table->unique(
                ['task_template_id', 'employee_id', 'scheduled_date'],
                'task_assign_unique'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('task_assignments');
    }
};
