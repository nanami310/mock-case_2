@extends('layouts.adminheader')
@section('content')
<div>
    <h1>管理者ログイン</h1>


    <form action="{{ route('admin.login.submit') }}" method="POST">
        @csrf
        <div>
            <label for="email">メールアドレス</label>
            <input type="email" name="email" id="email">
            @error('email')
    <span class="error">{{ $message }}</span>
@enderror
        </div>
        <div>
            <label for="password">パスワード</label>
            <input type="password" name="password" id="password">
            @error('password')
    <span class="error">{{ $message }}</span>
@enderror
        </div>
        <button type="submit">管理者ログインする</button>
    </form>
</div>
@endsection
