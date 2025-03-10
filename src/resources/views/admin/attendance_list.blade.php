@extends('layouts.adminheader')
@section('content')
<div class="attendance-list">
    <h1>勤怠一覧（管理者）</h1>

    <div class="date-selector">
        <button onclick="changeDay(-1)">← 前日</button>
        <span id="current-date">{{ $currentYear }}年 {{ $currentMonth }}月 {{ $currentDay }}日</span>
        <button onclick="changeDay(1)">翌日 →</button>
        <input type="date" id="date-picker" value="{{ $currentYear }}-{{ str_pad($currentMonth, 2, '0', STR_PAD_LEFT) }}-{{ str_pad($currentDay, 2, '0', STR_PAD_LEFT) }}" onchange="selectDate()">
    </div>

    <table>
        <thead>
            <tr>
                <th>名前</th>
                <th>日付</th>
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
                    <td>{{ $record->user->name }}</td>
                    <td>{{ \Carbon\Carbon::parse($record->date)->format('Y-m-d') }}</td>
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
                    <td colspan="7">勤怠情報がありません。</td>
                </tr>
            @endif
        </tbody>
    </table>
</div>

<script>
    function changeDay(direction) {
        const currentDate = document.getElementById('date-picker').value;
        const date = new Date(currentDate);
        date.setDate(date.getDate() + direction);
        window.location.href = '/admin/attendance/list?year=' + date.getFullYear() + '&month=' + (date.getMonth() + 1) + '&day=' + date.getDate();
    }

    function selectDate() {
        const selectedDate = document.getElementById('date-picker').value;
        window.location.href = '/admin/attendance/list?year=' + selectedDate.split('-')[0] + '&month=' + selectedDate.split('-')[1] + '&day=' + selectedDate.split('-')[2];
    }
</script>
@endsection
