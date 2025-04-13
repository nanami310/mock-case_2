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
    
    // 勤怠記録を取得
    $attendanceRecords = Attendance::where('user_id', $user->id)
                                   ->whereYear('date', $year)
                                   ->whereMonth('date', $month)
                                   ->get();
    
    // ビューに渡す
    return view('attendance.attendance_list', [
        'attendanceRecords' => $attendanceRecords,
        'currentYear' => $year,
        'currentMonth' => $month,
        'user' => $user,
    ]);
}

public function showAttendanceList()
{
    $records = Attendance::where('user_id', auth()->id())->get();

    return view('attendance.attendance_list', compact('records'));
}



public function show($id)
{\Log::info('Attendance show method called with ID: ' . $id);
    $attendance = Attendance::find($id);

    // 勤怠情報がない場合の処理
    if (!$attendance) {
        // 新しいインスタンスを作成
        $attendance = new Attendance();
        $attendance->user_id = auth()->id();
        $attendance->date = now(); // 現在の日付を設定
        $attendance->check_in = null; // 空にする
        $attendance->check_out = null; // 空にする
        $attendance->breaks = collect(); // 空のコレクションを設定
        $attendance->remarks = ''; // 備考も空に設定
    } else {
        if ($attendance->user_id !== auth()->id()) {
            abort(403);
        }

        // 日付のパース
        $attendance->date = Carbon::parse($attendance->date);
        $attendance->check_in = $attendance->check_in ? Carbon::parse($attendance->check_in) : null;
        $attendance->check_out = $attendance->check_out ? Carbon::parse($attendance->check_out) : null;

        // breaksのマッピング
        $attendance->breaks = $attendance->breaks->map(function($break) {
            $break->start = Carbon::parse($break->start);
            $break->end = Carbon::parse($break->end);
            return $break;
        });
    }

    $breakTimes = $attendance->breaks; // 勤怠情報の休憩時間を取得
    return view('attendance.show', compact('attendance', 'breakTimes'));
}

public function show2($id)
{
    \Log::info('Attendance show2 method called with ID: ' . $id);
    
    // 日付をY-m-d形式で受け取る
    $date = Carbon::parse($id);
    $attendance = Attendance::with(['breaks'])->where('date', $date)->where('user_id', auth()->id())->first();

    if (!$attendance) {
        // 新しい勤怠インスタンスを作成
        $attendance = new Attendance();
        $attendance->user_id = auth()->id();
        $attendance->date = $date; // リクエストされた日付を設定
        $attendance->check_in = null; // 空にする
        $attendance->check_out = null; // 空にする
        $attendance->remarks = ''; // 備考も空に設定
        
        // AttendanceStatusを作成
        $attendanceStatus = new AttendanceStatus();
        $attendanceStatus->status = 'registered'; // デフォルトのステータスを「registered」に設定
        $attendanceStatus->breaks = collect(); // 空のコレクションを設定
    } else {
        // ユーザーの権限を確認
        if ($attendance->user_id !== auth()->id()) {
            abort(403);
        }

        // 日付をCarbonでパース
        $attendance->date = Carbon::parse($attendance->date);
        $attendance->check_in = $attendance->check_in ? Carbon::parse($attendance->check_in) : null;
        $attendance->check_out = $attendance->check_out ? Carbon::parse($attendance->check_out) : null;

        // AttendanceStatusの取得
        $attendanceStatus = AttendanceStatus::where('attendance_id', $attendance->id)
            ->where('user_id', auth()->id())
            ->first();

        if (!$attendanceStatus) {
            // attendanceStatusが見つからない場合のデフォルト値
            $attendanceStatus = new AttendanceStatus();
            $attendanceStatus->check_in = null;
            $attendanceStatus->check_out = null;
            $attendanceStatus->status = 'registered'; // 状態を「registered」に設定
            $attendanceStatus->breaks = collect(); // 空のコレクションを設定
        }
    }

    // 休憩時間を取得
    $breaks = $attendance->breaks;

    // 日付をフォーマットして渡す
    $formattedDate = $attendance->date->format('Y年n月j日');
    $formattedCheckIn = $attendance->check_in ? $attendance->check_in->format('H:i') : '';
    $formattedCheckOut = $attendance->check_out ? $attendance->check_out->format('H:i') : '';

    // 変数をビューに渡す
    return view('attendance.show', compact('attendance', 'attendanceStatus', 'formattedDate', 'formattedCheckIn', 
'formattedCheckOut', 'breaks'));
}


