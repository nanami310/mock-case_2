<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Carbon\Carbon;

class StaffController extends Controller
{

    // スタッフ一覧画面
    public function index()
    {
        // スタッフの一覧を取得
        $staffs = User::all(); // 必要に応じて条件を追加

        return view('admin.staff_list', compact('staffs'));
    }

    // スタッフ別勤怠一覧画
    public function attendance(Request $request, $id)
{
    // 年月の取得
    $currentYear = $request->input('year', date('Y'));
    $currentMonth = $request->input('month', date('n'));

    // Carbonオブジェクトの作成
    $currentDate = Carbon::createFromDate($currentYear, $currentMonth, 1); // 日を1日に設定

    // 前月と翌月の計算
    $previousMonth = $currentDate->copy()->subMonth();
    $nextMonth = $currentDate->copy()->addMonth();

    // 勤怠情報の取得（ユーザー情報も一緒にロード）
    $attendanceRecords = Attendance::with('user') // userリレーションをロード
                                   ->where('user_id', $id)
                                   ->whereYear('date', $currentYear)
                                   ->whereMonth('date', $currentMonth)
                                   ->get();

    return view('admin.attendance_staff', [
        'id' => $id,
        'currentYear' => $currentDate->year,
        'currentMonth' => $currentDate->month,
        'previousMonth' => $previousMonth,
        'nextMonth' => $nextMonth,
        'attendanceRecords' => $attendanceRecords,
    ]);
}

    // CSV出力メソッド
    public function exportCsv(Request $request, $id)
{
    $year = $request->input('year', Carbon::now()->year);
    $month = $request->input('month', Carbon::now()->month);

    $attendanceRecords = Attendance::where('user_id', $id)
        ->whereYear('date', $year)
        ->whereMonth('date', $month)
        ->get();

    $csvFileName = "attendance_{$year}_{$month}.csv";

    $headers = [
        "Content-type" => "text/csv; charset=UTF-8",
        "Content-Disposition" => "attachment; filename={$csvFileName}",
        "Pragma" => "no-cache",
        "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
        "Expires" => "0",
    ];

    return response()->stream(function () use ($attendanceRecords) {
        $handle = fopen('php://output', 'w');

        // BOMを出力
        fwrite($handle, "\xEF\xBB\xBF");

        // ヘッダー行を書き込む
        fputcsv($handle, ['日付', '出勤時間', '退勤時間', '休憩時間', '合計']);

        foreach ($attendanceRecords as $record) {
            $totalBreakTime = $record->breaks->sum('duration'); // 休憩時間の合計
            $totalHours = '';

            if ($record->check_in && $record->check_out) {
                $checkIn = \Carbon\Carbon::parse($record->check_in);
                $checkOut = \Carbon\Carbon::parse($record->check_out);
                $totalMinutes = $checkOut->diffInMinutes($checkIn) - $totalBreakTime;
                $totalHours = floor($totalMinutes / 60) . ' 時間 ' . ($totalMinutes % 60) . ' 分';
            }

            fputcsv($handle, [
                $record->date,
                $record->check_in,
                $record->check_out,
                $totalBreakTime,
                $totalHours,
            ]);
        }

        fclose($handle); // ストリームを閉じる
    }, 200, $headers);
}
}