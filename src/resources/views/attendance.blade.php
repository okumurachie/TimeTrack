@extends('layouts.app')

@section('title', '勤怠登録')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance.css') }}">
@endsection


@section('content')

@include('components.header')

<div class="app">
    @if (session('message'))
    <div class="alert alert-success">
        {{ session('message') }}
    </div>
    @endif
    @if(session('error'))
    <div class="alert alert-danger">
        {{ session('error') }}
    </div>
    @endif
    <form action="{{route('attendance.stamp')}}" class="stamping" method="post">
        @csrf
        <div class="work-status">
            @php
            $todayAttendance = auth()->user->attendances()
            ->whereDate('work_date', now()->toDateString())
            ->first();
            @endphp
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
            <input type="text" class="todays-date" value="{{ now()->format('Y年m月d日(D)') }}" readonly>
            <input type="text" class="current-time" value="{{ now()->format('H:i') }}" readonly>
        </div>

        <div class="stamping__buttons">
            @if(!$todayAttendance)
            <button type="submit" name="action" class="clock-in btn" value="clock_in">出勤</button>
            @endif

            @if($todayAttendance && !$todayAttendance->clock_out)
            <div class="button__group">
                <button type="submit" name="action" class="clock-out btn" value="clock_out">退勤</button>
                @if(!$todayAttendance->is_on_break)
                <button type="submit" name="action" class="break-start" value="break_start">休憩入</button>
                @else
                <button type="submit" name="action" class="break-end" value="break_end">休憩戻</button>
                @endif
            </div>
            @if($todayAttendance && $todayAttendance->clock_out)
            <p class="good-job">お疲れ様でした</p>
            @endif
        </div>

    </form>
</div>
@endsection