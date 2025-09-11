@extends('layouts.app')

@section('title', '勤怠登録')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance.css') }}">
@endsection


@section('content')

@include('components.header')

<div class="app">
    <p class="work-status__label">勤務外</p>
    <p class="todays-date">今日の日付と曜日</p>
    <p class="current-time">現在時刻</p>
    <button class="stamping">出勤</button>
</div>
@endsection