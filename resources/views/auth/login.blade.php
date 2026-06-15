@extends('layouts.investor')

@section('content')
<div class="login-container">
    <div class="login-header">
        <h1>Pengen Tani</h1>
        <p>Aplikasi Investasi Pertanian</p>
    </div>

    <!-- Session Status -->
    @if (session('status'))
        <div class="alert-success">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Email Address -->
        <div class="form-group">
            <label for="email">Email</label>
            <input id="email" class="form-control" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username" placeholder="Masukkan email Anda" />
            @if ($errors->has('email'))
                <span class="text-danger mt-1" style="color: var(--danger); font-size: 0.8rem; margin-top: 5px; display: block;">{{ $errors->first('email') }}</span>
            @endif
        </div>

        <!-- Password -->
        <div class="form-group">
            <label for="password">Password</label>
            <input id="password" class="form-control" type="password" name="password" required autocomplete="current-password" placeholder="Masukkan password Anda" />
            @if ($errors->has('password'))
                <span class="text-danger mt-1" style="color: var(--danger); font-size: 0.8rem; margin-top: 5px; display: block;">{{ $errors->first('password') }}</span>
            @endif
        </div>

        <!-- Remember Me -->
        <div class="form-group" style="display: flex; justify-content: space-between; align-items: center;">
            <label for="remember_me" style="display: flex; align-items: center; cursor: pointer; margin-bottom: 0;">
                <input id="remember_me" type="checkbox" name="remember" style="margin-right: 8px;">
                <span style="font-size: 0.9rem; color: var(--text-muted); font-weight: 400;">Ingat Saya</span>
            </label>
            
            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}" style="color: var(--primary); text-decoration: none; font-size: 0.9rem; font-weight: 500;">
                    Lupa password?
                </a>
            @endif
        </div>

        <div>
            <button type="submit" class="btn-primary">
                Masuk
            </button>
        </div>
    </form>
</div>
@endsection
