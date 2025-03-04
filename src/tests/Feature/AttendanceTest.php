<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User; 
use App\Models\Attendance;

class AttendanceTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function current_datetime_is_displayed_correctly()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get('/attendance');
        $currentDateTime = \Carbon\Carbon::now();
        $dayOfWeek = ['日', '月', '火', '水', '木', '金', '土'];
        $currentDay = $dayOfWeek[$currentDateTime->dayOfWeek];
        
        $expectedDate = $currentDateTime->format('Y年n月j日') . " (" . $currentDay . ")";
        $expectedTime = $currentDateTime->format('H:i');

        $response->assertSee($expectedDate);
        $response->assertSee($expectedTime);
    }

    /** @test */
    public function status_is_displayed_as_off_duty()
    {
        $this->actingAs(User::factory()->create(['status' => 'off_duty']));
        
        $response = $this->get('/attendance');
        $response->assertSee('勤務外');
    }

    /** @test */
    public function status_is_displayed_as_on_duty()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // 勤怠データを作成
        Attendance::create([
            'user_id' => $user->id,
            'status' => 'on_duty',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->get('/attendance');
        $response->assertSee('勤務中');
    }

    /** @test */
public function status_is_displayed_as_on_break()
{
    $user = User::factory()->create();
    
    // 勤怠データを作成
    Attendance::create([
        'user_id' => $user->id,
        'status' => 'on_break',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    
    $this->actingAs($user);
    
    $response = $this->get('/attendance');
    $response->assertSee('休憩中');
}

public function status_is_displayed_as_off_work()
{
    $user = User::factory()->create();
    
    // 勤怠データを作成
    Attendance::create([
        'user_id' => $user->id,
        'status' => 'off_work',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    
    $this->actingAs($user);
    
    $response = $this->get('/attendance');
    $response->assertSee('退勤済');
}

    /** @test */
    public function attendance_button_works_correctly()
    {
        $user = User::factory()->create(['status' => 'off_duty']);
        $this->actingAs($user);
        
        $response = $this->get('/attendance');
        $response->assertSee('出勤');

        $this->post('/attendance/check-in');

        $response = $this->get('/attendance');
        $response->assertSee('勤務中');
    }
    /** @test */
public function check_in_can_only_be_done_once_a_day()
{
    $user = User::factory()->create();
    
    // 勤怠データを作成
    Attendance::create([
        'user_id' => $user->id,
        'status' => 'on_duty',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    
    $this->actingAs($user);
    
    $response = $this->get('/attendance');
    $response->assertDontSee('出勤');
}

/** @test */
public function break_button_works_correctly()
{
    $user = User::factory()->create();
    
    // 勤怠データを作成
    Attendance::create([
        'user_id' => $user->id,
        'status' => 'on_duty',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    
    $this->actingAs($user);
    
    $response = $this->get('/attendance');
    $response->assertSee('休憩入');

    $this->post('/attendance/take-break');

    $response = $this->get('/attendance');
    $response->assertSee('休憩中');
}

/** @test */
public function breaks_can_be_taken_multiple_times_a_day()
{
    // ユーザーを作成
    $user = User::factory()->create();

    // 勤怠データを作成
    Attendance::create([
        'user_id' => $user->id,
        'status' => 'on_duty',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    // ユーザーをログイン状態にする
    $this->actingAs($user);
    
    // 1回目の休憩を取る
    $this->post('/attendance/take-break');

    // 休憩中の状態を確認
    $response = $this->get('/attendance');
    $response->assertSee('休憩中');

    // 休憩から戻る
    $this->post('/attendance/return-from-break');

    // 勤務中の状態を確認
    $response = $this->get('/attendance');
    $response->assertSee('勤務中');

    // 2回目の休憩を取る
    $this->post('/attendance/take-break');

    // 再度、休憩中の状態を確認
    $response = $this->get('/attendance');
    $response->assertSee('休憩中');
}
/** @test */
public function return_from_break_button_works_correctly()
{
    $user = User::factory()->create();
    
    // 勤怠データを作成
    Attendance::create([
        'user_id' => $user->id,
        'status' => 'on_duty', // 初期状態を on_duty に設定
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    
    $this->actingAs($user);

    // 休憩を取る
    $this->post('/attendance/take-break');

    // 休憩から戻る
    $this->post('/attendance/return-from-break');

    $response = $this->get('/attendance');
    $response->assertSee('勤務中'); // 勤務中の状態を確認
}

/** @test */
public function return_from_break_can_be_done_multiple_times_a_day()
{
    // ユーザーを作成
    $user = User::factory()->create();

    // 勤怠データを作成（初期状態を on_duty に設定）
    Attendance::create([
        'user_id' => $user->id,
        'status' => 'on_duty',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    // ユーザーをログイン状態にする
    $this->actingAs($user);

    // 1回目の休憩を取る
    $this->post('/attendance/take-break');

    // 休憩から戻る
    $this->post('/attendance/return-from-break');

    // 勤務中の状態を確認
    $response = $this->get('/attendance');
    $response->assertSee('勤務中');

    // 2回目の休憩を取る
    $this->post('/attendance/take-break');

    // 休憩中の状態を確認
    $response = $this->get('/attendance');
    $response->assertSee('休憩中');

    // 休憩から戻る
    $this->post('/attendance/return-from-break');

    // 再度、勤務中の状態を確認
    $response = $this->get('/attendance');
    $response->assertSee('勤務中');
}
    /** @test */
public function check_out_button_works_correctly()
{
    $user = User::factory()->create();
    
    // 勤怠データを作成
    Attendance::create([
        'user_id' => $user->id,
        'status' => 'on_duty',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    
    $this->actingAs($user);

    $response = $this->get('/attendance');
    $response->assertSee('退勤');

    $this->post('/attendance/check-out');

    $response = $this->get('/attendance');
    $response->assertSee('退勤済');
}
}
