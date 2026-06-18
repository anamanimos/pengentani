@extends('layouts.investor')

@section('content')
<div class="auth-wrapper">
    <!-- Animated background blobs -->
    <div class="blob blob-1"></div>
    <div class="blob blob-2"></div>
    
    <div class="glass-card login-box fade-in-up">
        <div class="login-header">
            <div class="logo-container zoom-in">
                <img src="{{ asset('pengentani.png') }}" alt="Logo Pengen Tani">
            </div>
            <h2 class="welcome-text">Buat Akun Baru</h2>
            <p class="subtitle-text">Bergabunglah dengan Pengen Tani</p>
        </div>

        <form method="POST" action="{{ route('register') }}" class="modern-form" id="kt_sign_up_form">
            @csrf

            <!-- Name -->
            <div class="input-group-modern">
                <div class="input-icon">
                    <i class="ki-duotone ki-user fs-3">
                        <span class="path1"></span><span class="path2"></span>
                    </i>
                </div>
                <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name" class="modern-input" placeholder=" " />
                <label for="name" class="floating-label">Nama Lengkap</label>
            </div>
            @if ($errors->has('name'))
                <div class="error-text slide-down"><i class="ki-duotone ki-information-5"></i> {{ $errors->first('name') }}</div>
            @endif

            <!-- Email Address -->
            <div class="input-group-modern form-margin-top">
                <div class="input-icon">
                    <i class="ki-duotone ki-sms fs-3">
                        <span class="path1"></span><span class="path2"></span>
                    </i>
                </div>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="email" class="modern-input" placeholder=" " />
                <label for="email" class="floating-label">Alamat Email</label>
            </div>
            @if ($errors->has('email'))
                <div class="error-text slide-down"><i class="ki-duotone ki-information-5"></i> {{ $errors->first('email') }}</div>
            @endif

            <!-- Password -->
            <div class="input-group-modern form-margin-top">
                <div class="input-icon">
                    <i class="ki-duotone ki-lock-2 fs-3">
                        <span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span>
                    </i>
                </div>
                <input id="password" type="password" name="password" required autocomplete="new-password" class="modern-input" placeholder=" " />
                <label for="password" class="floating-label">Kata Sandi (Min 8 Karakter)</label>
            </div>
            @if ($errors->has('password'))
                <div class="error-text slide-down"><i class="ki-duotone ki-information-5"></i> {{ $errors->first('password') }}</div>
            @endif

            <!-- Confirm Password -->
            <div class="input-group-modern form-margin-top">
                <div class="input-icon">
                    <i class="ki-duotone ki-lock-2 fs-3">
                        <span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span>
                    </i>
                </div>
                <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" class="modern-input" placeholder=" " />
                <label for="password_confirmation" class="floating-label">Ulangi Kata Sandi</label>
            </div>
            @if ($errors->has('password_confirmation'))
                <div class="error-text slide-down"><i class="ki-duotone ki-information-5"></i> {{ $errors->first('password_confirmation') }}</div>
            @endif

            <!-- Submit Button -->
            <div class="submit-container form-margin-top-large">
                <button type="submit" id="kt_sign_up_submit" class="btn-modern">
                    <span class="indicator-label">Daftar Sekarang</span>
                    <i class="ki-duotone ki-arrow-right fs-2 ms-2">
                        <span class="path1"></span><span class="path2"></span>
                    </i>
                </button>
            </div>
            
            <div class="register-prompt form-margin-top">
                Sudah punya akun? <a href="{{ route('login') }}" class="register-link">Masuk di sini</a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('styles')
