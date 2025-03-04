<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Attendance;

class AttendanceSeeder extends Seeder
{
    public function run()
    {
        Attendance::factory()
            ->count(50)
            ->create()
            ->each(function ($attendance) {
                // 各出勤データに対して休憩時間を生成
                $breakCount = rand(1, 3); // 各出勤に対して1〜3回の休憩を生成
                \App\Models\BreakTime::factory()->count($breakCount)->create([
                    'attendance_id' => $attendance->id,
                ]);
            });
    }
}