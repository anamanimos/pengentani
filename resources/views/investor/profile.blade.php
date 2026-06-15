@extends('layouts.investor')

@section('content')
<div class="hero-card" style="padding-bottom: 30px; text-align: center;">
    <h2 style="margin-bottom: 20px; font-size: 1.2rem; font-weight: 600;">Profil Akun</h2>
    
    <div style="width: 80px; height: 80px; background: rgba(255,255,255,0.2); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2.5rem; font-weight: 700; color: white; margin: 0 auto 15px auto; backdrop-filter: blur(10px); box-shadow: 0 10px 25px rgba(0,0,0,0.1);">
        {{ strtoupper(substr($user->name, 0, 1)) }}
    </div>
    
    <h1 style="font-size: 1.5rem; font-weight: 700; margin-bottom: 5px;">{{ $user->name }}</h1>
    <p style="font-size: 0.9rem; opacity: 0.8; margin-bottom: 0;">{{ $user->email }}</p>
    @if($user->whatsapp)
        <p style="font-size: 0.85rem; opacity: 0.8; margin-top: 5px;"><i class="ki-duotone ki-whatsapp fs-6 me-1"></i> {{ $user->whatsapp }}</p>
    @endif
</div>

<div class="content-area" style="padding-top: 20px;">

    @if(session('success'))
        <div style="background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.3); border-radius: 12px; padding: 15px; margin-bottom: 20px; color: var(--primary-dark); font-size: 0.9rem; text-align: center;">
            {{ session('success') }}
        </div>
    @endif

    <!-- Summary Stats -->
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 20px;">
        <div class="investment-card" style="margin-bottom: 0; text-align: center; padding: 20px 10px;">
            <div style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 5px;">Total Investasi</div>
            <div style="font-size: 1.1rem; font-weight: 700; color: var(--primary-dark);">Rp {{ number_format($totalInvestment, 0, ',', '.') }}</div>
        </div>
        <div class="investment-card" style="margin-bottom: 0; text-align: center; padding: 20px 10px;">
            <div style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 5px;">Proyek Aktif</div>
            <div style="font-size: 1.2rem; font-weight: 700; color: var(--text);">{{ $activeProjects }}</div>
        </div>
    </div>

    <!-- Account Settings -->
    <div class="investment-card">
        <h3 class="section-title" style="margin-bottom: 15px; font-size: 1.05rem;">Pengaturan Akun</h3>
        <div class="card-body" style="grid-template-columns: 1fr; padding: 0;">
            <!-- Edit Profile Button -->
            <a href="{{ route('investor.profile.edit') }}" style="width: 100%; background: transparent; border: none; padding: 15px; text-align: left; display: flex; align-items: center; color: var(--text); font-weight: 600; font-size: 1rem; cursor: pointer; text-decoration: none; border-bottom: 1px solid rgba(0,0,0,0.05);">
                <i class="ki-duotone ki-user-edit fs-2 me-3" style="opacity: 0.7;"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                Edit Profil
            </a>

            <!-- Logout Button -->
            <form method="POST" action="{{ route('logout') }}" style="margin: 0;">
                @csrf
                <button type="submit" style="width: 100%; background: transparent; border: none; padding: 15px; text-align: left; display: flex; align-items: center; color: var(--danger); font-weight: 600; font-size: 1rem; border-radius: 12px; cursor: pointer;">
                    <i class="ki-duotone ki-exit-right fs-2 me-3"><span class="path1"></span><span class="path2"></span></i>
                    Keluar / Logout
                </button>
            </form>
        </div>
    </div>

</div>
@endsection
