<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\AttendanceStatus;
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

public function update(Request $request, $id)
{
    // バリデーション
    $request->validate([
        'check_in' => 'required|date_format:H:i',
        'check_out' => 'required|date_format:H:i',
        'remarks' => 'required|string',
        'breaks.*.start' => 'required|date_format:H:i',
        'breaks.*.end' => 'required|date_format:H:i',
    ]);

    // AttendancesTableから情報を取得
    $attendance = Attendance::findOrFail($id);
    $breakTimes = BreakTime::where('attendance_id', $id)->get();

    // AttendanceStatus を新規作成
    $attendanceStatus = new AttendanceStatus();
    $attendanceStatus->attendance_id = $attendance->id; // 勤怠IDを設定
    $attendanceStatus->check_in = $request->check_in; // フォームからのチェックイン
    $attendanceStatus->check_out = $request->check_out; // フォームからのチェックアウト
    $attendanceStatus->remarks = $request->remarks; // フォームからの備考
    $attendanceStatus->status = 'pending'; // 初期ステータスを 'pending' に設定
    $attendanceStatus->save();

    // 休憩時間の保存
    foreach ($request->breaks as $break) {
        $breakTime = new BreakTime();
        $breakTime->attendance_id = $attendance->id; // 元の勤怠IDを設定
        $breakTime->start = $break['start'];
        $breakTime->end = $break['end'];
        $breakTime->save();
    }

    // JSONレスポンスを返す
    return response()->json(['message' => '勤怠情報が保存されました。'], 200);
}

public function requestChange(Request $request, $id)
{
    // バリデーション
    $request->validate([
        'attendance_id' => 'required|exists:attendances,id',
        'check_in' => 'nullable|date_format:H:i',
        'check_out' => 'nullable|date_format:H:i',
        'new_break_start' => 'nullable|date_format:H:i',
        'new_break_end' => 'nullable|date_format:H:i',
        'remarks' => 'nullable|string',
    ]);

    // 勤怠情報を取得
    $attendance = Attendance::findOrFail($request->attendance_id);

    // AttendanceStatus を新規作成
    $attendanceStatus = new AttendanceStatus();
    $attendanceStatus->attendance_id = $attendance->id;
    $attendanceStatus->user_id = auth()->id();
    $attendanceStatus->check_in = $request->check_in;
    $attendanceStatus->check_out = $request->check_out;
    $attendanceStatus->remarks = $request->remarks;
    $attendanceStatus->status = 'pending';
    $attendanceStatus->save();

    // 新しい休憩時間の保存
    if ($request->new_break_start && $request->new_break_end) {
        $breakTime = new BreakTime();
        $breakTime->attendance_id = $attendance->id;
        $breakTime->start = \Carbon\Carbon::createFromFormat('H:i', $request->new_break_start)->setDate(now()->year, now()->month, now()->day);
        $breakTime->end = \Carbon\Carbon::createFromFormat('H:i', $request->new_break_end)->setDate(now()->year, now()->month, now()->day);
        $breakTime->save();
    }

    return redirect()->back()->with('message', '勤怠時間の変更申請が送信されました。');
}

public function requestList()
{
    $user = auth()->user(); // 現在のユーザーを取得

    // 承認待ちの申請を取得
    $pendingRequests = AttendanceStatus::where('user_id', $user->id)
                                       ->where('status', 'pending')
                                       ->get();

    // 承認済みの申請を取得
    $approvedRequests = AttendanceStatus::where('user_id', $user->id)
                                        ->where('status', 'approved')
                                        ->get();

    return view('attendance.request_list', compact('pendingRequests', 'approvedRequests'));
}

public function requestBreakTimeChange(Request $request)
{
    // バリデーション
    $request->validate([
        'attendance_id' => 'required|exists:attendances,id', // 勤怠IDが必要
        'start' => 'required|date_format:H:i', // 休憩開始時間
        'end' => 'nullable|date_format:H:i', // 休憩終了時間
        'remarks' => 'nullable|string', // 備考
    ]);

    // 勤怠情報を取得
    $attendance = Attendance::findOrFail($request->attendance_id);

    // BreakTime を新規作成
    $breakTime = new BreakTime();
    $breakTime->attendance_id = $attendance->id; // 勤怠IDを設定
    $breakTime->start = \Carbon\Carbon::createFromFormat('H:i', $request->start)->setDate(now()->year, now()->month, now()->day); // 現在の日付を設定
    $breakTime->end = $request->end ? \Carbon\Carbon::createFromFormat('H:i', $request->end)->setDate(now()->year, now()->month, now()->day) : null; // 終了時間を設定
    $breakTime->remarks = $request->remarks; // 備考を設定
    $breakTime->save();

    return redirect()->back()->with('message', '休憩時間の変更申請が送信されました。');
}

public function breakTimeRequestList()
{
    $user = auth()->user(); // 現在のユーザーを取得

    // 承認待ちの休憩時間申請を取得
    $pendingBreakRequests = BreakTime::whereHas('attendance', function ($query) use ($user) {
        $query->where('user_id', $user->id);
    })->where('status', 'pending')->get();

    return view('attendance.break_time_request_list', compact('pendingBreakRequests'));
}

public function showAttendanceDetail($id)
{
    $attendance = Attendance::findOrFail($id);
    $breakTimes = BreakTime::where('attendance_id', $attendance->id)->get(); // 休憩時間を取得

    return view('attendance.detail', compact('attendance', 'breakTimes'));
}


}
