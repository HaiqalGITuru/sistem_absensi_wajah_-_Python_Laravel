<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    // Baca dari database sistem Python (MySQL absensi_wajah), bukan sqlite lokal.
    protected $connection = 'python';

    protected $fillable = [
        'employee_code',
        'full_name',
        'department',
        'is_active',
        'work_schedule_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function workSchedule()
    {
        return $this->belongsTo(
            WorkSchedule::class,
            'work_schedule_id'
        );
    }

    public function attendanceSessions()
    {
        return $this->hasMany(
            AttendanceSession::class
        );
    }

    public function faceTemplates()
    {
        return $this->hasMany(FaceTemplate::class, 'employee_id');
    }

    // Jumlah templat wajah. Pakai hasil withCount() jika ada, agar tanpa query tambahan.
    public function getFaceCountAttribute(): int
    {
        return (int) ($this->face_templates_count ?? $this->faceTemplates()->count());
    }

    public function getFaceRegisteredAttribute(): bool
    {
        return $this->face_count > 0;
    }
}
