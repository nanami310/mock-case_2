@extends('layouts.header')
@section('content')
<div class="attendance-list">
    <h1>勤怠一覧</h1>

    <div class="month-selector">
        <button onclick="changeMonth(-1)">← 前月</button>
        <span id="current-month">{{ sprintf('%04d', $currentYear) }}年 {{ sprintf('%02d', $currentMonth) }}月</span>
        <button onclick="changeMonth(1)">翌月 →</button>
        <input type="month" id="month-picker" value="{{ $currentYear }}-{{ str_pad($currentMonth, 2, '0', STR_PAD_LEFT) }}" onchange="selectMonth()">
    </div>

    @if ($attendanceRecords->isNotEmpty())
        <table>
            <thead>
                <tr>
                    <th>日付</th>
                    <th>出勤</th>
                    <th>退勤</th>
                    <th>休憩</th>
                    <th>合計</th>
                    <th>詳細</th>
                </tr>
            </thead>
            <tbody>
                @php
                    // 勤怠データを日付でマッピング
                    $attendanceMap = [];
                    foreach ($attendanceRecords as $record) {
                        $formattedDate = \Carbon\Carbon::parse($record->date)->format('Y-m-d');
                        $attendanceMap[$formattedDate] = $record;
                    }
                    // 指定した月の日数を取得
                    $daysInMonth = \Carbon\Carbon::createFromDate($currentYear, $currentMonth)->daysInMonth;
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
                                <a href="{{ route('attendance.show', $record->id) }}">詳細</a>
                            @else
                                <a href="{{ route('attendance.show', ['id' => $attendanceRecords->first()->user_id, 'date' => $date]) }}">詳細</a>
                            @endif
                        </td>
                    </tr>
                @endfor
            </tbody>
        </table>
    @else
        <p>勤怠情報がありません。</p>
    @endif
</div>

<script>
    function changeMonth(direction) {
        const currentMonth = document.getElementById('month-picker').value;
        const date = new Date(currentMonth);
        date.setMonth(date.getMonth() + direction);
        window.location.href = '/attendance/list?year=' + date.getFullYear() + '&month=' + (date.getMonth() + 1);
    }

    function selectMonth() {
        const selectedMonth = document.getElementById('month-picker').value;
        window.location.href = '/attendance/list?year=' + selectedMonth.split('-')[0] + '&month=' + selectedMonth.split('-')[1];
    }
</script>
@endsection
