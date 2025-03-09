<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use Carbon\Carbon;
use App\Http\Requests\AttendanceRequest;

class AttendanceController extends Controller
{
    private function getAttendanceData($user)
    {
        return Attendance::where('user_id', $user->id)
            ->whereDate('created_at', today())
            ->first();
    }

    public function index()
{
    $user = auth()->user(); // 現在のユーザーを取得
    $attendance = $this->getAttendanceData($user);

    if (!$attendance) {
        $attendance = new Attendance();
        $attendance->status = 'off_duty'; // デフォルトの状態を修正
    }

    // 現在の月の勤怠情報を取得
    $currentYear = date('Y');
    $currentMonth = date('n');
    $attendanceRecords = Attendance::with('breaks')
        ->where('user_id', $user->id)
        ->whereYear('date', $currentYear)
        ->whereMonth('date', $currentMonth)
        ->get();

    // 前月と翌月の勤怠情報を取得
    $previousMonthRecords = Attendance::with('breaks')
        ->where('user_id', $user->id)
        ->whereYear('date', $currentYear)
        ->whereMonth('date', $currentMonth - 1)
        ->get();

    $nextMonthRecords = Attendance::with('breaks')
        ->where('user_id', $user->id)
        ->whereYear('date', $currentYear)
        ->whereMonth('date', $currentMonth + 1)
        ->get();

    return view('attendance.index', compact('attendance', 'attendanceRecords', 'previousMonthRecords', 'nextMonthRecords', 'currentYear', 'currentMonth'));
}

    public function checkIn(Request $request)
    {
        $attendance = $this->getAttendanceData(auth()->user());

        if ($attendance && $attendance->status === 'on_duty') {
            return redirect()->back()->with('message', 'すでに出勤中です。');
        }

        $attendance = Attendance::updateOrCreate(
            ['user_id' => auth()->id(), 'date' => today()],
            [
                'status' => 'on_duty',
                'check_in' => now(), // 出勤時間を保存
                'break_time' => 0, // 初期値を設定
                'total_hours' => 0, // 初期値を設定
            ]
        );

        return redirect()->back()->with('message', '出勤しました。');
    }

    public function checkOut(Request $request)
    {
        $attendance = $this->getAttendanceData(auth()->user());

        if ($attendance && $attendance->status === 'on_duty') {
            $attendance->check_out = now(); // 退勤時間を保存
            $attendance->total_hours = $attendance->check_out->diffInMinutes($attendance->check_in) - $attendance->break_time; // 合計時間を計算
            $attendance->status = 'off_work'; // ステータスを変更
            $attendance->save();
            return redirect()->back()->with('message', '退勤しました。');
        }

        return redirect()->back()->with('message', '出勤中ではありません。');
    }

    public function takeBreak(Request $request)
{
    $attendance = $this->getAttendanceData(auth()->user());

    if ($attendance && $attendance->status === 'on_duty') {
        $attendance->status = 'on_break';
        $attendance->save();

        // 休憩時間を新規作成
        $attendance->breaks()->create(['start' => now(), 'end' => null]); // end を null に設定
    }

    return redirect('/attendance');
}

public function returnFromBreak(Request $request)
{
    $attendance = $this->getAttendanceData(auth()->user());

    if ($attendance && $attendance->status === 'on_break') {
        $attendance->status = 'on_duty'; // 勤務に戻る
        $break = $attendance->breaks()->latest()->first(); // 最新の休憩を取得
        
        if ($break) {
            $break->end = now(); // 休憩終了時刻を保存
            $break->save();

            // Carbonインスタンスに変換してから差分を計算
            $breakStart = Carbon::parse($break->start);
            $breakEnd = Carbon::parse($break->end);

            // 休憩時間を計算して保存
            $attendance->break_time += $breakEnd->diffInMinutes($breakStart);
            $attendance->save();
        }
    }

    return redirect('/attendance');
}
    public function attendanceList(Request $request)
    {
        $user = auth()->user(); // 現在のユーザーを取得
        $year = $request->input('year', date('Y'));
        $month = $request->input('month', date('n'));

        // 勤怠情報を取得
        $attendanceRecords = Attendance::where('user_id', $user->id)
                                       ->whereYear('date', $year)
                                       ->whereMonth('date', $month)
                                       ->get();

        // ビューにデータを渡す
        return view('attendance.attendance_list', [
            'attendanceRecords' => $attendanceRecords,
            'currentYear' => $year,
            'currentMonth' => $month,
        ]);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function show($id)
{
    $attendance = Attendance::findOrFail($id);
    
    // ユーザーの確認
    if ($attendance->user_id !== auth()->id()) {
        abort(403);
    }

    // 日付をCarbonインスタンスに変換
    $attendance->date = Carbon::parse($attendance->date);
    $attendance->check_in = Carbon::parse($attendance->check_in);
    $attendance->check_out = Carbon::parse($attendance->check_out);
    
    // 休憩時間もCarbonインスタンスに変換
    foreach ($attendance->breaks as $break) {
        $break->start = Carbon::parse($break->start);
        $break->end = Carbon::parse($break->end);
    }

    // $breakTimesをビューに渡す
    $breakTimes = $attendance->breaks; // 休憩時間を取得
    return view('attendance.show', compact('attendance', 'breakTimes')); // $breakTimesを追加
}
    
public function update(AttendanceRequest $request, $id)
{
    $attendance = Attendance::findOrFail($id);
    
    // ユーザーの確認
    if ($attendance->user_id !== auth()->id()) {
        abort(403);
    }

    // ステータスが 'pending' の場合は更新できない
    if ($attendance->status === 'pending') {
        return redirect()->route('attendance.show', $attendance->id)->with('error', '承認待ちのため修正はできません。');
    }

    // 勤怠情報の更新
    $attendance->check_in = $request->input('check_in');
    $attendance->check_out = $request->input('check_out');
    $attendance->remarks = $request->input('remarks');

    // 休憩時間の更新
    $attendance->breaks()->delete(); // 既存の休憩を削除
    foreach ($request->input('breaks') as $break) {
        $attendance->breaks()->create([
            'start' => $break['start'],
            'end' => $break['end'],
        ]);
    }

    // ステータスを 'pending' に変更して修正申請を行う
    $attendance->status = 'pending';
    $attendance->save();

    return redirect()->route('attendance.show', $attendance->id)->with('message', '修正申請が完了しました。');
}

}
