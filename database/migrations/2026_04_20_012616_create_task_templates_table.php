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
        Schema::create('task_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            // Target: all, division, employee
            $table->enum('target_type', ['all', 'division', 'employee']);
            $table->string('target_value')->nullable(); // nama divisi atau employee_id
            // Jadwal: kapan task ini harus dikerjakan
            $table->date('scheduled_date');
            // Apakah laporan deskriptif wajib diisi
            $table->boolean('report_required')->default(false);
            $table->text('report_instruction')->nullable(); // panduan isi laporan
            // Apakah task ini bisa carry-over kalau belum selesai
            $table->boolean('carry_over')->default(true);
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('task_templates');
    }
};
