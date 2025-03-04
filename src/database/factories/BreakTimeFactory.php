<?php

namespace Database\Factories;

use App\Models\BreakTime;
use Illuminate\Database\Eloquent\Factories\Factory;

class BreakTimeFactory extends Factory
{
    protected $model = BreakTime::class;

    public function definition()
    {
        return [
            'attendance_id' => \App\Models\Attendance::factory(),
            'start' => $this->faker->dateTimeBetween('-1 hour', 'now'),
            'end' => $this->faker->dateTimeBetween('now', '+1 hour'),
        ];
    }
}