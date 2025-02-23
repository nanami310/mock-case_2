<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Attendance;

class AttendanceController extends Controller
{
    public function index()
    {
        $attendance = $this->getAttendanceData(auth()->user());

        // 勤怠情報がない場合は新しいオブジェクトを作成
        if (!$attendance) {
            $attendance = new Attendance();
            $attendance->status = 'off'; // デフォルトの状態
        }

        return view('attendance.index', compact('attendance'));
    }

    private function getAttendanceData($user)
    {
        // ユーザーの勤怠情報を取得
        return Attendance::where('user_id', $user->id)
            ->whereDate('created_at', today())
            ->first();
    }

    public function checkIn(Request $request)
    {
        $attendance = $this->getAttendanceData(auth()->user());

        // すでに出勤している場合は処理を行わない
        if ($attendance && $attendance->status === 'working') {
            return redirect()->back(); // メッセージを表示しない
        }

        // 新たに出勤記録を作成
        Attendance::updateOrCreate(
            ['user_id' => auth()->id(), 'date' => today()],
            ['status' => 'working']
        );

        return redirect()->back(); // メッセージを表示しない
    }

    public function checkOut(Request $request)
    {
        $attendance = $this->getAttendanceData(auth()->user());

        if ($attendance && $attendance->status === 'working') {
            $attendance->status = 'checked_out';
            $attendance->save();
            return redirect()->back(); // メッセージを表示しない
        }

        return redirect()->back(); // メッセージを表示しない
    }

    public function takeBreak(Request $request)
    {
        $attendance = $this->getAttendanceData(auth()->user());

        if ($attendance && $attendance->status === 'working') {
            $attendance->status = 'on_break';
            $attendance->save();
            return redirect()->back(); // メッセージを表示しない
        }

        return redirect()->back(); // メッセージを表示しない
    }

    public function returnFromBreak(Request $request)
    {
        $attendance = $this->getAttendanceData(auth()->user());

        if ($attendance && $attendance->status === 'on_break') {
            $attendance->status = 'working';
            $attendance->save();
            return redirect()->back(); // メッセージを表示しない
        }

        return redirect()->back(); // メッセージを表示しない
    }
}
