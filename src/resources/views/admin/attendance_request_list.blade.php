@extends('layouts.adminheader')

<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>

@section('content')
<div class="container">
    <h1>申請一覧(管理者)
    </h1>

    <!-- タブ -->
    <ul class="nav nav-tabs" id="myTab" role="tablist">
        <li class="nav-item">
            <a class="nav-link active" id="pending-tab" data-toggle="tab" href="#pending" role="tab" aria-controls="pending" aria-selected="true">
承認待ちの申請</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="approved-tab" data-toggle="tab" href="#approved" role="tab" aria-controls="approved" aria-selected="false">承認
済みの申請</a>
        </li>
    </ul>

    <div class="tab-content" id="myTabContent">
        <!-- 承認待ちの申請 -->
        <div class="tab-pane fade show active" id="pending" role="tabpanel" aria-labelledby="pending-tab">
            @if($pendingRequests->isEmpty())
                <p>承認待ちの申請はありません。</p>
            @else
                <table class="table">
                    <thead>
                        <tr>
                            <th>状態</th>
                            <th>ユーザー名</th>
                            <th>チェックイン</th>
                            <th>チェックアウト</th>
                            <th>備考</th>
                            <th>申請日時</th>
                            <th>詳細</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pendingRequests as $request)
                            <tr>
                                <td>{{ $request->status }}</td>
                                <td>{{ $request->user ? $request->user->name : '不明' }}</td>
                                <td>{{ $request->check_in }}</td>
                                <td>{{ $request->check_out }}</td>
                                <td>{{ $request->remarks }}</td>
                                <td>{{ $request->created_at->format('Y年n月j日 H:i') }}</td>
                                <td><a href="{{ route('admin.attendance.show', $request->attendance_id) }}" class="btn btn-info">詳細</a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>

        <!-- 承認済みの申請 -->
        <div class="tab-pane fade" id="approved" role="tabpanel" aria-labelledby="approved-tab">
            @if($approvedRequests->isEmpty())
                <p>承認済みの申請はありません。</p>
            @else
                <table class="table">
                    <thead>
                        <tr>
                            <th>状態</th>
                            <th>ユーザー名</th>
                            <th>チェックイン</th>
                            <th>チェックアウト</th>
                            <th>備考</th>
                            <th>申請日時</th>
                            <th>詳細</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($approvedRequests as $request)
                            <tr>
                                <td>{{ $request->status }}</td>
                                <td>{{ $request->user ? $request->user->name : '不明' }}</td>
                                <td>{{ $request->check_in }}</td>
                                <td>{{ $request->check_out }}</td>
                                <td>{{ $request->remarks }}</td>
                                <td>{{ $request->created_at->format('Y年n月j日 H:i') }}</td>
                                <td><a href="{{ route('admin.attendance.show', $request->attendance_id) }}" class="btn btn-info">詳細</a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
</div>
@endsection