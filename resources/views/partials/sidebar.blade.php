<aside class="sidebar" id="sidebar">
    <a class="brand" href="{{ route('prompts.index') }}" aria-label="{{ $directorySettings['site_name'] }} home">
        @if (!empty($directorySettings['logo_url']))
            <span class="brand-mark"><img src="{{ $directorySettings['logo_url'] }}" alt="{{ $directorySettings['site_name'] }}" style="width: 24px; height: 24px; object-fit: contain; border-radius: 5px;"></span>
        @else
            <span class="brand-mark"><svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M2 18.7 8.15 6.4l3.15 6.1 2.1-3.6L22 18.7H2Z" fill="currentColor"/><path d="m7.7 14.2 2.8 1.65 2.35-4.05 3.55 6.9" stroke="#fff" stroke-width=".75" opacity=".75"/></svg></span>
        @endif
        <span class="brand-name">{{ $directorySettings['site_name'] }}</span>
    </a>

    <nav class="side-scroll" aria-label="Main navigation">
        <div class="side-group">
            <a class="side-link {{ request()->routeIs('prompts.index', 'prompts.show') && !request('category') ? 'is-active' : '' }}" href="{{ route('prompts.index') }}"><span class="side-icon">⌂</span><span>Home</span></a>
            <a class="side-link" href="{{ auth()->check() ? route('dashboard.index').'#favorites' : route('login') }}"><span class="side-icon">♡</span><span>Favorites</span></a>
            @auth
                <a class="side-link {{ request()->routeIs('dashboard.profile.*') ? 'is-active' : '' }}" href="{{ route('dashboard.profile.edit') }}"><span class="side-icon">⚙</span><span>Profile settings</span></a>
                <a class="side-link {{ request()->routeIs('creators.show') && request()->route('user')?->id === auth()->id() ? 'is-active' : '' }}" href="{{ route('creators.show', auth()->user()) }}"><span class="side-icon">👤</span><span>My profile</span></a>
                @if (auth()->user()->isAdmin())
                    <a class="side-link {{ request()->routeIs('admin.dashboard') ? 'is-active' : '' }}" href="{{ route('admin.dashboard') }}"><span class="side-icon">✧</span><span>Admin studio</span></a>
                @endif
            @endauth
        </div>

        @auth
            @if (auth()->user()->isAdmin())
                <div class="side-section admin-side-section">
                    <div class="side-heading">Admin tools</div>
                    <a class="side-link {{ request()->routeIs('admin.prompts.*') ? 'is-active' : '' }}" href="{{ route('admin.prompts.index', ['status' => 'pending']) }}"><span class="side-icon">◷</span><span>Review queue</span></a>
                    <a class="side-link {{ request()->routeIs('admin.categories.*') ? 'is-active' : '' }}" href="{{ route('admin.categories.index') }}"><span class="side-icon">▦</span><span>Categories</span></a>
                    <a class="side-link {{ request()->routeIs('admin.models.*') ? 'is-active' : '' }}" href="{{ route('admin.models.index') }}"><span class="side-icon">◈</span><span>AI Models</span></a>
                    <a class="side-link {{ request()->routeIs('admin.users.*') ? 'is-active' : '' }}" href="{{ route('admin.users.index') }}"><span class="side-icon">◎</span><span>Members</span></a>
                    <a class="side-link {{ request()->routeIs('admin.contacts.*') ? 'is-active' : '' }}" href="{{ route('admin.contacts.index') }}"><span class="side-icon">✉</span><span>Contacts</span></a>
                    <a class="side-link {{ request()->routeIs('admin.newsletters.*') ? 'is-active' : '' }}" href="{{ route('admin.newsletters.index') }}"><span class="side-icon">📬</span><span>Newsletters</span></a>
                    <a class="side-link {{ request()->routeIs('admin.settings*') ? 'is-active' : '' }}" href="{{ route('admin.settings') }}"><span class="side-icon">⚙</span><span>Settings</span></a>
                </div>
            @endif
        @endauth

        <div class="side-section">
            <div class="side-heading">Categories <span class="side-more">• • •</span></div>
            <div class="category-list">
                <a class="side-link side-category {{ !request('category') ? 'is-selected' : '' }}" href="{{ route('prompts.index') }}"><span class="category-dot dot-all">✦</span><span>All</span></a>
                @foreach ($sidebarCategories as $category)
                    <a class="side-link side-category {{ request('category') === $category->slug ? 'is-selected' : '' }}" href="{{ route('prompts.index', ['category' => $category->slug]) }}">
                        <span class="category-dot">{{ $category->icon }}</span><span>{{ $category->name }}</span>
                    </a>
                @endforeach
            </div>
        </div>

    </nav>

    <div class="sidebar-bottom">
        @guest
            @if ($directorySettings['allow_registrations'] === '1')
                <a class="publish-card" href="{{ route('register') }}"><span class="publish-spark">✦</span><span><strong>Publish &amp; get noticed</strong><small>Share your best AI prompts</small></span><span class="publish-arrow">↗</span></a>
            @endif
            <a class="get-started" href="{{ route($directorySettings['allow_registrations'] === '1' ? 'register' : 'login') }}">{{ $directorySettings['allow_registrations'] === '1' ? 'Get started' : 'Log in' }} <span>→</span></a>
        @else
            @if ($directorySettings['allow_submissions'] === '1')
                <a class="publish-card" href="{{ route('dashboard.prompts.create') }}"><span class="publish-spark">✦</span><span><strong>Publish a prompt</strong><small>Share your best AI prompts</small></span><span class="publish-arrow">↗</span></a>
            @else
                <div class="publish-card publish-card-paused"><span class="publish-spark">◷</span><span><strong>Submissions paused</strong><small>New prompts are temporarily closed</small></span></div>
            @endif
            <div class="signed-in-row">
                <a class="signed-user" href="{{ route('dashboard.index') }}"><span class="avatar">{{ str(auth()->user()->name)->substr(0, 1)->upper() }}</span><span><strong>{{ auth()->user()->name }}</strong><small>{{ auth()->user()->isAdmin() ? 'Administrator' : 'Creator account' }}</small></span></a>
                <form method="POST" action="{{ route('logout') }}">@csrf<button class="logout-icon" type="submit" aria-label="Sign out">↗</button></form>
            </div>
        @endguest
    </div>
</aside>
