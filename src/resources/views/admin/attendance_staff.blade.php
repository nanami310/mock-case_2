@extends('layouts.adminheader')
@section('content')
<div class="attendance-list">
    @foreach($attendanceRecords as $attendance)
    <h1>{{ $attendance->user->name }}さんの勤怠一覧</h1>
    @endforeach
    <div class="month-selector">
        <button onclick="changeMonth(-1)">← 前月</button>
        <span id="current-month">{{ sprintf('%04d', $currentYear) }}/{{ sprintf('%02d', $currentMonth) }}</span>
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
            @php
                // 指定した月の日数を取得
                $daysInMonth = \Carbon\Carbon::createFromDate($currentYear, $currentMonth)->daysInMonth;
                // 勤怠データを日付でマッピング
                $attendanceMap = [];
                foreach ($attendanceRecords as $record) {
                    $formattedDate = \Carbon\Carbon::parse($record->date)->format('Y-m-d');
                    $attendanceMap[$formattedDate] = $record;
                }
            @endphp
            @for ($day = 1; $day <= $daysInMonth; $day++)
                @php
                    // 日付をY-m-d形式で生成
                    $date = sprintf('%04d-%02d-%02d', $currentYear, $currentMonth, $day);
                    $record = $attendanceMap[$date] ?? null; // 該当するレコードを取得
                    $dayOfWeek = ['日', '月', '火', '水', '木', '金', '土'][\Carbon\Carbon::parse($date)->format('w')];
                @endphp
                <tr>
                    <td>{{ \Carbon\Carbon::parse($date)->format('n/j') }} ({{ $dayOfWeek }})</td>
                    <td>{{ $record && $record->check_in ? \Carbon\Carbon::parse($record->check_in)->format('H:i') : '' }}</td>
                    <td>{{ $record && $record->check_out ? \Carbon\Carbon::parse($record->check_out)->format('H:i') : '' }}</td>
                    <td>
                        @php
                            $totalBreakTime = $record ? $record->breaks->sum(function($break) {
                                return $break->end ? \Carbon\Carbon::parse($break->end)->diffInMinutes(\Carbon\Carbon::parse($break->start)) : 0;
                            }) : 0;
                            $breakHours = floor($totalBreakTime / 60);
                            $breakMinutes = $totalBreakTime % 60;
                        @endphp
                        {{ $record ? sprintf('%02d:%02d', $breakHours, $breakMinutes) : '' }}
                    </td>
                    <td>
                        @php
                            if ($record && $record->check_in && $record->check_out) {
                                $checkIn = \Carbon\Carbon::parse($record->check_in);
                                $checkOut = \Carbon\Carbon::parse($record->check_out);
                                $totalHours = $checkOut->diffInMinutes($checkIn) - $totalBreakTime;
                                $hours = floor($totalHours / 60);
                                $minutes = $totalHours % 60;
                            } else {
                                $hours = 0;
                                $minutes = 0;
                            }
                        @endphp
                        {{ $record ? sprintf('%02d:%02d', $hours, $minutes) : '' }}
                    </td>
                    <td>
                        @if ($record)
                            <a href="{{ route('admin.attendance.show', ['id' => $record->user_id, 'date' => $record->date]) }}">詳細</a>
                        @else
                            <a href="{{ route('admin.attendance.show', ['id' => $id, 'date' => $date]) }}">詳細</a> <!-- 日付を渡す -->
                        @endif
                    </td>
                </tr>
            @endfor
        </tbody>
    </table>

    <form method="POST" action="{{ route('admin.attendance.export', $id) }}">
        @csrf
        <input type="hidden" name="year" value="{{ $currentYear }}">
        <input type="hidden" name="month" value="{{ $currentMonth }}">
        <button type="submit">CSV出力</button>
    </form>
</div>

<script>
    const staffId = {{ $id }};

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
