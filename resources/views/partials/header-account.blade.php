@auth
    <div class="user-menu" data-user-menu>
        <button class="user-menu-trigger" type="button" data-user-menu-toggle aria-haspopup="menu" aria-expanded="false" aria-controls="user-menu-panel">
            <span class="avatar avatar-small">{{ str(auth()->user()->name)->substr(0, 1)->upper() }}</span>
            <span class="user-menu-name">{{ str(auth()->user()->name)->before(' ') }}</span>
            <span class="user-menu-chevron" aria-hidden="true">⌄</span>
        </button>
        <div class="user-menu-panel" id="user-menu-panel" role="menu">
            <div class="user-menu-heading"><strong>{{ auth()->user()->name }}</strong><small>{{ auth()->user()->email }}</small></div>
            <a role="menuitem" href="{{ route('dashboard.index') }}">My dashboard</a>
            <a role="menuitem" href="{{ route('dashboard.index') }}#my-prompts">My prompts</a>
            <a role="menuitem" href="{{ route('dashboard.index') }}#favorites">Saved prompts</a>
            @if (auth()->user()->isAdmin())<a role="menuitem" href="{{ route('admin.dashboard') }}">Admin studio</a>@endif
            <div class="user-menu-divider"></div>
            <form method="POST" action="{{ route('logout') }}">@csrf<button class="user-menu-logout" type="submit" role="menuitem">Log Out <span>↗</span></button></form>
        </div>
    </div>
@else
    <a class="top-login" href="{{ route('login') }}">Log in <span>↗</span></a>
@endauth
