@php
    $currentUser = auth()->user();
    $pageTitle = $pageTitle ?? 'Dashboard';
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <title>{{ $pageTitle }} - Startup</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">

    <link href="{{ asset('favicon.ico') }}" rel="icon">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&family=Rubik:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="{{ asset('css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/style.css') }}" rel="stylesheet">
</head>

<body class="panel-body">
    <div class="panel-shell">
        <aside class="panel-sidebar">
            <a href="{{ route('dashboard') }}" class="panel-brand">
                <span class="panel-brand-mark"><i class="fa fa-user-tie"></i></span>
                <span class="panel-brand-copy">
                    <strong>Startup</strong>
                    <small>{{ $currentUser->isAdmin() ? 'Admin Suite' : 'Client Suite' }}</small>
                </span>
            </a>

            <div class="panel-user">
                <span class="panel-avatar">{{ strtoupper(substr($currentUser->name, 0, 1)) }}</span>
                <div class="panel-user-meta">
                    <strong>{{ $currentUser->name }}</strong>
                    <small>{{ $currentUser->roleName() }}</small>
                </div>
            </div>

            <div class="panel-nav-block">
                <span class="panel-nav-label">Workspace</span>
                <nav class="panel-nav">
                    <a href="{{ route('dashboard') }}" class="panel-link {{ request()->routeIs('dashboard', 'user.dashboard', 'admin.dashboard') ? 'active' : '' }}">
                        <i class="fa fa-tachometer-alt"></i>
                        <span>Dashboard</span>
                    </a>

                    @if ($currentUser->isAdmin())
                        <a href="{{ route('admin.users') }}" class="panel-link {{ request()->routeIs('admin.users') ? 'active' : '' }}">
                            <i class="fa fa-users"></i>
                            <span>Users</span>
                        </a>
                        <a href="{{ route('admin.services') }}" class="panel-link {{ request()->routeIs('admin.services*') ? 'active' : '' }}">
                            <i class="fa fa-cubes"></i>
                            <span>Services</span>
                        </a>
                        <a href="{{ route('admin.quotes') }}" class="panel-link {{ request()->routeIs('admin.quotes*') ? 'active' : '' }}">
                            <i class="fa fa-file-signature"></i>
                            <span>Quotes</span>
                        </a>
                        <a href="{{ route('admin.contacts') }}" class="panel-link {{ request()->routeIs('admin.contacts') ? 'active' : '' }}">
                            <i class="fa fa-envelope-open-text"></i>
                            <span>Messages</span>
                        </a>
                    @endif
                </nav>
            </div>

            <div class="panel-nav-block">
                <span class="panel-nav-label">Website</span>
                <nav class="panel-nav">
                    <a href="{{ route('quote') }}" class="panel-link">
                        <i class="fa fa-file-signature"></i>
                        <span>Quote</span>
                    </a>
                    <a href="{{ route('service') }}" class="panel-link">
                        <i class="fa fa-cubes"></i>
                        <span>Services</span>
                    </a>
                    <a href="{{ route('contact') }}" class="panel-link">
                        <i class="fa fa-headset"></i>
                        <span>Contact</span>
                    </a>
                </nav>
            </div>

            <div class="panel-side-card">
                <span><i class="fa fa-bolt"></i></span>
                <div>
                    <strong>{{ now()->format('M d') }}</strong>
                    <small>Panel ready</small>
                </div>
            </div>

            <form action="{{ route('logout') }}" method="post" class="panel-logout" data-toast-pending="Logging you out...">
                @csrf
                <button type="submit">
                    <i class="fa fa-sign-out-alt"></i>
                    <span>Logout</span>
                </button>
            </form>
        </aside>

        <main class="panel-main">
            <header class="panel-topbar">
                <div>
                    <p class="panel-kicker mb-1">{{ $currentUser->isAdmin() ? 'Admin panel' : 'User panel' }}</p>
                    <h1>{{ $pageTitle }}</h1>
                    <span class="panel-subtitle">Welcome back, {{ $currentUser->name }}</span>
                </div>
                <div class="panel-topbar-actions">
                    <a href="{{ route('quote') }}" class="panel-icon-action" title="Quote">
                        <i class="fa fa-file-signature"></i>
                    </a>
                    <a href="{{ route('home') }}" class="btn btn-outline-primary">
                        <i class="fa fa-globe me-2"></i>View Site
                    </a>
                </div>
            </header>

            @yield('panel')
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    @include('partials.toasts')
    @stack('scripts')
</body>

</html>
