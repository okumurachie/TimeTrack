@extends('layouts.app')

@section('title', '勤怠登録')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance.css') }}">
@endsection

@section('content')

@if($todayAttendance && $todayAttendance->clock_out)
@include('components.header_after_clock_out')
@else
@include('components.header')
@endif

<div class="app">
    @if(session('error'))
    <div class="alert alert-danger">
        {{ session('error') }}
    </div>
    @endif
    <form action="{{route('attendance.stamp')}}" class="stamping" method="post">
        @csrf
        <div class="work-status">
            @if(!$todayAttendance)
            <p class="work-status__label">勤務外</p>
            @elseif($todayAttendance->clock_out)
            <p class="work-status__label">退勤済</p>
            @elseif($todayAttendance->is_on_break)
            <p class="work-status__label">休憩中</p>
            @else
            <p class="work-status__label">出勤中</p>
            @endif
        </div>
        <div class="datetime">
            <input type="text" class="todays-date" value="{{ now()->isoFormat('YYYY年MM月DD日(dd)') }}" readonly>
            <input type="text" class="current-time" value="{{ now()->format('H:i') }}" readonly>
        </div>

        <div class="stamping__buttons">
            @if(!$todayAttendance)
            <button type="submit" name="action" class="clock-in button" value="clock_in">出勤</button>
            @endif

            @if($todayAttendance && !$todayAttendance->clock_out)
            @if(!$todayAttendance->is_on_break)
            <div class="button__group">
                <button type="submit" name="action" class="clock-out button" value="clock_out">退勤</button>
                <button type="submit" name="action" class="break-start" value="break_start">休憩入</button>
            </div>
            @else
            <button type="submit" name="action" class="break-end" value="break_end">休憩戻</button>
            @endif
            @endif

            @if($todayAttendance && $todayAttendance->clock_out)
            <p class="good-job">お疲れ様でした</p>
            @endif
        </div>

    </form>
</div>
@endsection