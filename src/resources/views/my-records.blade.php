@extends('layouts.app')

@section('title', '一般ユーザー勤怠一覧')

@section('css')
<link rel="stylesheet" href="{{ asset('css/records.css') }}">
@endsection

@section('content')

@include('components.header')

<div class="app">
    <div class="records__list">
        <h1 class="page-title">勤怠一覧</h1>

        <div class="month-navigation">
            <a href="{{route('my-record.list', ['month' => $currentMonth->copy()->subMonth()->format('Y-m')])}}" , class="month-button last-month">前月</a>
            <span class="current-month">{{ $currentMonth->format('Y/m') }}</span>
            <a href="{{route('my-record.list', ['month' => $currentMonth->copy()->addMonth()->format('Y-m')])}}" , class="month-button next-month">翌月</a>
        </div>
        <table class="record__list">
            <thead>
                <tr class="table__header">
                    <th class="date">日付</th>
                    <th>出勤</th>
                    <th>退勤</th>
                    <th>休憩</th>
                    <th>合計</th>
                    <th class="detail">詳細</th>
                </tr>
            </thead>

            <tbody>
                @forelse($attendances as $attendance)
                <tr class="table__row">
                    <td>{{ $attendance->work_date->format('m/d(D)') }}</td>
                    <td>{{ $attendance->clock_in?->format('H:i') ?? '' }}</td>
                    <td>{{ $attendance->clock_out?->format('H:i') ?? '' }}</td>
                    <td>{{ $attendance->total_break ? gmdate('H:i', $attendance->total_break * 60) : '' }}</td>
                    <td>{{ $attendance->total_work ? gmdate('H:i', $attendance->total_work * 60) : '' }}</td>
                    <td class="detail__link">
                        <a href="{{ route('detail.record', $attendance->id) }}">詳細</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="no-data">この月の勤怠データはありません</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection