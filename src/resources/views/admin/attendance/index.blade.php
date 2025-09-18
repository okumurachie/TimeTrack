@extends('layouts.app')

@section('title', '勤怠一覧（管理者）')

@section('css')
<link rel="stylesheet" href="{{ asset('css/index.css') }}">
@endsection


@section('content')

@include('components.admin-header')
<div class="app">
    <div class="attendances__list">

        <h1 class="page-title">{{$today}}の勤怠</h1>

        <div class="date-navigation">
            <a href="{{route('admin.attendances.index', ['date' => $workDate->copy()->subDay()->format('Y-m-d')])}}" , class="date-button last-day">前日</a>
            <span class="work-date">{{ $workDate->format('Y/m/d') }}</span>
            <a href="{{route('admin.attendances.index', ['date' => $workDate->copy()->addDay()->format('Y-m-d')])}}" , class="date-button next-day">翌日</a>
        </div>

        <table class="attendances__list__table">
            <thead>
                <tr class="table__row">
                    <th class="name">名前</th>
                    <th>出勤</th>
                    <th>退勤</th>
                    <th>休憩</th>
                    <th>合計</th>
                    <th class="detail">詳細</th>
                </tr>
            </thead>

            <tbody>
                @if(!$hasAttendance)
                <tr>
                    <td colspan="6" class="no-data">この日の勤怠データはありません</td>
                </tr>
                @else
                @foreach($users as $user)
                @php
                $attendance = $attendances[$user->id] ?? null;
                @endphp
                <tr class="table__row">
                    <td>{{ $user->name }}</td>
                    <td>{{ $attendance?->clock_in?->format('H:i') ?? '' }}</td>
                    <td>{{ $attendance?->clock_out?->format('H:i') ?? '' }}</td>
                    <td>{{ $attendance?->total_break ? gmdate('H:i', $attendance->total_break * 60) : '' }}</td>
                    <td>{{ $attendance?->total_work ? gmdate('H:i', $attendance->total_work * 60) : '' }}</td>
                    <td class="detail__link">
                        @if($attendance)
                        <a href="{{ route('admin.detail.record', $attendance->id) }}">詳細</a>
                        @else
                        <span>詳細</span>
                        @endif
                    </td>
                </tr>
                @endforeach
                @endif
            </tbody>
        </table>
    </div>
</div>

@endsection