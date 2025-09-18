@extends('layouts.guest')

@section('title', 'メール認証')

@section('css')
<link rel="stylesheet" href="{{ asset('css/auth/verify-email.css') }}">
@endsection


@section('content')

@include('components.header')

<div class="verify-email__content">
    <div class="message">
        <p>登録していただいたメールアドレスに認証メールを送付しました。</p>
        <p>メール認証を完了してください</p>
    </div>
    <div class="sent-message">
        <a href="http://localhost:8025" class="verify-email__link">
            <button class="verify-email__button" type="submit">
                認証はこちらから
            </button>
        </a>
    </div>
    <form method="POST" action="{{ route('verification.send') }}">
        @csrf
        <div>
            <button class="resend" type="submit">
                認証メールを再送する
            </button>
        </div>
    </form>
</div>
@endsection