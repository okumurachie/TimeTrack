@extends('layouts.guest')

@section('title', '会員登録')

@section('css')
<link rel="stylesheet" href="{{ asset('/css/auth/authentication.css')  }}">
@endsection

@section('content')

@include('components.header')
<form action="/register" class="authenticate center" method="post">
    @csrf
    <h1 class="page__title">会員登録</h1>
    <label for="name" class="label__name">名前</label>
    <input name="name" id="name" type="text" class="input" value="{{ old('name' )}}">
    <div class="form__error">
        @error('name')
        {{ $message }}
        @enderror
    </div>
    <label for="mail" class="label__name">メールアドレス</label>
    <input name="email" id="mail" type="text" class="input" value="{{ old('email' )}}">
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
    <label for="password_confirm" class="label__name">確認用パスワード</label>
    <input name="password_confirmation" id="password_confirm" type="password" class="input">
    <button class="btn btn--big">登録する</button>
    <a href="/login" class="link">ログインはこちら</a>
</form>
@endsection