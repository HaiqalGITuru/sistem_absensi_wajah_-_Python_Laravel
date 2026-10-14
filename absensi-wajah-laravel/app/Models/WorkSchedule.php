<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkSchedule extends Model
{
    use HasFactory;

    protected $connection = 'python';

    protected $fillable = [
        'name',
        'late_tolerance_minutes',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function employees()
    {
        return $this->hasMany(Employee::class, 'work_schedule_id');
    }

    public function attendanceSessions()
    {
        return $this->hasMany(AttendanceSession::class);
    }

    public function days()
    {
        return $this->hasMany(WorkScheduleDay::class, 'work_schedule_id')
            ->orderBy('weekday');
    }

    // Ringkasan jam per hari untuk kolom "Jam Kerja" di daftar.
    public function getSummaryAttribute(): array
    {
        $names = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];

        return $this->days->map(function ($d) use ($names) {
            return ($names[$d->weekday] ?? $d->weekday) . ' ' . $d->start_time . '–' . $d->end_time;
        })->all();
    }
}
