@extends('layouts.app')

@section('title', '一般ユーザー勤怠一覧')

@section('css')
<link rel="stylesheet" href="{{ asset('css/my-records.css') }}">
@endsection

@section('content')

@include('components.header')

<div class="app">
    <div class="change-month-button//group">
        <!-- 先月・当月・翌月切り替え表示。遷移した際に、その月の一覧が表示される -->
        <button class="last-month">前月</button><!--表示されてる月の前の月を表示させるボタン「前月」の前に左向き矢印マーク付ける-->
        <button type="button"> 2025/09</button><!--表示されてる月（2025/09等）を表示し、月の前にカレンダーマーク付ける-->
        <button class="next-month">前月</button><!--表示されてる月の翌月を表示させるボタン「翌月」の後ろに右向き矢印マーク付ける-->
    </div>
    <table class="record__list">
        <tr class="table__header">
            <th class="date">日付</th>
            <th>出勤</th>
            <th>退勤</th>
            <th>休憩</th>
            <th>合計</th>
            <th class="detail">
                詳細
            </th>
        </tr>

        <!-- @foreach(その月の勤怠データをループ表示させる) -->
        <tr class="table__row">
            <td>09/01(月)</td> //出勤日
            <td>09:00</td> //出勤時間
            <td>18:00</td> //退勤時間
            <td>1:00</td> //その日の休憩取得時間
            <td>08:00</td> //その勤務時間
            <td class="detail__link"> //詳細画面へ遷移
                <a href="">詳細</a>
            </td>
        </tr>
        <!-- @endforeach -->

    </table>
</div>
@endsection