@extends('layouts.adminheader')

@section('content')
<div class="container">
    <h1>申請一覧（管理者）</h1>

    <!-- タブ -->
    <ul class="nav nav-tabs" id="myTab" role="tablist">
        <li class="nav-item">
            <a class="nav-link active" id="pending-tab" data-toggle="tab" href="#pending" role="tab" aria-controls="pending" aria-selected="true">承認待ち</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="approved-tab" data-toggle="tab" href="#approved" role="tab" aria-controls="approved" aria-selected="false">承認済み</a>
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
                            <th>名前</th>
                            <th>対象日時</th>
                            <th>申請理由</th>
                            <th>申請日時</th>
                            <th>詳細</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pendingRequests as $request)
                            <tr>
                                <td>承認待ち</td>
                                <td>{{ $request->user->name ?? '不明' }}</td>
                                <td>{{ optional($request->attendance)->date->format('Y年/m月/d日') ?? '不明' }}</td>
                                <td>{{ $request->remarks }}</td>
                                <td>{{ $request->created_at->format('Y年/m月/d日') }}</td>
                                <td>
                                    <a href="{{ route('admin.attendance.approve', $request->id) }}" class="btn btn-info">詳細</a>
                                </td>
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
                            <th>名前</th>
                            <th>対象日時</th>
                            <th>申請理由</th>
                            <th>申請日時</th>
                            <th>詳細</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($approvedRequests as $request)
                            <tr>
                                <td>承認済み</td>
                                <td>{{ $request->user->name ?? '不明' }}</td>
                                <td>{{ optional($request->attendance)->date->format('Y年/m月/d日') ?? '不明' }}</td>
                                <td>{{ $request->remarks }}</td>
                                <td>{{ $request->created_at->format('Y年/m月/d日') }}</td>
                                <td>
                                    <a href="{{ route('admin.attendance.approve', $request->id) }}" class="btn btn-info">詳細</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
</div>
@endsection
