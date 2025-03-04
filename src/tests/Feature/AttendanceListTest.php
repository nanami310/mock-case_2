<?php
namespace Tests\Feature;

use App\Models\User;
use App\Models\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class AttendanceListTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // テスト用ユーザーを作成
        $this->user = User::factory()->create();
        Carbon::setTestNow('2025-03-02 14:30:00'); // 現在の日時を設定
    }

    /** @test */
    public function it_displays_user_attendance_records()
    {
        // ユーザーでログイン
        $this->actingAs($this->user);

        // 勤怠情報を作成
Attendance::create([
    'user_id' => $this->user->id,
    'status' => 'on_duty',
    'check_in' => now(),
    'check_out' => now()->addHours(8),
    'break_time' => 60, // 休憩時間を1時間（60分）に設定
    'date' => today(),
]);

        // 勤怠一覧ページを開く
        $response = $this->get('/attendance/list');

        // 自分の勤怠情報が表示されていることを確認
        $response->assertStatus(200);
    }
/** @test */
public function it_displays_current_month_on_attendance_list_page()
{
    $this->actingAs($this->user);

    // 勤怠情報を作成
    Attendance::create([
        'user_id' => $this->user->id,
        'status' => 'on_duty',
        'check_in' => now(),
        'check_out' => now()->addHours(8),
        'break_time' => 60, // 休憩時間を設定
        'date' => today(),
    ]);

    $response = $this->get('/attendance/list');
    $response->assertStatus(200);
    
    // 期待される出力を修正（スペースを削除）
    $currentYear = now()->format('Y');
    $currentMonth = now()->format('n');
    $response->assertSee("{$currentYear}年 {$currentMonth}月"); // スペースを削除
}

/** @test */
public function it_displays_previous_month_attendance_records()
{
    $this->actingAs($this->user);

    // 前月の勤怠情報を作成
    $attendanceRecord = Attendance::create([
        'user_id' => $this->user->id,
        'status' => 'on_duty',
        'check_in' => now()->subMonth()->startOfMonth(),
        'check_out' => now()->subMonth()->startOfMonth()->addHours(8),
        'break_time' => 60,
        'date' => now()->subMonth()->startOfMonth(),
    ]);

    // 前月のデータを取得するためのリクエスト
    $response = $this->get('/attendance/list?month=' . now()->subMonth()->month . '&year=' . now()->subMonth()->year);
    
    // ステータスコードの確認
    $response->assertStatus(200);
    
    // 勤怠情報がレスポンスに含まれていることを確認
    $response->assertSee($attendanceRecord->date->format('Y-m-d'));
    // check_in の確認
    $response->assertSee($attendanceRecord->check_in ? \Carbon\Carbon::parse($attendanceRecord->check_in)->format('H:i') : '');
    
    // check_out の確認
    $response->assertSee($attendanceRecord->check_out ? \Carbon\Carbon::parse($attendanceRecord->check_out)->format('H:i') : '');

    // 休憩時間の確認
    $totalBreakTime = $attendanceRecord->break_time; // ここでは直接プロパティを使用
    $response->assertSee($totalBreakTime . ' 分');

    // 合計時間の計算
    if ($attendanceRecord->check_in && $attendanceRecord->check_out) {
        $checkIn = \Carbon\Carbon::parse($attendanceRecord->check_in);
        $checkOut = \Carbon\Carbon::parse($attendanceRecord->check_out);
        $totalHours = $checkOut->diffInMinutes($checkIn) - $totalBreakTime;
        $hours = floor($totalHours / 60);
        $minutes = $totalHours % 60;
        $response->assertSee($hours . ' 時間 ' . $minutes . ' 分');
    } else {
        $response->assertSee('');
    }
}

