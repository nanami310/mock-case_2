<?php
namespace Database\Factories;

use App\Models\Attendance;
use Illuminate\Database\Eloquent\Factories\Factory;

class AttendanceFactory extends Factory
{
    protected $model = Attendance::class;

    public function definition()
    {
        return [
            'user_id' => \App\Models\User::factory(), // ユーザーをファクトリで生成
            'status' => $this->faker->randomElement(['off', 'working', 'checked_out']),
            'check_in' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'check_out' => $this->faker->dateTimeBetween('now', '+1 month'),
            'break_time' => 0, // 初期値は0
        ];
    }
}