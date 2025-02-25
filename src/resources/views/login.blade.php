@extends('layouts.header')
@section('content')
<div>
    <h1>ログイン</h1>
    <form action="{{ url('/login') }}" method="POST">
        @csrf
        <div>
            <label for="email">メールアドレス</label>
            <input type="email" id="email" name="email" value="{{ old('email') }}">
            @error('email')
                <span class="error">{{ $message }}</span>
            @enderror
        </div>
        <div>
            <label for="password">パスワード</label>
            <input type="password" id="password" name="password">
            @error('password')
                <span class="error">{{ $message }}</span>
            @enderror
        </div>
        <button type="submit">ログイン</button>
    </form>
    <p><a href="{{ url('/register') }}">会員登録はこちら</a></p>
</div>
@endsection
