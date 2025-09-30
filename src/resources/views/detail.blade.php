@extends('layouts.app')

@section('title', '一般ユーザー勤怠詳細')

@section('css')
<link rel="stylesheet" href="{{ asset('css/detail.css') }}">
@endsection

@section('content')

@include('components.header')

<div class="app">
    <div class="correct__form__content">
        <h1 class="page-title">勤怠詳細</h1>

        <form action="{{route('attendance.request', $attendance->id)}}" class="correct__form" method="post">
            @csrf
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

                @php
                $isPending = $latestCorrection && ($latestCorrection->status === 'pending');
                @endphp

                <tr class="table__row">
                    <td>
                        <label class="input__label ">出勤・退勤</label>
                        <div class="input__space">
                            <div class="input__group">
                                @php
                                $clockIn = $latestChanges['clock_in'] ?? optional($attendance->clock_in)->format('H:i');
                                $clockOut = $latestChanges['clock_out'] ?? optional($attendance->clock_out)->format('H:i');
                                @endphp
                                <input type="text" name="clock_in" class="clock_in" value="{{ old('clock_in', $clockIn) }}" @if($isPending) readonly @endif>
                                <span class="tilde-mark">〜</span>
                                <input type="text" name="clock_out" class="clock_out" value="{{ old ('clock_out', $clockOut) }}" @if($isPending) readonly @endif>
                            </div>
                            <div class="form__error">
                                @error('clock_in'){{ $message }}@enderror
                                @error('clock_out')<div>{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </td>
                </tr>

                @foreach($breaks as $i => $break)
                <tr class="table__row">
                    <td>
                        <label class="input__label" for="breaks-{{ $i }}-start">
                            {{ $i === 0 ? '休憩' : '休憩' . ($i + 1) }}
                        </label>
                        <div class="input__space">
                            <div class="input__group">
                                <input id="breaks-{{ $i }}-start" type="text" name="breaks[{{ $i }}][start]" class="clock_in" value="{{ old("breaks.{$i}.start", $break['start'] ?? '') }}" @if($isPending) readonly @endif>
                                <span class="tilde-mark" aria-hidden="true">〜</span>
                                <input id="breaks-{{ $i }}-end" type="text" name="breaks[{{ $i }}][end]" class="clock_out" value="{{ old ("breaks.{$i}.end", $break['end'] ?? '') }}" @if($isPending) readonly @endif>
                            </div>
                            <div class=" form__error">
                                @error("breaks.{$i}.start") <div>{{ $message }}</div> @enderror
                                @error("breaks.{$i}.end") <div>{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </td>
                </tr>
                @endforeach

                @unless($isPending)
                <tr class="table__row">
                    <td>
                        <label class="input__label">休憩{{ count($breaks) + 1 }}</label>
                        <div class="input__space">
                            <div class="input__group">
                                <input type="text" class="clock_in" name="breaks[{{ count($breaks) }}][start]" value="{{ old("breaks." . count($breaks) . ".start") }}">
                                <span class="tilde-mark" aria-hidden="true">〜</span>
                                <input type="text" class="clock_out" name="breaks[{{ count($breaks) }}][end]" value="{{ old("breaks." . count($breaks) . ".end") }}">
                            </div>
                            <div class="form__error">
                                @error("breaks." . count($breaks) . ".start") <div>{{ $message }}</div> @enderror
                                @error("breaks." . count($breaks) . ".end") <div>{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </td>
                </tr>
                @endunless

                <tr class="table__row">
                    <td>
                        <label class="input__label">備考</label>
                        <div class="textarea__space">
                            @php
                            $reasonValue = $latestCorrection->reason ?? '';
                            @endphp
                            <textarea name="reason" @if($isPending) readonly @endif>{{ old('reason', $reasonValue) }}</textarea>
                            <div class="form__error">
                                @error('reason') <div>{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </td>
                </tr>
            </table>

            <div class="form__button">
                @if($isPending)
                <p class="pending">*承認待ちのため修正はできません。</p>
                @else
                <button class="form__button__submit">修正</button>
                @endif
            </div>
        </form>
    </div>
</div>
@endsection