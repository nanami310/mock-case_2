@extends('layouts.adminheader')
@section('content')
<div class="attendance-list">
    <h1>{{ $currentYear }}年 {{ $currentMonth }}月 {{ $currentDay }}日の勤怠</h1>

    <div class="date-selector">
        <button onclick="changeDay(-1)">← 前日</button>
        <span id="current-date">{{ sprintf('%04d/%02d/%02d', $currentYear, $currentMonth, $currentDay) }}</span>
        <button onclick="changeDay(1)">翌日 →</button>
        <input type="date" id="date-picker" value="{{ $currentYear }}-{{ str_pad($currentMonth, 2, '0', STR_PAD_LEFT) }}-{{ str_pad($currentDay, 2, '0', STR_PAD_LEFT) }}" onchange="selectDate()">
    </div>

    <table>
        <thead>
            <tr>
                <th>名前</th>
                <th>出勤</th>
                <th>退勤</th>
                <th>休憩</th>
                <th>合計</th>
                <th>詳細</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($attendanceRecords as $record)
            <tr>
                <td>{{ $record->user->name }}</td>
                <td>{{ $record->check_in ? \Carbon\Carbon::parse($record->check_in)->format('H:i') : '' }}</td>
                <td>{{ $record->check_out ? \Carbon\Carbon::parse($record->check_out)->format('H:i') : '' }}</td>
                <td>
                    @php
                        $totalBreakTime = $record->breaks->sum(function($break) {
                            return $break->end ? \Carbon\Carbon::parse($break->end)->diffInMinutes(\Carbon\Carbon::parse($break->start)) : 0;
                        });
                        $breakHours = floor($totalBreakTime / 60);
                        $breakMinutes = $totalBreakTime % 60;
                    @endphp
                    {{ sprintf('%02d:%02d', $breakHours, $breakMinutes) }} 
                </td>
                <td>
                    @php
                        if ($record->check_in && $record->check_out) {
                            $checkIn = \Carbon\Carbon::parse($record->check_in);
                            $checkOut = \Carbon\Carbon::parse($record->check_out);
                            $totalHours = $checkOut->diffInMinutes($checkIn) - $totalBreakTime;
                            $hours = floor($totalHours / 60);
                            $minutes = $totalHours % 60;
                            echo sprintf('%02d:%02d', $hours, $minutes);
                        } else {
                            echo '';
                        }
                    @endphp
                </td>
                <td>
                    <a href="{{ route('admin.attendance.show', $record->id) }}">詳細</a>
                </td>
            </tr>
            @empty
                <tr>
                    <td colspan="6">勤怠情報がありません。</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<script>
    function changeDay(direction) {
        const currentDate = document.getElementById('date-picker').value;
        const date = new Date(currentDate);
        date.setDate(date.getDate() + direction);

        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');

        window.location.href = '/admin/attendance/list?year=' + year + '&month=' + month + '&day=' + day;
    }

    function selectDate() {
        const selectedDate = document.getElementById('date-picker').value;
        const [year, month, day] = selectedDate.split('-');
        window.location.href = '/admin/attendance/list?year=' + year + '&month=' + month + '&day=' + day;
    }
</script>
@endsection
