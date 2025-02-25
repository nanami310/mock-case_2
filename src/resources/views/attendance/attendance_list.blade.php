@extends('layouts.header')
@section('content')
<div class="attendance-list">
    <h1>勤怠一覧</h1>

    <div class="month-selector">
        <button onclick="changeMonth(-1)">← 前月</button>
        <span id="current-month">{{ $currentYear }}年 {{ $currentMonth }}月</span>
        <button onclick="changeMonth(1)">翌月 →</button>
        <input type="month" id="month-picker" value="{{ $currentYear }}-{{ str_pad($currentMonth, 2, '0', STR_PAD_LEFT) }}" onchange="selectMonth()">
    </div>

    <table>
        <thead>
            <tr>
                <th>日付（月、日、曜日）</th>
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
                    <td>{{ \Carbon\Carbon::parse($record->date)->format('Y-m-d') }}</td> <!-- Carbonに変換 -->
                    <td>{{ $record->check_in ? \Carbon\Carbon::parse($record->check_in)->format('H:i') : '' }}</td>
                    <td>{{ $record->check_out ? \Carbon\Carbon::parse($record->check_out)->format('H:i') : '' }}</td>
                    <td>{{ $record->break_time ?? '' }}</td>
                    <td>{{ $record->total_hours ?? '' }}</td>
                    <td>
                        <a href="{{ url('/attendance/' . $record->id) }}">詳細</a>
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
