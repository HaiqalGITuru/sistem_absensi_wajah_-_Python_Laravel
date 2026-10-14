<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceSession extends Model
{
    use HasFactory;

    // Baca dari database sistem Python (MySQL absensi_wajah), bukan sqlite lokal.
    protected $connection = 'python';

    protected $fillable = [
        'employee_id',
        'work_schedule_id',
        'work_date',
        'check_in_at',
        'check_out_at',
        'status',
    ];

    protected $casts = [
        'work_date' => 'date',
        'check_in_at' => 'datetime',
        'check_out_at' => 'datetime',
    ];

    public function employee()
    {
        return $this->belongsTo(
            Employee::class
        );
    }

    public function workSchedule()
    {
        return $this->belongsTo(
            WorkSchedule::class
        );
    }
}