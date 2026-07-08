<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Pengen Tani - Investor</title>
    <link rel="shortcut icon" href="{{ asset('pengentani-icon.png') }}" />
    <link rel="stylesheet" href="{{ asset('css/investor-app.css') }}?v={{ filemtime(public_path('css/investor-app.css')) }}">
    <meta name="theme-color" content="#16a34a">
    <!-- Include Metronic Plugins for Icons -->
    <link href="{{ asset('assets/plugins/global/plugins.bundle.css') }}" rel="stylesheet" type="text/css" />
    @stack('styles')
</head>
<body>
    <div class="bg-mesh"></div>
    <div class="mobile-container">
        @if(session()->has('impersonate_by'))
        <div style="background: var(--danger); color: white; padding: 10px; text-align: center; font-size: 0.85rem; font-weight: 600; display: flex; justify-content: space-between; align-items: center; margin-bottom: 0; position: sticky; top: 0; z-index: 50; box-shadow: 0 4px 10px rgba(0,0,0,0.1);">
            <span>Mode Login Sebagai {{ auth()->user()->name }}</span>
            <form action="{{ route('users.stop_impersonate') }}" method="POST" style="margin: 0;">
                @csrf
                <button type="submit" style="background: white; color: var(--danger); border: none; padding: 5px 10px; border-radius: 8px; font-weight: 700; font-size: 0.75rem; cursor: pointer;">
                    Kembali
                </button>
            </form>
        </div>
        @endif

        @yield('content')
    </div>
    
    @auth
    <nav class="bottom-nav">
        <a href="{{ route('dashboard') }}" class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <i class="ki-duotone ki-home fs-2x mb-1"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span></i>
            <span>Beranda</span>
        </a>

        <a href="{{ route('investor.portfolio') }}" class="nav-item {{ request()->routeIs('investor.portfolio') ? 'active' : '' }}">
            <i class="ki-duotone ki-briefcase fs-2x mb-1"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span></i>
            <span>Portofolio</span>
        </a>

        <a href="{{ route('investor.opportunities') }}" class="nav-item {{ request()->routeIs('investor.opportunities') ? 'active' : '' }}">
            <i class="ki-duotone ki-star fs-2x mb-1"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span></i>
            <span>Peluang</span>
        </a>

        <a href="{{ route('investor.withdrawals') }}" class="nav-item {{ request()->routeIs('investor.withdrawals') ? 'active' : '' }}">
            <i class="ki-duotone ki-wallet fs-2x mb-1"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span></i>
            <span>Penarikan</span>
        </a>
        
        @if(auth()->user()->isAdmin() || auth()->user()->isPengelola())
        <a href="{{ route('console.dashboard') }}" class="nav-item">
            <i class="ki-duotone ki-setting-2 fs-2x mb-1"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span></i>
            <span>Console</span>
        </a>
        @endif

        <a href="{{ route('investor.profile') }}" class="nav-item {{ request()->routeIs('investor.profile') ? 'active' : '' }}">
            <i class="ki-duotone ki-user fs-2x mb-1"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span></i>
            <span>Profil</span>
        </a>
    </nav>
    @endauth
    @stack('scripts')
</body>
</html>
