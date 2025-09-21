<header class="header">
    <div class="header__logo">
        <a href="{{ route('admin.attendances.index') }}">
            <img src="{{asset('images/logo.svg')}}" alt="ロゴ" class="header__logo-image">
        </a>
    </div>
    <nav class="header__nav">
        <ul>
            @if(Auth::guard('admin')->check())
            <li><a href="{{ route('admin.attendances.index') }}">勤怠一覧</a></li>
            <li><a href="{{ route('admin.staff.list') }}">スタッフ一覧</a></li>
            <li><a href="/stamp_correction_request/list">申請一覧</a></li>
            <li>
                <form action="{{route('admin.logout')}}" class="logout" method="post">
                    @csrf
                    <button class="header__logout">ログアウト</button>
                </form>
            </li>
        </ul>
        @endif
    </nav>

</header>