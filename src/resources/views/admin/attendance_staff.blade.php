@extends('layouts.adminheader')
@section('content')
<div class="attendance-list">
    <h1>スタッフ別勤怠一覧（管理者）</h1>

    <div class="month-selector">
    <button onclick="changeMonth(-1)">← 前月</button>
    <span id="current-month">{{ $currentYear }}年 {{ $currentMonth }}月</span>
    <button onclick="changeMonth(1)">翌月 →</button>
    <input type="month" id="month-picker" value="{{ $currentYear }}-{{ str_pad($currentMonth, 2, '0', STR_PAD_LEFT) }}" onchange="selectMonth()">
</div>

    <table>
        <thead>
            <tr>
                <th>日付（M/D 曜日）</th>
                <th>出勤時間</th>
                <th>退勤時間</th>
                <th>休憩時間</th>
                <th>合計</th>
                <th>詳細</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($attendanceRecords as $record)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($record->date)->format('n/j D') }}</td>
                    <td>{{ $record->check_in ? \Carbon\Carbon::parse($record->check_in)->format('H:i') : '' }}</td>
                    <td>{{ $record->check_out ? \Carbon\Carbon::parse($record->check_out)->format('H:i') : '' }}</td>
                    <td>
                        @php
                            $totalBreakTime = $record->breaks->sum(function($break) {
                                return $break->end ? \Carbon\Carbon::parse($break->end)->diffInMinutes(\Carbon\Carbon::parse($break->start)) : 0;
                            });
                        @endphp
                        {{ $totalBreakTime }} 分
                    </td>
                    <td>
                        @php
                            if ($record->check_in && $record->check_out) {
                                $checkIn = \Carbon\Carbon::parse($record->check_in);
                                $checkOut = \Carbon\Carbon::parse($record->check_out);
                                $totalHours = $checkOut->diffInMinutes($checkIn) - $totalBreakTime;
                                echo floor($totalHours / 60) . ' 時間 ' . ($totalHours % 60) . ' 分';
                            } else {
                                echo '';
                            }
                        @endphp
                    </td>
                    <td>
                        <a href="{{ route('admin.attendance.show', $record->id) }}">詳細</a>
                    </td>
                </tr>
            @endforeach
            @if ($attendanceRecords->isEmpty())
                <tr>
                    <td colspan="6">勤怠情報がありません。</td>
                </tr>
            @endif
        </tbody>
    </table>

    <form method="POST" action="{{ route('admin.attendance.export', $id) }}">
    @csrf
    <input type="hidden" name="year" value="{{ $currentYear }}"> <!-- 修正 -->
    <input type="hidden" name="month" value="{{ $currentMonth }}"> <!-- 修正 -->
    <button type="submit">CSV出力</button>
</form>
</div>

<script>
    const staffId = {{ $id }}; // コントローラーから渡されたスタッフIDをJavaScriptに渡す

    function changeMonth(direction) {
        const currentMonth = document.getElementById('month-picker').value;
        const date = new Date(currentMonth);
        date.setMonth(date.getMonth() + direction);
        window.location.href = '/admin/attendance/staff/' + staffId + '?year=' + date.getFullYear() + '&month=' + (date.getMonth() + 1);
    }

    function selectMonth() {
        const selectedMonth = document.getElementById('month-picker').value;
        window.location.href = '/admin/attendance/staff/' + staffId + '?year=' + selectedMonth.split('-')[0] + '&month=' + selectedMonth.split('-')[1];
    }
</script>
@endsection
