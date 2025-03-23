<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\AttendanceStatus; // AttendanceStatusモデルのインポート
use Carbon\Carbon;

class AdminAttendanceController extends Controller
{
    public function index()
    {
        $attendances = Attendance::with('user')->get(); // ユーザー情報を含めて全ての勤怠を取得
        return view('admin.attendance_list', compact('attendances'));
    }

    // 勤怠詳細画面を表示
    public function show($id)
{
    $attendance = Attendance::with('breaks')->findOrFail($id); // breaks リレーションを含めて取得
    return view('admin.attendance_show', compact('attendance'));
}

public function update(Request $request, $id)
{
    $request->validate([
        'check_in' => 'nullable|date_format:H:i',
        'check_out' => 'nullable|date_format:H:i',
        'breaks.*.start' => 'nullable|date_format:H:i',
        'breaks.*.end' => 'nullable|date_format:H:i',
    ]);

    $attendance = Attendance::findOrFail($id);

    // 現在の日付を取得
    $date = now()->format('Y-m-d'); // 現在の日付を取得または適切な日付を指定

    // check_in と check_out を適切な形式で設定
    if ($request->input('check_in')) {
        $attendance->check_in = $date . ' ' . $request->input('check_in') . ':00';
    }
    if ($request->input('check_out')) {
        $attendance->check_out = $date . ' ' . $request->input('check_out') . ':00';
    }

    if ($request->has('breaks') && is_array($request->input('breaks'))) {
        $attendance->breaks()->delete();
        foreach ($request->input('breaks') as $break) {
            $start = $date . ' ' . $break['start'] . ':00';
            $end = $date . ' ' . $break['end'] . ':00';
            
            $attendance->breaks()->create([
                'start' => $start,
                'end' => $end,
            ]);
        }
    }

    $attendance->save();

    return redirect()->route('admin.attendance.list')->with('success', '勤怠情報が更新されました。');
}

public function approve($id)
{
    // AttendanceStatusを取得
    $attendanceStatus = AttendanceStatus::where('attendance_id', $id)->first();

    // AttendanceStatusが存在するか確認
    if ($attendanceStatus) {
        // ステータスをapprovedに更新
        $attendanceStatus->status = 'approved';
        $attendanceStatus->save();

        // AttendancesTableを更新
        $attendance = Attendance::where('id', $id)->first();
        if ($attendance) {
            // 現在の日付を取得
            $date = now()->format('Y-m-d');

            // check_inとcheck_outをtimestamp形式に変換
            if ($attendanceStatus->check_in) {
                $attendance->check_in = \Carbon\Carbon::createFromFormat('H:i:s', $attendanceStatus->check_in)->setDateFrom($date);
            }

            if ($attendanceStatus->check_out) {
                $attendance->check_out = \Carbon\Carbon::createFromFormat('H:i:s', $attendanceStatus->check_out)->setDateFrom($date);
            }

            // 休憩時間を更新
            if ($attendanceStatus->break_start) {
                $attendance->break_start = \Carbon\Carbon::createFromFormat('H:i:s', $attendanceStatus->break_start)->setDateFrom($date);
            }

            if ($attendanceStatus->break_end) {
                $attendance->break_end = \Carbon\Carbon::createFromFormat('H:i:s', $attendanceStatus->break_end)->setDateFrom($date);
            }

            $attendance->save();
        }

        return redirect()->back()->with('success', 'ステータスが承認されました。');
    } else {
        return redirect()->back()->with('error', '関連するステータスが見つかりません。');
    }
}


public function reject($id)
{
    $attendance = Attendance::findOrFail($id);
    
    // ステータスを拒否に変更
    $attendance->status = 'rejected';
    $attendance->save();

    // リダイレクトまたはメッセージの表示
    return redirect()->back()->with('success', '拒否されました。');
}

public function approveBreakTime($id)
{
    // BreakTimeを取得
    $breakTime = BreakTime::findOrFail($id);

    // 承認処理（必要に応じてステータスを変更するなど）
    $breakTime->status = 'approved'; // ステータスを承認に設定
    $breakTime->save();

    // 休憩時間をAttendanceStatusに更新
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
    // 承認待ちの申請
    $pendingRequests = AttendanceStatus::with('user', 'attendance')
        ->where('status', 'pending')
        ->get()
        ->map(function ($request) {
            // created_atをCarbonオブジェクトに変換
            $request->created_at = Carbon::parse($request->created_at);
            return $request;
        });

    // 承認済みの申請
    $approvedRequests = AttendanceStatus::with('user', 'attendance')
        ->where('status', 'approved')
        ->get()
        ->map(function ($request) {
            // created_atをCarbonオブジェクトに変換
            $request->created_at = Carbon::parse($request->created_at);
            return $request;
        });

    return view('admin.attendance_request_list', compact('pendingRequests', 'approvedRequests'));
}

}
