<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\User;
use Carbon\Carbon;

class AttendanceSeeder extends Seeder
{
    public function run()
    {
        $users = User::all(); // すべてのユーザーを取得

        foreach ($users as $user) {
            // ダミーの勤怠記録を作成
            for ($i = 0; $i < 10; $i++) {
                $date = Carbon::today()->subDays($i); // 過去10日間のデータ

                $attendance = Attendance::create([
                    'user_id' => $user->id,
                    'date' => $date,
                    'check_in' => $date->copy()->setTime(9, 0), // 9:00に出勤
                    'check_out' => $date->copy()->setTime(17, 0), // 17:00に退勤
                    'status' => 'on_duty',
                ]);

                // ダミーの休憩時間を作成
                BreakTime::create([
                    'attendance_id' => $attendance->id,
                    'start' => $date->copy()->setTime(12, 0), // 12:00に休憩開始
                    'end' => $date->copy()->setTime(13, 0), // 13:00に休憩終了
                ]);
            }
        }
    }
}
