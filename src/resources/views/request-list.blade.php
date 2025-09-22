@extends('layouts.app')

@section('title', '申請一覧')

@section('css')
<link rel="stylesheet" href="{{ asset('css/records.css') }}">
@endsection

@section('content')

@if(Auth::guard('admin')->check())
@include('components.admin-header')
@else
@include('components.header')
@endif

<div class="app">
    <div class="records__list">
        <h1 class="page-title">勤怠一覧</h1>

        <table class="records__list__table">
            <thead>
                <tr class="table__row">
                    <th class="status">状態</th>
                    <th>名前</th>
                    <th>対象日時</th>
                    <th>申請理由</th>
                    <th>申請日時</th>
                    <th class="detail">詳細</th>
                </tr>
            </thead>

            <tbody>
                @if(!$hasCollections)
                <tr>
                    <td colspan="6" class="no-data">申請データはありません</td>
                </tr>
                @else
                @if(Auth::guard('admin')->check())
                @foreach($users as $user)
                <tr class="table__row">
                    <td>{{ $user->corrections->status}}</td>
                    <td>{{ $user->name}}</td>
                    <td>{{ $user->attendance->work_date->format(Y/m/d)}}</td>
                    <td>{{ $user->corrections->reason}}</td>
                    <td>{{ $user->attendances->created_at->format(Y/m/d)}}</td>
                    <td class="detail__link">
                        @if($correction)
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