@extends('layouts.app')

@section('title', '申請一覧')

@section('css')
<link rel="stylesheet" href="{{ asset('css/request-list.css') }}">
@endsection

@section('content')

@if(Auth::guard('admin')->check())
@include('components.admin-header')
@else
@include('components.header')
@endif

<div class="app">
    <div class="records__list">
        <h1 class="page-title">申請一覧</h1>

        <div class="status__lists">
            @if(Auth::guard('admin')->check())
            <a href="{{ route('admin.correction.list', ['tab' => 'pending']) }}" class="pending {{ $tab === 'pending' ? 'active' : '' }}">承認待ち</a>
            <a href="{{ route('admin.correction.list', ['tab' => 'approved']) }}" class="approved {{ $tab === 'approved' ? 'active' : '' }}">承認済み</a>
            @else
            <a href="{{ route('user.correction.list', ['tab' => 'pending']) }}" class="pending {{ $tab === 'pending' ? 'active' : '' }}">承認待ち</a>
            <a href="{{ route('user.correction.list', ['tab' => 'approved']) }}" class="approved {{ $tab === 'approved' ? 'active' : '' }}">承認済み</a>
            @endif
        </div>

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
                @forelse($corrections as $correction)
                <tr class="table__row">
                    <td class="status__td">{{ $correction->status_label}}</td>
                    <td>{{ $correction->user->name }}</td>
                    <td>{{ $correction->attendance->work_date->format('Y/m/d') }}</td>
                    <td>{{ $correction->reason }}</td>
                    <td>{{ $correction->created_at->format('Y/m/d') }}</td>
                    <td class="detail__link">
                        @if(Auth::guard('admin')->check())
                        <a href="{{ route('admin.detail.record', $correction->attendance->id) }}">詳細</a>
                        @else
                        <a href="{{ route('user.detail.record', $correction->attendance->id) }}">詳細</a>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="no-data">申請データはありません</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection