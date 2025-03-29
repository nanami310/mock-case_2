<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\AttendanceStatus;
use Carbon\Carbon;
use App\Models\BreakTime;

class AdminAttendanceController extends Controller
{
    public function adminAttendanceIndex()
    {
        return $this->index();
    }

    public function index()
    {
        $attendanceRecords = Attendance::with('user')->get();
        $currentYear = now()->year;
        $currentMonth = now()->month;
        $currentDay = now()->day;

        return view('admin.attendance_list', compact('attendanceRecords', 'currentYear', 'currentMonth', 'currentDay'));
    }

    public function show($id)
{
    // 出勤情報を取得し、関連する休憩情報もロード
    $attendance = Attendance::with('breaks')->findOrFail($id);
$breakTimes = $attendance->breaks; // breaksを取得
    
    

    // その後の処理はここに続く...
    return view('admin.attendance_show', compact('attendance', 'breakTimes'));
}

public function update(Request $request, $id)
{
    // バリデーションの変更
    $request->validate([
        'check_in' => 'required|date_format:H:i',
        'check_out' => 'required|date_format:H:i',
        'breaks.*.start' => 'nullable|date_format:H:i',
        'breaks.*.end' => 'nullable|date_format:H:i',
    ]);

    $attendance = Attendance::findOrFail($id);
    $date = now()->format('Y-m-d');

    // check_in と check_out の値を設定
    $attendance->check_in = \Carbon\Carbon::parse($date . ' ' . $request->input('check_in') . ':00');
    $attendance->check_out = \Carbon\Carbon::parse($date . ' ' . $request->input('check_out') . ':00');

    // breaks の処理
    if ($request->has('breaks') && is_array($request->input('breaks'))) {
        $attendance->breaks()->delete(); // 既存の休憩時間を削除
        foreach ($request->input('breaks') as $break) {
            $start = !empty($break['start']) ? \Carbon\Carbon::parse($date . ' ' . $break['start'] . ':00') : null; // 開始時刻
            $end = !empty($break['end']) ? \Carbon\Carbon::parse($date . ' ' . $break['end'] . ':00') : null; // 終了時刻

            $attendance->breaks()->create([
                'start' => $start,
                'end' => $end,
            ]);
        }
    }

    $attendance->save(); // 勤怠情報を保存
    return redirect()->route('admin.attendance.list')->with('success', '勤怠情報が更新されました。');
}


public function approve($id)
{
    $attendanceStatus = AttendanceStatus::where('attendance_id', $id)->first();

    if ($attendanceStatus) {
        $attendanceStatus->status = 'approved';
        $attendanceStatus->save();

        $attendance = Attendance::findOrFail($id);
        $date = now()->format('Y-m-d');

        if ($attendanceStatus->check_in) {
            $attendance->check_in = $date . ' ' . \Carbon\Carbon::createFromFormat('H:i:s', $attendanceStatus->check_in)->format('H:i:s');
        }

        if ($attendanceStatus->check_out) {
            $attendance->check_out = $date . ' ' . \Carbon\Carbon::createFromFormat('H:i:s', $attendanceStatus->check_out)->format('H:i:s');
        }

        // 休憩時間を更新
        if ($attendanceStatus->break_start) {
            $attendance->break_start = $date . ' ' . \Carbon\Carbon::createFromFormat('H:i:s', $attendanceStatus->break_start)->format('H:i:s');
        }

        if ($attendanceStatus->break_end) {
            $attendance->break_end = $date . ' ' . \Carbon\Carbon::createFromFormat('H:i:s', $attendanceStatus->break_end)->format('H:i:s');
        }

        $attendance->save();
        return redirect()->back()->with('success', 'ステータスが承認されました。');
    } else {
        return redirect()->back()->with('error', '関連するステータスが見つかりません。');
    }
}

    public function reject($id)
    {
        $attendanceStatus = AttendanceStatus::where('attendance_id', $id)->first();

        if ($attendanceStatus) {
            $attendanceStatus->status = 'rejected';
            $attendanceStatus->save();

            return redirect()->back()->with('success', 'ステータスが拒否されました。');
        } else {
            return redirect()->back()->with('error', '関連するステータスが見つかりません。');
        }
    }

    public function approveBreakTime($id)
    {
        $breakTime = BreakTime::findOrFail($id);
        $breakTime->status = 'approved';
        $breakTime->save();

        $attendanceStatus = AttendanceStatus::where('attendance_id', $breakTime->attendance_id)->first();
        if ($attendanceStatus) {
            $attendanceStatus->break_start = $breakTime->start->format('H:i:s');
            $attendanceStatus->break_end = $breakTime->end->format('H:i:s');
            $attendanceStatus->save();
        }

        return redirect()->back()->with('success', '休憩時間が承認されました。');
    }

    public function listRequests()
    {
        $pendingRequests = AttendanceStatus::with('user', 'attendance')
            ->where('status', 'pending')
            ->get()
            ->map(function ($request) {
                $request->created_at = Carbon::parse($request->created_at);
                return $request;
            });

        $approvedRequests = AttendanceStatus::with('user', 'attendance')
            ->where('status', 'approved')
            ->get()
            ->map(function ($request) {
                $request->created_at = Carbon::parse($request->created_at);
                return $request;
            });

        return view('admin.attendance_request_list', compact('pendingRequests', 'approvedRequests'));
    }
}
