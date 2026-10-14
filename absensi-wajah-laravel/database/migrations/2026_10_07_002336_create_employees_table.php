<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();

            $table->string('employee_code', 50)->unique();

            $table->string('full_name');

            $table->string('department');

            $table->boolean('is_active')->default(true);

            $table->foreignId('work_schedule_id')
                ->nullable()
                ->constrained('work_schedules')
                ->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};