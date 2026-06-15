<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Konfirmasi Ganti Akun - Pengen Tani</title>
    <!-- Include Metronic Icons -->
    <link href="{{ asset('assets/plugins/global/plugins.bundle.css') }}" rel="stylesheet" type="text/css" />
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            background-color: #f0fdf4;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
            font-family: 'Outfit', sans-serif;
            background-image: radial-gradient(at 0% 0%, hsla(153,100%,74%,0.2) 0px, transparent 50%),
                              radial-gradient(at 100% 100%, hsla(161,100%,85%,0.3) 0px, transparent 50%);
        }
        .card-confirm {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            border-radius: 24px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.08);
            padding: 40px 30px;
            max-width: 420px;
            width: 90%;
            text-align: center;
            border: 1px solid rgba(255, 255, 255, 0.6);
        }
        .btn-yes {
            background: #10b981;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 1rem;
            font-family: 'Outfit', sans-serif;
            text-decoration: none;
            display: inline-block;
            margin: 5px;
            cursor: pointer;
            box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .btn-yes:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(16, 185, 129, 0.4); color: white; }
        
        .btn-no {
            background: #f8fafc;
            color: #475569;
            border: 1px solid #e2e8f0;
            padding: 12px 24px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 1rem;
            font-family: 'Outfit', sans-serif;
            text-decoration: none;
            display: inline-block;
            margin: 5px;
            cursor: pointer;
            transition: all 0.2s;
        }
        .btn-no:hover { background: #f1f5f9; color: #0f172a; }

        .icon-circle {
            width: 80px; height: 80px;
            background: rgba(245, 158, 11, 0.1);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 20px auto;
        }
    </style>
</head>
<body>

    <div class="card-confirm">
        <div class="icon-circle">
            <i class="ki-duotone ki-information-5 fs-4x text-warning"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
        </div>
        
        <h2 style="font-weight: 700; color: #064e3b; margin-bottom: 15px; font-size: 1.5rem;">Ganti Akun?</h2>
        
        <p style="color: #475569; font-size: 1rem; margin-bottom: 30px; line-height: 1.6;">
            Anda saat ini sedang login sebagai <strong style="color: #0f172a;">{{ Auth::user()->name }}</strong>.<br><br>
            Apakah Anda ingin keluar dan beralih ke akun <strong style="color: #10b981;">{{ $user->name }}</strong>?
        </p>

        <div style="display: flex; justify-content: center; flex-wrap: wrap;">
            <a href="{{ route(Auth::user()->isInvestor() ? 'dashboard' : 'console.dashboard') }}" class="btn-no">Batal</a>
            <form action="{{ URL::signedRoute('autologin.force', ['user' => $user->id]) }}" method="POST" style="margin: 0;">
                @csrf
                <button type="submit" class="btn-yes">Ya, Ganti Akun</button>
            </form>
        </div>
    </div>

</body>
</html>
