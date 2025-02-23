<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;
use App\Models\User; // 追加

class AttendanceTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function current_datetime_is_displayed_correctly()
    {
        // テスト用ユーザーを作成してログイン
        $user = User::factory()->create();
        $this->actingAs($user);

        // 勤怠打刻画面を開く
        $response = $this->get('/attendance');
        // 現在の日時を取得
    $currentDateTime = \Carbon\Carbon::now();
    $dayOfWeek = ['日', '月', '火', '水', '木', '金', '土'];
    $currentDay = $dayOfWeek[$currentDateTime->dayOfWeek];
    
    $expectedDate = $currentDateTime->format('Y年n月j日') . " (" . $currentDay . ")";
    $expectedTime = $currentDateTime->format('H:i');

    // 画面に表示されている日時情報を確認
    $response->assertSee($expectedDate);
    $response->assertSee($expectedTime);
    }

    /** @test */
    public function status_is_displayed_as_off_duty()
    {
        // 勤務外のユーザーにログイン
        $this->actingAs(User::factory()->create(['status' => 'off_duty']));
        
        // 勤怠打刻画面を開く
        $response = $this->get('/attendance');

        // ステータスを確認
        $response->assertSee('勤務外');
    }

    /** @test */
    public function status_is_displayed_as_on_duty()
    {
        // 出勤中のユーザーにログイン
        $this->actingAs(User::factory()->create(['status' => 'on_duty']));
        
        // 勤怠打刻画面を開く
        $response = $this->get('/attendance');

        // ステータスを確認
        $response->assertSee('勤務中');
    }

    /** @test */
    public function status_is_displayed_as_on_break()
    {
        // 休憩中のユーザーにログイン
        $this->actingAs(User::factory()->create(['status' => 'on_break']));
        
        // 勤怠打刻画面を開く
        $response = $this->get('/attendance');

        // ステータスを確認
        $response->assertSee('休憩中');
    }

    /** @test */
    public function status_is_displayed_as_off_work()
    {
        // 退勤済のユーザーにログイン
        $this->actingAs(User::factory()->create(['status' => 'off_work']));
        
        // 勤怠打刻画面を開く
        $response = $this->get('/attendance');

        // ステータスを確認
        $response->assertSee('退勤済');
    }

    /** @test */
    public function attendance_button_works_correctly()
    {
        // 勤務外のユーザーにログイン
        $this->actingAs(User::factory()->create(['status' => 'off_duty']));
        
        // 出勤ボタンが表示されていることを確認
        $response = $this->get('/attendance');
        $response->assertSee('出勤');

        // 出勤の処理を行う
        $this->post('/attendance/check-in');

        // ステータスが「勤務中」になることを確認
        $response = $this->get('/attendance');
        $response->assertSee('勤務中');
    }

    /** @test */
    public function check_in_can_only_be_done_once_a_day()
    {
        // 退勤済のユーザーにログイン
        $user = User::factory()->create(['status' => 'off_work']);
        $this->actingAs($user);

        // 勤務ボタンが表示されないことを確認
        $response = $this->get('/attendance');
        $response->assertDontSee('出勤');
    }

    /** @test */
    public function break_button_works_correctly()
    {
        // 出勤中のユーザーにログイン
        $this->actingAs(User::factory()->create(['status' => 'on_duty']));
        
        // 休憩入ボタンが表示されていることを確認
        $response = $this->get('/attendance');
        $response->assertSee('休憩入');

        // 休憩の処理を行う
        $this->post('/attendance/take-break');

        // ステータスが「休憩中」になることを確認
        $response = $this->get('/attendance');
        $response->assertSee('休憩中');
    }

    /** @test */
    public function breaks_can_be_taken_multiple_times_a_day()
    {
        // 出勤中のユーザーにログイン
        $user = User::factory()->create(['status' => 'on_duty']);
        $this->actingAs($user);
        
        // 休憩入と休憩戻の処理を行う
        $this->post('/attendance/take-break');
        $this->post('/attendance/return-from-break');

        // 再度休憩入の処理を行う
        $this->post('/attendance/take-break');

        // 休憩入ボタンが表示されることを確認
        $response = $this->get('/attendance');
        $response->assertSee('休憩入');
    }

    /** @test */
    public function return_from_break_button_works_correctly()
    {
        // 出勤中のユーザーにログイン
        $user = User::factory()->create(['status' => 'on_duty']);
        $this->actingAs($user);

        // 休憩入の処理を行う
        $this->post('/attendance/take-break');

        // 休憩戻の処理を行う
        $this->post('/attendance/return-from-break');

        // ステータスが「出勤中」に変更されることを確認
        $response = $this->get('/attendance');
        $response->assertSee('勤務中');
    }

    /** @test */
    public function return_from_break_can_be_done_multiple_times_a_day()
    {
        // 出勤中のユーザーにログイン
        $user = User::factory()->create(['status' => 'on_duty']);
        $this->actingAs($user);

        // 休憩入と休憩戻の処理を行う
        $this->post('/attendance/take-break');
        $this->post('/attendance/return-from-break');

        // 再度休憩入の処理を行う
        $this->post('/attendance/take-break');
        $this->post('/attendance/return-from-break');

        // 休憩戻ボタンが表示されることを確認
        $response = $this->get('/attendance');
        $response->assertSee('休憩戻');
    }

    /** @test */
    public function check_out_button_works_correctly()
    {
        // 勤務中のユーザーにログイン
        $user = User::factory()->create(['status' => 'on_duty']);
        $this->actingAs($user);

        // 退勤ボタンが表示されていることを確認
        $response = $this->get('/attendance');
        $response->assertSee('退勤');

        // 退勤の処理を行う
        $this->post('/attendance/check-out');

        // ステータスが「退勤済」になることを確認
        $response = $this->get('/attendance');
        $response->assertSee('退勤済');
    }
}
