<header class="header">
    <div class="header__logo">
        <img src="{{asset('images/logo.svg')}}" alt="ロゴ" class="header__logo-image">
    </div>
    <nav class="header__nav">
        <ul>
            @if(Auth::check() && Auth::user()->hasVerifiedEmail())
            <li><a href="{{ route('my-record.list') }}">今月の出勤一覧</a></li>
            <li><a href="/stamp_correction_request/list">申請一覧</a></li>
            <li>
                <form action="/logout" class="logout" method="post">
                    @csrf
                    <button class="header__logout">ログアウト</button>
                </form>
            </li>
        </ul>
    </nav>
    @endif
</header>