<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attendance;

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
        $attendance = Attendance::findOrFail($id); // IDで勤怠レコードを取得
        return view('admin.attendance_show', compact('attendance'));
    }

    // 勤怠情報を更新
    public function update(Request $request, $id)
    {
        $request->validate([
            'check_in' => 'nullable|date_format:H:i',
            'check_out' => 'nullable|date_format:H:i',
            'break_start' => 'nullable|date_format:H:i',
            'break_end' => 'nullable|date_format:H:i',
            'remarks' => 'nullable|string|max:255',
        ]);

        $attendance = Attendance::findOrFail($id);

        // データを更新
        $attendance->check_in = $request->input('check_in');
        $attendance->check_out = $request->input('check_out');
        $attendance->break_start = $request->input('break_start');
        $attendance->break_end = $request->input('break_end');
        $attendance->remarks = $request->input('remarks');

        $attendance->save(); // 保存

        return redirect()->route('admin.attendance.show', $id)->with('success', '勤怠情報が更新されました。');
    }
}
