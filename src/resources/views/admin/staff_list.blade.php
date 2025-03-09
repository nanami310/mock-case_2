@extends('layouts.adminheader')
@section('content')
<div class="staff-list">
    <h1>スタッフ一覧（管理者）</h1>

    <table>
        <thead>
            <tr>
                <th>名前</th>
                <th>メールアドレス</th>
                <th>月次勤怠</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($staffs as $staff)
                <tr>
                    <td>{{ $staff->name }}</td>
                    <td>{{ $staff->email }}</td>
                    <td>
                        <a href="{{ route('admin.attendance.staff', $staff->id) }}">詳細</a>
                    </td>
                </tr>
            @endforeach
            @if ($staffs->isEmpty())
                <tr>
                    <td colspan="3">スタッフ情報がありません。</td>
                </tr>
            @endif
        </tbody>
    </table>
</div>
@endsection
