<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;

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
        $attendance = $this->getAttendanceData(auth()->user());

        if (!$attendance) {
            $attendance = new Attendance();
            $attendance->status = 'off_duty'; // デフォルトの状態を修正
        }

        return view('attendance.index', compact('attendance'));
    }

    public function checkIn(Request $request)
    {
        $attendance = $this->getAttendanceData(auth()->user());

        if ($attendance && $attendance->status === 'on_duty') {
            return redirect()->back()->with('message', 'すでに出勤中です。');
        }

        $attendance = Attendance::updateOrCreate(
            ['user_id' => auth()->id(), 'date' => today()],
            ['status' => 'on_duty'] // ステータスを修正
        );

        return redirect()->back()->with('message', '出勤しました。');
    }

    public function checkOut(Request $request)
    {
        $attendance = $this->getAttendanceData(auth()->user());

        if ($attendance && $attendance->status === 'on_duty') {
            $attendance->status = 'off_work'; // ステータスを変更
            $attendance->save();
            return redirect()->back()->with('message', '退勤しました。');
        }

        return redirect()->back()->with('message', '出勤中ではありません。');
    }

    public function takeBreak(Request $request)
{
    $attendance = Attendance::where('user_id', auth()->id())->latest()->first();

    // ステータスが 'on_duty' の場合のみ休憩に入る
    if ($attendance && $attendance->status === 'on_duty') {
        $attendance->status = 'on_break';
        $attendance->save();
    }

    return redirect('/attendance');
}

public function returnFromBreak(Request $request)
{
    $attendance = Attendance::where('user_id', auth()->id())->latest()->first();

    // ステータスが 'on_break' の場合のみ勤務に戻る
    if ($attendance && $attendance->status === 'on_break') {
        $attendance->status = 'on_duty'; // または、適切なステータスに更新
        $attendance->save();
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
}
