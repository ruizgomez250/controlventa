<nav class="main-header navbar
    {{ config('adminlte.classes_topnav_nav', 'navbar-expand') }}
    {{ config('adminlte.classes_topnav', 'navbar-white navbar-light') }}">

    <ul class="navbar-nav">
        @include('adminlte::partials.navbar.menu-item-left-sidebar-toggler')
        @each('adminlte::partials.navbar.menu-item', $adminlte->menu('navbar-left'), 'item')
        @yield('content_top_nav_left')
    </ul>

    <ul class="navbar-nav ml-auto">
        @yield('content_top_nav_right')

        {{-- Language Switcher --}}
        @php $currentLocale = app()->getLocale(); @endphp
        <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" data-toggle="dropdown" aria-expanded="false">
                <i class="fas fa-language"></i>
                {{ $currentLocale == 'en' ? 'English' : 'Español' }}
            </a>
            <div class="dropdown-menu dropdown-menu-right">
                <a class="dropdown-item {{ $currentLocale == 'es' ? 'active' : '' }}"
                   href="{{ url('idioma/es') }}">
                    <span class="flag-icon flag-icon-py mr-1"></span> Español
                </a>
                <a class="dropdown-item {{ $currentLocale == 'en' ? 'active' : '' }}"
                   href="{{ url('idioma/en') }}">
                    <span class="flag-icon flag-icon-us mr-1"></span> English
                </a>
            </div>
        </li>

        @each('adminlte::partials.navbar.menu-item', $adminlte->menu('navbar-right'), 'item')
        @if(Auth::user())
            @if(config('adminlte.usermenu_enabled'))
                @include('adminlte::partials.navbar.menu-item-dropdown-user-menu')
            @else
                @include('adminlte::partials.navbar.menu-item-logout-link')
            @endif
        @endif
        @if(config('adminlte.right_sidebar'))
            @include('adminlte::partials.navbar.menu-item-right-sidebar-toggler')
        @endif
    </ul>
</nav>
