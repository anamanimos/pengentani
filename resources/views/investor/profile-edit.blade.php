@extends('layouts.investor')

@section('content')
<div class="hero-card" style="padding-bottom: 20px;">
    <div style="margin-bottom: 20px;">
        <a href="{{ route('investor.profile') }}" style="color: white; text-decoration: none; display: flex; align-items: center; font-size: 0.9rem; font-weight: 500;">
            <i class="ki-duotone ki-arrow-left fs-3 me-2 text-white"><span class="path1"></span><span class="path2"></span></i>
            Kembali
        </a>
    </div>

    <div style="text-align: center; position: relative; z-index: 2;">
        <h2 style="font-size: 1.3rem; font-weight: 700; margin-bottom: 5px;">Edit Profil</h2>
        <p style="font-size: 0.85rem; opacity: 0.8; margin-bottom: 0;">Perbarui informasi akun Anda</p>
    </div>
</div>

<div class="content-area" style="padding-top: 10px;">

    @if ($errors->any())
        <div style="background: rgba(220, 38, 38, 0.1); border: 1px solid rgba(220, 38, 38, 0.3); border-radius: 12px; padding: 15px; margin-bottom: 20px;">
            <ul style="color: var(--danger); font-size: 0.85rem; margin: 0; padding-left: 20px;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="investment-card">
        <form method="POST" action="{{ route('investor.profile.update') }}">
            @csrf
            @method('PATCH')

            <div style="margin-bottom: 15px;">
                <label style="display: block; font-size: 0.85rem; font-weight: 600; color: var(--text-muted); margin-bottom: 5px;">Nama Lengkap</label>
                <input type="text" name="name" value="{{ old('name', $user->name) }}" required style="width: 100%; padding: 12px 15px; border-radius: 10px; border: 1px solid rgba(0,0,0,0.1); background: rgba(255,255,255,0.5); font-family: 'Outfit', sans-serif; font-size: 0.95rem; box-sizing: border-box; outline: none; transition: border-color 0.2s;">
            </div>

            <div style="margin-bottom: 15px;">
                <label style="display: block; font-size: 0.85rem; font-weight: 600; color: var(--text-muted); margin-bottom: 5px;">Email</label>
                <input type="email" name="email" value="{{ old('email', $user->email) }}" required style="width: 100%; padding: 12px 15px; border-radius: 10px; border: 1px solid rgba(0,0,0,0.1); background: rgba(255,255,255,0.5); font-family: 'Outfit', sans-serif; font-size: 0.95rem; box-sizing: border-box; outline: none; transition: border-color 0.2s;">
            </div>

            <div style="margin-bottom: 15px;">
                <label style="display: block; font-size: 0.85rem; font-weight: 600; color: var(--text-muted); margin-bottom: 5px;">Nomor WhatsApp</label>
                <input type="text" name="whatsapp" value="{{ old('whatsapp', $user->whatsapp) }}" style="width: 100%; padding: 12px 15px; border-radius: 10px; border: 1px solid rgba(0,0,0,0.1); background: rgba(255,255,255,0.5); font-family: 'Outfit', sans-serif; font-size: 0.95rem; box-sizing: border-box; outline: none; transition: border-color 0.2s;">
            </div>

            <hr style="border: 0; border-top: 1px dashed rgba(0,0,0,0.1); margin: 25px 0;">
            <p style="font-size: 0.8rem; color: var(--text-muted); margin-bottom: 15px;">Kosongkan kolom password di bawah ini jika tidak ingin mengubah password.</p>

            <div style="margin-bottom: 15px;">
                <label style="display: block; font-size: 0.85rem; font-weight: 600; color: var(--text-muted); margin-bottom: 5px;">Password Baru</label>
                <input type="password" name="password" style="width: 100%; padding: 12px 15px; border-radius: 10px; border: 1px solid rgba(0,0,0,0.1); background: rgba(255,255,255,0.5); font-family: 'Outfit', sans-serif; font-size: 0.95rem; box-sizing: border-box; outline: none; transition: border-color 0.2s;">
            </div>

            <div style="margin-bottom: 25px;">
                <label style="display: block; font-size: 0.85rem; font-weight: 600; color: var(--text-muted); margin-bottom: 5px;">Konfirmasi Password Baru</label>
                <input type="password" name="password_confirmation" style="width: 100%; padding: 12px 15px; border-radius: 10px; border: 1px solid rgba(0,0,0,0.1); background: rgba(255,255,255,0.5); font-family: 'Outfit', sans-serif; font-size: 0.95rem; box-sizing: border-box; outline: none; transition: border-color 0.2s;">
            </div>

            <button type="submit" style="width: 100%; background: var(--primary); color: white; border: none; padding: 14px; border-radius: 12px; font-size: 1rem; font-weight: 600; font-family: 'Outfit', sans-serif; cursor: pointer; box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3); transition: transform 0.2s, box-shadow 0.2s;">
                Simpan Perubahan
            </button>
        </form>
    </div>

</div>
@endsection
