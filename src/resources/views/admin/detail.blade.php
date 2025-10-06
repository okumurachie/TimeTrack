@extends('layouts.app')

@section('title', '管理者用勤怠詳細')

@section('css')
<link rel="stylesheet" href="{{ asset('css/detail.css') }}">
@endsection

@section('content')

@include('components.admin-header')

<div class="app">
    <div class="correct__form__content">
        <h1 class="page-title">勤怠詳細</h1>

        <form action="{{route('admin.attendance.update', $attendance->id)}}" class="correct__form" method="post">
            @csrf
            @method('patch')
            <table class="form__table">
                <tr class="table__row">
                    <td>
                        <label class="input__label">名前</label>
                        <input type="hidden" name="user_id" value="{{$user->id}}">
                        <div class="user-name">
                            <span>{{$user->name}}</span>
                        </div>
                    </td>
                </tr>
                <tr class="table__row">
                    <td>
                        <label class="input__label">日付</label>
                        <input type="hidden" name="attendance_id" value="{{$attendance->id}}">
                        <div class="work_date">
                            <span class="work_date-y">{{ $workDate->format('Y年') }}</span>
                            <span class="work_date-m-d">{{ $workDate->format('n月j日') }}</span>
                        </div>
                    </td>
                </tr>
                <tr class="table__row">
                    <td>
                        <label class="input__label">出勤・退勤</label>
                        <div class="input__space">
                            <div class="input__group">
                                <input type="text" name="clock_in" class="clock_in" value="{{ old('clock_in', optional($attendance?->clock_in)->format('H:i')) }}">
                                <span class="tilde-mark">〜</span>
                                <input type="text" name="clock_out" class="clock_out" value="{{ old('clock_out', optional($attendance?->clock_out)->format('H:i')) }}">
                            </div>
                            <div class="form__error">
                                @error('clock_in')<div>{{ $message }}</div>@enderror
                                @error('clock_out')<div>{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </td>
                </tr>
                @foreach($breakTimes as $i => $breakTime)
                @php
                $startKey = "breaks.$i.start";
                $endKey = "breaks.$i.end";
                $startValue = old($startKey, optional($breakTime->break_start)->format('H:i'));
                $endValue = old($endKey, optional($breakTime->break_end)->format('H:i'));
                @endphp
                <tr class="table__row">
                    <td>
                        <label class="input__label" for="breaks-{{ $i }}-start">
                            {{ $i === 0 ? '休憩' : '休憩' . ($i + 1) }}
                        </label>
                        <div class="input__space">
                            <div class="input__group">
                                <input id="breaks-{{ $i }}-start" type="text" name="breaks[{{ $i }}][start]" class="clock_in" value="{{$startValue}}">
                                <span class="tilde-mark" aria-hidden="true">〜</span>
                                <input id="breaks-{{ $i }}-end" type="text" name="breaks[{{ $i }}][end]" class="clock_out" value="{{$endValue}}">
                            </div>
                            <div class="form__error">
                                @foreach([$startKey,$endKey] as $key)
                                @error($key)
                                <div>{{ $message }}</div>
                                @enderror
                                @endforeach
                            </div>
                        </div>
                    </td>
                </tr>
                @endforeach
                <tr class="table__row">
                    <td>
                        <label class="input__label">休憩{{$breakTimes->count() + 1 }}</label>
                        <div class="input__space">
                            <div class="input__group">
                                <input type="text" class="clock_in" name="breaks[{{ $breakTimes->count() }}][start]" value="{{ old("breaks." . $breakTimes->count() . ".start") }}">
                                <span class="tilde-mark" aria-hidden="true">〜</span>
                                <input type="text" class="clock_out" name="breaks[{{ $breakTimes->count() }}][end]" value="{{ old("breaks." . $breakTimes->count() . ".end") }}">
                            </div>
                            <div class="form__error">
                                @foreach(['start', 'end'] as $type)
                                @php
                                $key = "breaks." . $breakTimes->count() . ".$type";
                                @endphp
                                @error($key)
                                <div>{{ $message }} </div>
                                @enderror
                                @endforeach
                            </div>
                        </div>
                    </td>
                </tr>

                <tr class="table__row">
                    <td>
                        <label class="input__label">備考</label>
                        <div class="textarea__space">
                            <textarea name="reason">{{old('reason', $latestCorrection->reason)}}</textarea>
                            <div class="form__error">
                                @error('reason')
                                {{ $message }}
                                @enderror
                            </div>
                        </div>
                    </td>
                </tr>
            </table>
            <div class="form__button">
                @if($latestCorrection && $latestCorrection->status === 'pending')
                <p class="pending">*承認待ちのため修正はできません。</p>
                @else
                <button class="form__button__submit">修正</button>
                @endif
            </div>
        </form>
    </div>
</div>
@endsection