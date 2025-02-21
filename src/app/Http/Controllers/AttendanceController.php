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
}
