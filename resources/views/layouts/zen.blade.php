<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', \App\Support\Branding::portalTitle()) — {{ \App\Support\Branding::institutionName() }}</title>
    <link rel="icon" type="image/png" href="{{ \App\Support\Branding::logoUrl() }}">
    <link rel="stylesheet" href="{{ asset('css/zendy-app.css') }}?v=2">
    @include('layouts.partials.portal-branding')
    @stack('styles')
    @yield('styles')
</head>
<body>
@php
    $user = auth()->user();
    $initials = strtoupper(substr($user->fname ?? 'U', 0, 1) . substr($user->lname ?? '', 0, 1));
    $currentRoute = request()->route()?->getName();
    $roleLabels = \App\Models\User::roleOptions();
    $activeClickId = session(\App\Services\ZendyTrackingService::SESSION_CLICK_ID);
    $activeLaunchedAt = session(\App\Services\ZendyTrackingService::SESSION_LAUNCHED_AT);
@endphp

<div class="app-shell" id="appShell">
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <img src="{{ \App\Support\Branding::logoUrl() }}" alt="{{ \App\Support\Branding::institutionName() }}">
            <div class="sidebar-brand-text">
                <div class="sidebar-brand-title">{{ \App\Support\Branding::portalTitle() }}</div>
                <div class="sidebar-brand-sub">{{ \App\Support\Branding::institutionName() }}</div>
            </div>
        </div>

        <button type="button" class="sidebar-toggle" id="sidebarCollapseBtn" aria-label="Toggle sidebar">
            <span class="icon">☰</span>
            <span class="toggle-label">Collapse menu</span>
        </button>

        <nav class="sidebar-nav">
            <div class="sidebar-section-label">Main</div>
            <a href="{{ route('zendy.home') }}" class="sidebar-link {{ $currentRoute === 'zendy.home' ? 'active' : '' }}">
                <span class="icon">🏠</span>
                <span class="label">Dashboard</span>
            </a>
            <a href="{{ route('zendy.launch') }}" class="sidebar-link {{ $currentRoute === 'zendy.launch' ? 'active' : '' }}">
                <span class="icon">↗</span>
                <span class="label">Go to Zendy</span>
            </a>
            @cannot('isAdmin')
            <a href="{{ route('zendy.activity') }}" class="sidebar-link {{ $currentRoute === 'zendy.activity' ? 'active' : '' }}">
                <span class="icon">🕐</span>
                <span class="label">My Activity</span>
            </a>
            @endcannot

            @can('isAdmin')
            <div class="sidebar-section-label">Administration</div>
            <a href="{{ route('zendy.logs') }}" class="sidebar-link {{ $currentRoute === 'zendy.logs' ? 'active' : '' }}">
                <span class="icon">📋</span>
                <span class="label">Activity Logs</span>
            </a>
            <a href="{{ route('zendy.reports') }}" class="sidebar-link {{ $currentRoute === 'zendy.reports' ? 'active' : '' }}">
                <span class="icon">📊</span>
                <span class="label">Reports</span>
            </a>
            <a href="{{ route('users.index') }}" class="sidebar-link {{ in_array($currentRoute, ['users.index', 'users.edit', 'users.create']) ? 'active' : '' }}">
                <span class="icon">👥</span>
                <span class="label">Users</span>
            </a>
            <a href="{{ route('pending.index') }}" class="sidebar-link {{ $currentRoute === 'pending.index' ? 'active' : '' }}">
                <span class="icon">⏳</span>
                <span class="label">Pending Approvals</span>
            </a>
            @endcan
        </nav>

        <div class="sidebar-footer">
            <div class="sidebar-user">
                <div class="sidebar-user-avatar">{{ $initials }}</div>
                <div class="sidebar-user-info">
                    <div class="sidebar-user-name">{{ $user->fname }} {{ $user->lname }}</div>
                    <div class="sidebar-user-role">{{ $roleLabels[$user->role] ?? ucfirst($user->role) }}</div>
                </div>
            </div>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="sidebar-logout">
                    <span>⏻</span>
                    <span class="label">Sign out</span>
                </button>
            </form>
        </div>
    </aside>

    <div class="sidebar-backdrop" id="sidebarBackdrop"></div>

    <div class="main-wrapper">
        <header class="topbar">
            <button type="button" class="mobile-menu-btn" id="mobileMenuBtn" aria-label="Open menu">☰</button>
            <div>
                <h1 class="topbar-title">@yield('page_title', 'Dashboard')</h1>
                @hasSection('page_subtitle')
                    <p class="topbar-subtitle">@yield('page_subtitle')</p>
                @endif
            </div>
        </header>

        <main class="main-content">
            @if(session('success'))
                <div class="alert-app alert-success-app">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="alert-app alert-danger-app">{{ session('error') }}</div>
            @endif

            @yield('content')
        </main>
    </div>
</div>

<script src="{{ asset('js/sidebar.js') }}?v=1"></script>
<script>
    window.zendySessionConfig = {
        endUrl: @json(route('zendy.session-end')),
        @if($activeClickId && $activeLaunchedAt)
        clickId: @json($activeClickId),
        launchedAt: @json($activeLaunchedAt),
        @else
        clearSession: true,
        @endif
    };
</script>
<script src="{{ asset('js/zendy-session.js') }}?v=1"></script>
@stack('scripts')
@yield('footer')
</body>
</html>
