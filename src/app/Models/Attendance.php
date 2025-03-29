<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'status',
        'date',
        'check_in',
        'check_out',
        'total_hours',
    ];

    protected $casts = [
        'check_in' => 'datetime',
        'check_out' => 'datetime',
        'date' => 'date',
    ];

    public function hasCheckedInToday()
    {
        return $this->status === 'on_duty';
    }

    public function breaks()
    {
        return $this->hasMany(BreakTime::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function attendanceStatus()
    {
        return $this->hasOne(AttendanceStatus::class);
    }

    public function status()
    {
        return $this->hasOne(AttendanceStatus::class);
    }

    public function breakTimes()
    {
        return $this->hasMany(BreakTime::class);
    }
}