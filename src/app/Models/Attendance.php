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

    public function hasCheckedInToday()
    {
        return $this->status === 'on_duty'; // ステータスを 'on_duty' に変更
    }

    public function breaks()
    {
        return $this->hasMany(BreakTime::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}