<style>
    .auth-wrapper {
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
        position: relative;
        overflow: hidden;
        font-family: 'Outfit', sans-serif;
    }
    .blob {
        position: absolute;
        filter: blur(60px);
        z-index: -1;
        opacity: 0.6;
        animation: float 10s infinite ease-in-out alternate;
    }
    .blob-1 {
        width: 300px;
        height: 300px;
        background: linear-gradient(135deg, #10b981 0%, #3b82f6 100%);
        top: -10%;
        left: -10%;
        border-radius: 50%;
    }
    .blob-2 {
        width: 250px;
        height: 250px;
        background: linear-gradient(135deg, #f59e0b 0%, #10b981 100%);
        bottom: -5%;
        right: -5%;
        border-radius: 50%;
        animation-delay: -5s;
    }
    @keyframes float {
        0% { transform: translate(0, 0) scale(1); }
        100% { transform: translate(30px, 50px) scale(1.1); }
    }
    .glass-card {
        background: rgba(255, 255, 255, 0.75);
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.5);
        border-radius: 24px;
        padding: 40px 30px;
        width: 100%;
        max-width: 420px;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.08);
        z-index: 10;
    }
    .login-header {
        text-align: center;
        margin-bottom: 35px;
    }
    .logo-container {
        margin-bottom: 20px;
    }
    .logo-container img {
        height: 85px;
        max-width: 100%;
        object-fit: contain;
        filter: drop-shadow(0px 8px 16px rgba(16, 185, 129, 0.2));
    }
    .welcome-text {
        font-size: 1.75rem;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 5px;
        letter-spacing: -0.5px;
        margin-top: 0;
    }
    .subtitle-text {
        color: #64748b;
        font-size: 0.95rem;
        font-weight: 400;
        margin-top: 0;
    }
    .form-margin-top { margin-top: 20px; }
    .form-margin-top-large { margin-top: 30px; }
    .input-group-modern {
        position: relative;
        background: #ffffff;
        border-radius: 14px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.02);
        border: 1px solid #e2e8f0;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        overflow: hidden;
        height: 60px;
        box-sizing: border-box;
    }
    .input-group-modern:focus-within {
        border-color: #10b981;
        box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.1);
        transform: translateY(-2px);
    }
    .input-icon {
        padding: 0 0 0 15px;
        color: #94a3b8;
        display: flex;
        align-items: center;
        transition: color 0.3s ease;
    }
    .input-group-modern:focus-within .input-icon {
        color: #10b981;
    }
    .modern-input {
        width: 100%;
        border: none;
        background: transparent;
        padding: 22px 15px 10px 15px;
        font-size: 1rem;
        color: #334155;
        font-family: inherit;
        font-weight: 500;
    }
    .modern-input:focus { outline: none; }
    .floating-label {
        position: absolute;
        left: 45px;
        top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
        font-size: 1rem;
        transition: all 0.2s ease;
        pointer-events: none;
        font-weight: 400;
    }
    .modern-input:focus ~ .floating-label,
    .modern-input:not(:placeholder-shown) ~ .floating-label {
        top: 12px;
        font-size: 0.75rem;
        font-weight: 600;
        color: #10b981;
    }
    .error-text {
        color: #ef4444;
        font-size: 0.85rem;
        margin-top: 6px;
        margin-left: 5px;
        display: flex;
        align-items: center;
        gap: 5px;
        font-weight: 500;
    }
    .btn-modern {
        width: 100%;
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: white;
        border: none;
        padding: 15px;
        border-radius: 14px;
        font-size: 1.05rem;
        font-weight: 600;
        cursor: pointer;
        display: flex;
        justify-content: center;
        align-items: center;
        transition: all 0.3s ease;
        box-shadow: 0 10px 20px rgba(16, 185, 129, 0.3);
    }
    .btn-modern:hover {
        transform: translateY(-3px);
        box-shadow: 0 15px 25px rgba(16, 185, 129, 0.4);
    }
    .btn-modern:active {
        transform: translateY(1px);
        box-shadow: 0 5px 10px rgba(16, 185, 129, 0.3);
    }
    .register-prompt {
        text-align: center;
        color: #64748b;
        font-size: 0.95rem;
        font-weight: 500;
    }
    .register-link {
        color: #10b981;
        text-decoration: none;
        font-weight: 700;
        transition: color 0.2s;
    }
    .register-link:hover { color: #059669; }
    .fade-in-up { animation: fadeInUp 0.8s cubic-bezier(0.16, 1, 0.3, 1); }
    .zoom-in { animation: zoomIn 0.8s cubic-bezier(0.16, 1, 0.3, 1); }
    .slide-down { animation: slideDown 0.3s ease; }
    @keyframes fadeInUp { 0% { opacity: 0; transform: translateY(30px); } 100% { opacity: 1; transform: translateY(0); } }
    @keyframes zoomIn { 0% { opacity: 0; transform: scale(0.8); } 100% { opacity: 1; transform: scale(1); } }
    @keyframes slideDown { 0% { opacity: 0; transform: translateY(-10px); } 100% { opacity: 1; transform: translateY(0); } }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('kt_sign_up_form');
        const submitButton = document.getElementById('kt_sign_up_submit');

        form.addEventListener('submit', function (e) {
            e.preventDefault();
            
            // Basic UI indication
            submitButton.innerHTML = '<span>Memproses...</span>';
            submitButton.disabled = true;

            // Bersihkan error
            document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
            document.querySelectorAll('.ajax-error').forEach(el => el.remove());

            axios.post(form.action, new FormData(form))
                .then(function (response) {
                    alert("Registrasi berhasil! Akun Anda sedang menunggu aktivasi oleh Admin.");
                    setTimeout(function() {
                        window.location.href = "{{ route('login') }}";
                    }, 1000);
                })
                .catch(function (error) {
                    submitButton.innerHTML = '<span>Daftar Sekarang</span><i class="ki-duotone ki-arrow-right fs-2 ms-2"><span class="path1"></span><span class="path2"></span></i>';
                    submitButton.disabled = false;
                    
                    if (error.response && error.response.status === 422) {
                        let errors = error.response.data.errors;
                        for (let field in errors) {
                            let input = form.querySelector('[name="' + field + '"]');
                            if (input) {
                                let errorDiv = document.createElement('div');
                                errorDiv.className = 'error-text slide-down ajax-error';
                                errorDiv.innerHTML = '<i class="ki-duotone ki-information-5"></i> ' + errors[field][0];
                                input.parentNode.insertAdjacentElement('afterend', errorDiv);
                            }
                        }
                    } else {
                        alert("Terjadi kesalahan sistem, silakan coba lagi.");
                    }
                });
        });
    });
</script>
@endpush
