<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FaceTemplate extends Model
{
    // Tabel milik sistem Python (MySQL absensi_wajah).
    protected $connection = 'python';

    protected $table = 'face_templates';

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }
}