public function update(AttendanceRequest $request, $id)
{
    // バリデーションは AttendanceRequest で行われるため、ここでは省略

    // Attendanceから情報を取得
    $attendance = Attendance::findOrFail($id);
    
    // AttendanceStatusを作成または更新
    $attendanceStatus = AttendanceStatus::updateOrCreate(
        ['attendance_id' => $attendance->id, 'user_id' => auth()->id()],
        [
            'status' => 'approved', // ステータスを承認済みに設定
        ]
    );

    // 勤怠情報の更新
    $attendanceStatus->check_in = now()->format('Y-m-d') . ' ' . $request->check_in . ':00';
    $attendanceStatus->check_out = now()->format('Y-m-d') . ' ' . $request->check_out . ':00';
    $attendanceStatus->remarks = $request->remarks;

    // 休憩時間の更新
    foreach ($attendanceStatus->breaks as $index => $break) {
        $break->start = now()->format('Y-m-d') . ' ' . $request->breaks[$index]['start'];
        $break->end = $request->breaks[$index]['end'] ? now()->format('Y-m-d') . ' ' . $request->breaks[$index]['end'] : null;
        $break->save();
    }

    // 新しい休憩時間の追加
    for ($i = count($attendanceStatus->breaks); $i < count($request->breaks); $i++) {
        $breakTime = new BreakTime();
        $breakTime->attendance_status_id = $attendanceStatus->id; // AttendanceStatusのIDを関連付け
        $breakTime->start = now()->format('Y-m-d') . ' ' . $request->breaks[$i]['start'];
        $breakTime->end = $request->breaks[$i]['end'] ? now()->format('Y-m-d') . ' ' . $request->breaks[$i]['end'] : null;
        $breakTime->save();
    }

    $attendanceStatus->save(); // 最後に保存

    // リダイレクトまたはレスポンス
    return redirect()->route('attendance.show2', ['id' => $attendance->date->format('Y-m-d')])
                     ->with('success', '修正申請が送信されました。');
}

public function requestChange2(AttendanceRequest $request)
{
    // attendance_id を取得
    $attendanceId = $request->attendance_id;

    // Attendance モデルを取得
    $attendance = Attendance::find($attendanceId);
    if (!$attendance) {
        return redirect()->back()->withErrors(['attendance_id' => '指定された勤怠情報が存在しません。']);
    }

    // 勤怠ステータスを作成
    $attendanceStatus = new AttendanceStatus();
    $attendanceStatus->attendance_id = $attendance->id; // attendance_id を設定
    $attendanceStatus->user_id = auth()->id(); // 現在のユーザーIDを設定
    $attendanceStatus->check_in = $request->check_in; // チェックイン時間を設定
    $attendanceStatus->check_out = $request->check_out; // チェックアウト時間を設定
    $attendanceStatus->remarks = $request->remarks; // 備考を設定
    $attendanceStatus->status = 'pending'; // ステータスを設定

    // 休憩時間の処理
    if ($request->breaks) {
        foreach ($request->breaks as $break) {
            if (!empty($break['start']) && !empty($break['end'])) {
                $attendanceStatus->break_start = $break['start']; // 休憩開始時間を保存
                $attendanceStatus->break_end = $break['end']; // 休憩終了時間を保存
            }
        }
    }

    // データベースに保存
    $attendanceStatus->save();

    return redirect()->back()->with('message', '勤怠情報が正常に申請されました。');
}


public function store(AttendanceRequest $request)
{
    // バリデーション済みのデータを取得
    $validated = $request->validated();

    // 日付がリクエストに含まれていない場合、現在の日付を使用
    $date = $request->input('date', \Carbon\Carbon::now()->format('Y-m-d'));

    // 新しい勤怠情報を作成
    $attendance = new Attendance();
    $attendance->user_id = $request->user()->id; // 現在のユーザーIDを設定
    $attendance->date = $date; // 日付を設定
    $attendance->save();

    // AttendanceStatusを作成
    $attendanceStatus = new AttendanceStatus();
    $attendanceStatus->attendance_id = $attendance->id;
    $attendanceStatus->user_id = auth()->id();
    $attendanceStatus->status = 'approved'; // ステータスを承認済みに設定
    $attendanceStatus->check_in = \Carbon\Carbon::parse($date . ' ' . $validated['check_in'] . ':00');
    $attendanceStatus->check_out = \Carbon\Carbon::parse($date . ' ' . $validated['check_out'] . ':00');
    $attendanceStatus->remarks = $validated['remarks'] ?? null; // 備考を設定
    $attendanceStatus->save();

    // 休憩時間を保存
    if (isset($validated['breaks'])) {
        foreach ($validated['breaks'] as $break) {
            $start = !empty($break['start']) ? \Carbon\Carbon::parse($date . ' ' . $break['start'] . ':00') : null;
            $end = !empty($break['end']) ? \Carbon\Carbon::parse($date . ' ' . $break['end'] . ':00') : null;

            $attendanceStatus->breaks()->create([
                'start' => $start,
                'end' => $end,
            ]);
        }
    }

    // リダイレクトまたはレスポンス
    return redirect()->route('admin.attendance.list')->with('success', '勤怠情報が保存されました。');
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