/** @test */
public function it_displays_next_month_attendance_records()
{
    $this->actingAs($this->user);

    // 翌月の勤怠情報を作成
    $attendanceRecord = Attendance::create([
        'user_id' => $this->user->id,
        'status' => 'on_duty',
        'check_in' => now()->addMonth()->startOfMonth(),
        'check_out' => now()->addMonth()->startOfMonth()->addHours(8),
        'break_time' => 60, // 休憩時間を設定
        'date' => now()->addMonth()->startOfMonth(),
    ]);

    // 翌月のデータを取得するためのリクエスト
    $response = $this->get('/attendance/list?month=' . now()->addMonth()->month . '&year=' . now()->addMonth()->year);
    
    // ステータスコードの確認
    $response->assertStatus(200);
    
    // 勤怠情報がレスポンスに含まれていることを確認
    $response->assertSee($attendanceRecord->date->format('Y-m-d'));
    
    // check_in の確認
    $response->assertSee($attendanceRecord->check_in ? \Carbon\Carbon::parse($attendanceRecord->check_in)->format('H:i') : '');
    
    // check_out の確認
    $response->assertSee($attendanceRecord->check_out ? \Carbon\Carbon::parse($attendanceRecord->check_out)->format('H:i') : '');

    // 休憩時間の確認
    $totalBreakTime = $attendanceRecord->break_time; // ここでは直接プロパティを使用
    $response->assertSee($totalBreakTime . ' 分');

    // 合計時間の計算
    if ($attendanceRecord->check_in && $attendanceRecord->check_out) {
        $checkIn = \Carbon\Carbon::parse($attendanceRecord->check_in);
        $checkOut = \Carbon\Carbon::parse($attendanceRecord->check_out);
        $totalHours = $checkOut->diffInMinutes($checkIn) - $totalBreakTime;
        $hours = floor($totalHours / 60);
        $minutes = $totalHours % 60;
        $response->assertSee($hours . ' 時間 ' . $minutes . ' 分');
    } else {
        $response->assertSee('');
    }
}


    /** @test */
public function it_displays_attendance_records_with_detail_link()
{
    // ユーザーを認証
    $this->actingAs($this->user);

    // 勤怠情報を作成
    $attendance = Attendance::create([
        'user_id' => $this->user->id,
        'date' => now(),
        'check_in' => now()->subHours(8),
        'check_out' => now(),
        'status' => 'off_duty',
    ]);

    // 勤怠一覧を取得
    $response = $this->get('/attendance/list');

    // ステータスコードの確認
    $response->assertStatus(200);
    // 「詳細」リンクが表示されていることを確認
    $response->assertSee('詳細'); // リンクのテキストが表示されているか確認


    // リンクの URL が正しいことを確認
    $response->assertSee(route('attendance.show', $attendance->id)); // 正しい URL が表示されているか確認
}
    /** @test */
    public function it_records_check_in_time()
    {
        $this->actingAs($this->user);
        $response = $this->post('/attendance/check-in');
        $response->assertStatus(302); // リダイレクトを確認

        $this->assertDatabaseHas('attendances', [
            'user_id' => $this->user->id,
            'status' => 'on_duty',
        ]);
    }

/** @test */
public function it_records_break_time()
{
    $this->actingAs($this->user);

    // 勤怠情報を作成（出勤）
    Attendance::create([
        'user_id' => $this->user->id,
        'status' => 'on_duty',
        'check_in' => now(),
        'check_out' => now()->addHours(8),
        'break_time' => 0, // 初期の休憩時間は0
        'date' => today(),
    ]);

    // 休憩を開始
    $this->post('/attendance/take-break');

    // 5分間待機（300秒）
    \Carbon\Carbon::setTestNow(now()->addMinutes(5));

    // 休憩から戻る
    $this->post('/attendance/return-from-break');

    // 最新の勤怠情報を取得
    $attendance = Attendance::where('user_id', $this->user->id)->latest()->first();
    
    // ステータスが戻っていることを確認
    $this->assertDatabaseHas('attendances', [
        'user_id' => $this->user->id,
        'status' => 'on_duty',
    ]);

    // 休憩時間が正しく計算されているかを確認
    $this->assertGreaterThan(0, $attendance->break_time); // 休憩時間が0より大きいことを確認
}


    /** @test */
    public function it_records_check_out_time_and_calculates_total_hours()
    {
        $this->actingAs($this->user);
        $this->post('/attendance/check-in'); // 出勤
        sleep(2); // 2秒待機してから退勤する（テストのため）
        $this->post('/attendance/check-out'); // 退勤

        $attendance = Attendance::where('user_id', $this->user->id)->latest()->first();

        $this->assertDatabaseHas('attendances', [
            'user_id' => $this->user->id,
            'status' => 'off_work',
        ]);

        // 合計時間が正しく計算されているかを確認
        $this->assertEquals(0, $attendance->total_hours); // 休憩時間がない場合の合計時間
    }
}
