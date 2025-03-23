<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceStatus extends Model
{
    protected $fillable = ['attendance_id', 'status'];

    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }

    public function user()
{
    return $this->belongsTo(User::class);
}
}
