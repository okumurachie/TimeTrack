@extends('layouts.app')

@section('title', '管理者ログイン')


@section('content')

@include('components.admin-header')
<form action="/admin/login" class="authenticate center" method="post">
    @csrf
    <h1 class="page__title">管理者ログイン</h1>
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
    <button class="btn btn--big">管理者ログインする</button>
</form>


@endsection