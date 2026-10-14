<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_sessions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('employee_id')
                ->constrained('employees')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreignId('work_schedule_id')
                ->nullable()
                ->constrained('work_schedules')
                ->nullOnDelete();

            $table->date('work_date');

            $table->dateTime('check_in_at')
                ->nullable();

            $table->dateTime('check_out_at')
                ->nullable();

            $table->string('status', 30)
                ->default('present');

            $table->timestamps();

            $table->index([
                'employee_id',
                'work_date',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_sessions');
    }
};