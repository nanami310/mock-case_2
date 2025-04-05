<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\AttendanceStatus;
use Carbon\Carbon;
use App\Models\BreakTime;
use App\Http\Requests\AttendanceRequest;

class AttendanceController extends Controller
{
    private function getAttendanceData($user)
    {
        return Attendance::where('user_id', $user->id)
            ->whereDate('date', today())
            ->first();
    }

    public function index()
    {
        $user = auth()->user();
        $attendance = $this->getAttendanceData($user);

        if (!$attendance) {
            $attendance = new Attendance();
            $attendance->status = 'off_duty';
        }

        $currentYear = date('Y');
        $currentMonth = date('n');
        $attendanceRecords = Attendance::with('breaks')
            ->where('user_id', $user->id)
            ->whereYear('date', $currentYear)
            ->whereMonth('date', $currentMonth)
            ->get();

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
        return redirect()->back()->with('message', '');
    }

    $attendance = Attendance::updateOrCreate(
        ['user_id' => auth()->id(), 'date' => today()],
        [
            'status' => 'on_duty',
            'check_in' => now()->format('Y-m-d H:i:s'), // 日付と時間を結合
            'break_time' => 0,
            'total_hours' => 0,
        ]
    );

    return redirect()->back()->with('message', '');
}

public function checkOut(Request $request)
{
    $attendance = $this->getAttendanceData(auth()->user());

    if ($attendance && $attendance->status === 'on_duty') {
        $attendance->check_out = now()->format('Y-m-d H:i:s'); // 日付と時間を結合
        $attendance->total_hours = $attendance->check_out->diffInMinutes($attendance->check_in) - $attendance->break_time;
        $attendance->status = 'off_work';
        $attendance->save();
        return redirect()->back()->with('message', 'お疲れさまでした。');
    }

    return redirect()->back()->with('message', '');
}

    public function takeBreak(Request $request)
    {
        $attendance = $this->getAttendanceData(auth()->user());

        if ($attendance && $attendance->status === 'on_duty') {
            $attendance->status = 'on_break';
            $attendance->save();
            $attendance->breaks()->create(['start' => now(), 'end' => null]);
            return redirect()->back()->with('message', '');
        }

        return redirect()->back()->with('message', '');
    }

    public function returnFromBreak(Request $request)
    {
        $attendance = $this->getAttendanceData(auth()->user());

        if ($attendance && $attendance->status === 'on_break') {
            $attendance->status = 'on_duty';
            $break = $attendance->breaks()->latest()->first();

            if ($break) {
                $break->end = now();
                $break->save();

                $breakStart = Carbon::parse($break->start);
                $breakEnd = Carbon::parse($break->end);
                $attendance->break_time += $breakEnd->diffInMinutes($breakStart);
                $attendance->save();
            }
            return redirect()->back()->with('message', '');
        }

        return redirect()->back()->with('message', '');
    }

    public function attendanceList(Request $request)
    {
        $user = auth()->user();
        $year = $request->input('year', date('Y'));
        $month = $request->input('month', date('n'));

        $attendanceRecords = Attendance::where('user_id', $user->id)
                                       ->whereYear('date', $year)
                                       ->whereMonth('date', $month)
                                       ->get();

        return view('attendance.attendance_list', [
            'attendanceRecords' => $attendanceRecords,
            'currentYear' => $year,
            'currentMonth' => $month,
        ]);
    }

    public function show($id)
    {
        $attendance = Attendance::findOrFail($id);

        if ($attendance->user_id !== auth()->id()) {
            abort(403);
        }

        $attendance->date = Carbon::parse($attendance->date);
        $attendance->check_in = Carbon::parse($attendance->check_in);
        $attendance->check_out = Carbon::parse($attendance->check_out);

        foreach ($attendance->breaks as $break) {
            $break->start = Carbon::parse($break->start);
            $break->end = Carbon::parse($break->end);
        }

        $breakTimes = $attendance->breaks;
        return view('attendance.show', compact('attendance', 'breakTimes'));
    }

