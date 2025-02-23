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
        @if ($attendance->status === 'off')
            <h3>勤務外</h3>
            <p>{{ $date }}</p>
            <p>{{ $time }}</p>
            <form action="{{ url('/attendance/check-in') }}" method="POST">
                @csrf
                <button type="submit">出勤</button>
            </form>
        @elseif ($attendance->status === 'working')
            <h3>出勤中</h3>
            <p>{{ $date }}</p>
            <p>{{ $time }}</p>
            <form action="{{ url('/attendance/check-out') }}" method="POST">
                @csrf
                <button type="submit">退勤</button>
            </form>
            <form action="{{ url('/attendance/take-break') }}" method="POST">
                @csrf
                <button type="submit">休憩入</button>
            </form>
        @elseif ($attendance->status === 'on_break')
            <h3>休憩中</h3>
            <p>{{ $date }}</p>
            <p>{{ $time }}</p>
            <form action="{{ url('/attendance/return-from-break') }}" method="POST">
                @csrf
                <button type="submit">休憩戻</button>
            </form>
        @elseif ($attendance->status === 'checked_out')
            <h3>退勤済</h3>
            <p>{{ $date }}</p>
            <p>{{ $time }}</p>
            <p>お連れ様でした。</p>
        @endif
    @else
        <h3>勤怠情報が見つかりません。</h3>
        <form action="{{ url('/attendance/check-in') }}" method="POST">
            @csrf
            <button type="submit">出勤</button>
        </form>
    @endif

    @if (session('message'))
        <p>{{ session('message') }}</p>
    @endif
</div>
@endsection
