@extends('layouts.header')

@section('content')
<div>
    @php
        $dayOfWeek = ['日', '月', '火', '水', '木', '金', '土'];
        $currentDay = $dayOfWeek[\Carbon\Carbon::now()->dayOfWeek];
        $date = \Carbon\Carbon::now()->format('Y年n月j日') . " (" . $currentDay . ")";
        $time = \Carbon\Carbon::now()->format('H:i');
    @endphp

    @if ($attendance)
        <h3>
            @switch($attendance->status)
                @case('on_duty')
                    勤務中
                    @break
                @case('on_break')
                    休憩中
                    @break
                @case('off_work')
                    退勤済
                    @break
                @default
                    勤務外
            @endswitch
        </h3>
        <p>{{ $date }}</p>
        <p id="current-time">{{ $time }}</p> <!-- 時刻を表示する要素 -->

        <script>
            // 現在の時刻をリアルタイムで更新する関数
            function updateTime() {
                const now = new Date();
                const hours = String(now.getHours()).padStart(2, '0');
                const minutes = String(now.getMinutes()).padStart(2, '0');
                document.getElementById('current-time').textContent = hours + ':' + minutes;
            }

            // 1分ごとに時刻を更新
            setInterval(updateTime, 60000);
            // ページが読み込まれたときに即座に時刻を更新
            updateTime();
        </script>

        @if ($attendance->status === 'off_duty')
            <form action="{{ url('/attendance/check-in') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-primary">出勤</button>
            </form>
        @elseif ($attendance->status === 'on_duty')
            <form action="{{ url('/attendance/check-out') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-danger">退勤</button>
            </form>
            <form action="{{ url('/attendance/take-break') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-warning">休憩入</button>
            </form>
        @elseif ($attendance->status === 'on_break')
            <form action="{{ url('/attendance/return-from-break') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-success">休憩戻</button>
            </form>
        @endif
    @else
        <h3>勤怠情報が見つかりません。</h3>
        <form action="{{ url('/attendance/check-in') }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-primary">出勤</button>
        </form>
    @endif

    @if (session('message'))
        <p>{{ session('message') }}</p>
    @endif
</div>
@endsection
