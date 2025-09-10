@extends('layouts.app')

@section('title', '一般ユーザーログイン')

@section('css')
<link rel="stylesheet" href="{{ asset('/css/authentication.css')  }}">
@endsection

@section('content')

@include('components.header')
<form action="/login" class="authenticate center" method="post">
    @csrf
    <h1 class="page__title">ログイン</h1>
    <label for="mail" class="label__name">メールアドレス</label>
    <input name="email" id="mail" type="text" class="input" value="{{ old('email') }}">
    <div class="form__error">
        @error('email')
        {{ $message }}
        @enderror
    </div>
    <label for="password" class="label__name">パスワード</label>
    <input name="password" id="password" type="password" class="input">
    <div class="form__error">
        @error('password')
        {{ $message }}
        @enderror
    </div>
    <button class="btn btn--big">ログインする</button>
    <a href="/register" class="link">会員登録はこちら</a>
</form>


@endsection