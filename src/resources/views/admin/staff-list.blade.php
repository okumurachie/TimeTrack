@extends('layouts.app')

@section('title', 'スタッフ一覧')

@section('css')
<link rel="stylesheet" href="{{ asset('css/staff.css') }}">
@endsection

@section('content')

@include('components.admin-header')

<div class="app">
    <div class="staff__list">

        <h1 class="page-title">スタッフ一覧</h1>

        <table class="staff__list__table">
            <thead>
                <tr class="table__row">
                    <th class="name">名前</th>
                    <th>メールアドレス</th>
                    <th class="detail">月次勤怠</th>
                </tr>
            </thead>

            <tbody>
                @if($users->isEmpty())
                <tr>
                    <td colspan="6" class="no-data">スタッフのデータはありません</td>
                </tr>
                @else
                @foreach($users as $user)
                @php
                $attendance = $user->attendances
                @endphp
                <tr class="table__row">
                    <td>{{$user->name}}</td>
                    <td>{{$user->email}}</td>
                    <td class="detail__link">
                        @if($attendance->isNotEmpty())
                        <a href="{{ route('staff-record.list', $user->id) }}">詳細</a>
                        @else
                        <span>勤怠記録はありません</span>
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