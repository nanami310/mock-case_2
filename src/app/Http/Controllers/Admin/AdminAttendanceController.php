<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\AttendanceStatus;
use Carbon\Carbon;
use App\Models\BreakTime;
use App\Http\Requests\AttendanceRequest;

class AdminAttendanceController extends Controller
{

    public function adminAttendanceIndex(Request $request)
    {
        // URLから年、月、日を取得
        $year = $request->input('year', now()->year);
        $month = $request->input('month', now()->month);
        $day = $request->input('day', now()->day);

        // indexメソッドを呼び出す
        return $this->index($year, $month, $day);
    }

    public function index($year, $month, $day)
    {
        // 指定された日付に基づいて出勤記録を取得
        $currentDate = Carbon::createFromDate($year, $month, $day);
        $attendanceRecords = Attendance::with('user')->whereDate('date', $currentDate)->get();

        // 現在の日付を取得
        $currentYear = $currentDate->year;
        $currentMonth = $currentDate->month;
        $currentDay = $currentDate->day;

        return view('admin.attendance_list', compact('attendanceRecords', 'currentYear', 'currentMonth', 'currentDay'));
    }


    public function show($id, Request $request)
    {
        // 日付を取得
        $date = $request->input('date');

        // 出勤情報を取得
        $attendance = Attendance::with('breaks')->where('user_id', $id)->where('date', $date)->first();

        // 勤怠情報がない場合は新しいオブジェクトを作成
        if (!$attendance) {
            $attendance = new Attendance();
            $attendance->user_id = $id; // ユーザーIDを設定
            $attendance->date = $date;   // 日付を設定
            $attendance->check_in = null; // 出勤時刻を空に
            $attendance->check_out = null; // 退勤時刻を空に
            $attendance->remarks = null;   // 備考を空に
        }

        $breakTimes = $attendance->breaks ?? collect(); // breaksを取得（空のコレクションを使用）

        return view('admin.attendance_show', compact('attendance', 'breakTimes'));
    }


    
public function update(AttendanceRequest $request, $id) // AttendanceRequestをタイプヒントにする
{
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

public function store(AttendanceRequest $request)
{
    // バリデーション済みのデータを取得
    $validated = $request->validated();

    // 新しい勤怠情報を作成
    $attendance = new Attendance();
    $attendance->user_id = $request->user()->id; // 現在のユーザーIDを設定
    $attendance->date = $validated['date']; // 日付を設定
    $attendance->check_in = \Carbon\Carbon::parse($validated['date'] . ' ' . $validated['check_in'] . ':00');
    $attendance->check_out = \Carbon\Carbon::parse($validated['date'] . ' ' . $validated['check_out'] . ':00');
    $attendance->remarks = $validated['remarks'] ?? null; // 備考を設定
    $attendance->save(); // 勤怠情報を保存

    // 休憩時間を保存
    if (isset($validated['breaks'])) {
        foreach ($validated['breaks'] as $break) {
            $start = !empty($break['start']) ? \Carbon\Carbon::parse($validated['date'] . ' ' . $break['start'] . ':00') : null;
            $end = !empty($break['end']) ? \Carbon\Carbon::parse($validated['date'] . ' ' . $break['end'] . ':00') : null;

            $attendance->breaks()->create([
                'start' => $start,
                'end' => $end,
            ]);
        }
    }

    // リダイレクトまたはレスポンス
    return redirect()->route('admin.attendance.list')->with('success', '勤怠情報が保存されました。');
}

public function approve($id)
{
    try {
        // AttendanceStatusを取得
        $attendanceStatus = AttendanceStatus::where('attendance_id', $id)->first();

        if ($attendanceStatus) {
            // ステータスを承認済みに更新
            $attendanceStatus->status = 'approved';
            $attendanceStatus->save();

            // Attendanceを取得
            $attendance = Attendance::findOrFail($id);
            $date = now()->format('Y-m-d');

            // 出勤時間を更新
            if ($attendanceStatus->check_in) {
                $attendance->check_in = $date . ' ' . \Carbon\Carbon::createFromFormat('H:i:s', $attendanceStatus->check_in)->format('H:i:s');
            }

            // 退勤時間を更新
            if ($attendanceStatus->check_out) {
                $attendance->check_out = $date . ' ' . \Carbon\Carbon::createFromFormat('H:i:s', $attendanceStatus->check_out)->format('H:i:s');
            }

            // Attendanceを保存
            $attendance->save();

            // BreakTimeの作成または更新
            $breakTime = BreakTime::where('attendance_id', $attendance->id)->first();
            
            // 休憩開始時刻と終了時刻を設定
            $breakStart = $attendanceStatus->break_start ? $date . ' ' . \Carbon\Carbon::createFromFormat('H:i:s', $attendanceStatus->break_start)->format('H:i:s') : null;
            $breakEnd = $attendanceStatus->break_end ? $date . ' ' . \Carbon\Carbon::createFromFormat('H:i:s', $attendanceStatus->break_end)->format('H:i:s') : null;

            if ($breakTime) {
                // 既存の休憩時間を更新
                $breakTime->start = $breakStart;
                $breakTime->end = $breakEnd;
                $breakTime->save();
            } else {
                // 新しい休憩時間を作成
                BreakTime::create([
                    'attendance_id' => $attendance->id,
                    'start' => $breakStart,
                    'end' => $breakEnd,
                ]);
            }

            return redirect()->back()->with('success', 'ステータスが承認されました。');
        } else {
            return redirect()->back()->with('error', '関連するステータスが見つかりません。');
        }
    } catch (\Exception $e) {
        return redirect()->back()->with('error', 'エラーが発生しました: ' . $e->getMessage());
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
    public function showCorrectionRequest($id)
{
    $attendanceStatus = AttendanceStatus::with('user')->where('attendance_id', $id)->firstOrFail();
    return view('admin.attendance_correction_approve', compact('attendanceStatus'));
}
}
