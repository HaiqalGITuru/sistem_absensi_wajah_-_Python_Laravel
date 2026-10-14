<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkScheduleDay extends Model
{
    protected $connection = 'python';
    protected $table = 'work_schedule_days';

    // Tampilkan jam sebagai HH:MM (MySQL TIME mengembalikan HH:MM:SS).
    public function getStartTimeAttribute($value)
    {
        return $value ? substr((string) $value, 0, 5) : $value;
    }

    public function getEndTimeAttribute($value)
    {
        return $value ? substr((string) $value, 0, 5) : $value;
    }
}