public function update(AttendanceRequest $request, $id)
{
    // バリデーションは AttendanceRequest で行われるため、ここでは省略

    // Attendanceから情報を取得
    $attendance = Attendance::findOrFail($id);
    
    // 既存の勤怠情報を更新
    $attendance->check_in = now()->format('Y-m-d') . ' ' . $request->check_in . ':00'; // 日付と時間を結合
    $attendance->check_out = now()->format('Y-m-d') . ' ' . $request->check_out . ':00'; // 日付と時間を結合
    $attendance->remarks = $request->remarks;
    $attendance->save();
    
    // 休憩時間の更新
    foreach ($attendance->breaks as $index => $break) {
        $break->start = now()->format('Y-m-d') . ' ' . $request->breaks[$index]['start'];
        $break->end = $request->breaks[$index]['end'] ? now()->format('Y-m-d') . ' ' . $request->breaks[$index]['end'] : null;
        $break->save();
    }
    
    // 新しい休憩時間の追加
    for ($i = count($attendance->breaks); $i < count($request->breaks); $i++) {
        $breakTime = new BreakTime();
        $breakTime->attendance_id = $attendance->id;
        $breakTime->start = now()->format('Y-m-d') . ' ' . $request->breaks[$i]['start'];
        $breakTime->end = $request->breaks[$i]['end'] ? now()->format('Y-m-d') . ' ' . $request->breaks[$i]['end'] : null;
        $breakTime->save();
    }
    
    // ビューを表示
    return view('attendance.show', [
        'attendance' => $attendance,
        'breakTimes' => $attendance->breaks,
    ]);
}

public function requestChange(AttendanceRequest $request)
{
    // バリデーションは AttendanceRequest で行われるため、ここでは省略

    $attendance = Attendance::findOrFail($request->attendance_id);
    $attendanceStatus = new AttendanceStatus();
    $attendanceStatus->attendance_id = $attendance->id;
    $attendanceStatus->user_id = auth()->id();
    $attendanceStatus->check_in = $request->check_in;
    $attendanceStatus->check_out = $request->check_out;
    
    // 休憩時間の処理
    if ($request->breaks) {
        foreach ($request->breaks as $break) {
            if (!empty($break['start']) && !empty($break['end'])) {
                $attendanceStatus->break_start = $break['start']; // 休憩開始時間を保存
                $attendanceStatus->break_end = $break['end']; // 休憩終了時間を保存
            }
        }
    }
    
    $attendanceStatus->remarks = $request->remarks;
    $attendanceStatus->status = 'pending';
    $attendanceStatus->save();
    
    return redirect()->back()->with('message', '勤怠時間および休憩時間の変更申請が送信されました。');
}

    public function requestList()
    {
        $user = auth()->user();
        $pendingRequests = AttendanceStatus::where('user_id', $user->id)
                                           ->where('status', 'pending')
                                           ->get()
                                           ->map(function ($request) {
                                               $request->created_at = Carbon::parse($request->created_at);
                                               return $request;
                                           });

        $approvedRequests = AttendanceStatus::where('user_id', $user->id)
                                            ->where('status', 'approved')
                                            ->get()
                                            ->map(function ($request) {
                                                $request->created_at = Carbon::parse($request->created_at);
                                                return $request;
                                            });

        return view('attendance.request_list', compact('pendingRequests', 'approvedRequests'));
    }

    public function breakTimeRequestList()
    {
        $user = auth()->user();
        $pendingBreakRequests = BreakTime::whereHas('attendance', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })->where('status', 'pending')->get();

        return view('attendance.break_time_request_list', compact('pendingBreakRequests'));
    }

    public function showAttendanceDetail($id)
    {
        $attendance = Attendance::findOrFail($id);
        $breakTimes = BreakTime::where('attendance_id', $attendance->id)->get();

        return view('attendance.detail', compact('attendance', 'breakTimes'));
    }
}
