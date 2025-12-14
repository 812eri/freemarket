<header class="header-main">
    <div class="header-main__left">
        <h1>
            <a href="{{ url('/') }}" class="header-main__logo-link">
                <img src="{{ asset('images/logo.svg') }}" alt="Free-market-Logo" class="header-main__logo-img">
            </a>
        </h1>
    </div>

    <form action="{{ url('/') }}" method="get" class="header-main__search-form">
        <div class="header-main__search-wrapper">
            <input
                type="search"
                name="keyword"
                placeholder="なにをお探しですか？"
                value="{{ request('keyword') }}"
                class="header-main__search-input"
            >
        </div>
    </form>

    <div class="header-main__action-group">
        @auth
            <form method="post" action="{{ route('logout') }}" class="header-main__logout-form">
                @csrf
                <button type="submit" class="header-main__logout-button">
                    ログアウト
                </button>
            </form>
            <a href="{{ route('profile.show') }}" class="header-main__nav-link">マイページ</a>
            <a href="{{ route('item.create') }}" class="header-main__action-button">出品</a>
        @endauth

        @guest
            <a href="{{ route('login') }}" class="header-main__nav-link">ログイン</a>
            <a href="{{ route('register') }}" class="header-main__nav-link">会員登録</a>
        @endguest
    </div>
</header>