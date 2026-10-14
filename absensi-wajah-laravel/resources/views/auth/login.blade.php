@php
    // Foto obor: otomatis dicari di public/images (obor.jpg / obor.png / obor.webp, atau di dalam folder public/images/obor/)
    $oborFiles = array_merge(
        glob(public_path('images/obor.*')) ?: [],
        glob(public_path('images/obor/*.{jpg,jpeg,png,webp,JPG,JPEG,PNG,WEBP}'), GLOB_BRACE) ?: []
    );

    $oborUrl = $oborFiles
        ? asset(ltrim(str_replace('\\', '/', substr($oborFiles[0], strlen(public_path()))), '/'))
        : null;
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Absensi Wajah</title>

    <script>
        try {
            if (localStorage.getItem('theme') === 'dark') {
                document.documentElement.setAttribute('data-theme', 'dark');
            }
        } catch (e) {}
    </script>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background: #e2e8f0;
            color: #0f172a;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }

        /* ===== Container Utama (Kartu Melayang) ===== */
        .login-card {
            width: 100%;
            max-width: 960px;
            min-height: 560px;
            background: #ffffff;
            border-radius: 24px;
            box-shadow: 0 20px 40px -15px rgba(0, 0, 0, 0.12);
            display: grid;
            grid-template-columns: 1fr 1fr;
            overflow: hidden;
            position: relative;
        }

        /* ===== Panel Kiri (Welcome / Hero dengan Background Obor) ===== */
        .hero {
            position: relative;
            background-color: #0b2a57;
            background-image:
                linear-gradient(180deg, rgba(8, 30, 66, 0.45) 0%, rgba(8, 30, 66, 0.6) 60%, rgba(8, 30, 66, 0.88) 100%),
                var(--obor, none);
            background-size: cover;
            background-position: center 30%;
            background-repeat: no-repeat;
            padding: 48px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            color: #ffffff;
        }

        .hero-top {
            text-align: center;
            z-index: 2;
        }

        .hero-top h2 {
            margin: 0 0 24px;
            font-size: 22px;
            font-weight: 500;
            letter-spacing: 0.5px;
            color: #ffffff;
        }

        .hero-logo-wrap {
            width: 72px;
            height: 72px;
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(8px);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 14px;
            box-shadow: 0 8px 16px rgba(0,0,0,0.15);
        }

        .hero-logo {
            width: 38px;
            height: 38px;
            object-fit: contain;
        }

        .hero-brand-name {
            font-size: 20px;
            font-weight: 700;
            letter-spacing: 0.5px;
        }

        .hero-desc {
            font-size: 13px;
            line-height: 1.6;
            color: rgba(255, 255, 255, 0.8);
            text-align: center;
            max-width: 300px;
            margin: 0 auto;
            z-index: 2;
        }

        .hero-footer {
            font-size: 12px;
            color: rgba(255, 255, 255, 0.7);
            text-align: center;
            z-index: 2;
        }

        /* ===== Transisi Gelombang SVG yang Mulus di Tengah ===== */
        .wave-divider {
            position: absolute;
            top: 0;
            bottom: 0;
            right: 0;
            width: 120px;
            pointer-events: none;
            z-index: 3;
            display: flex;
            align-items: center;
        }

        .wave-divider svg {
            height: 100%;
            width: 100%;
        }

        /* ===== Panel Kanan (Form Login) ===== */
        .form-side {
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 48px 56px;
            background: #ffffff;
            z-index: 2;
        }

        .form-wrap {
            width: 100%;
            max-width: 360px;
            margin: 0 auto;
        }

        h1 {
            margin: 0 0 6px;
            font-size: 24px;
            font-weight: 700;
            color: #0f172a;
            letter-spacing: -0.5px;
        }

        .subtitle {
            margin: 0 0 24px;
            font-size: 13px;
            color: #64748b;
        }

        .field {
            margin-bottom: 16px;
        }

        label {
            display: block;
            margin-bottom: 6px;
            font-size: 13px;
            font-weight: 600;
            color: #334155;
        }

        .input-wrap {
            position: relative;
        }

        .input-wrap .icon-left {
            position: absolute;
            top: 50%;
            left: 14px;
            width: 16px;
            height: 16px;
            transform: translateY(-50%);
            color: #94a3b8;
            pointer-events: none;
            transition: color 0.2s ease;
        }

        input {
            width: 100%;
            height: 42px;
            padding: 0 14px 0 42px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            outline: none;
            font-size: 13px;
            font-family: inherit;
            color: #0f172a;
            background: #f8fafc;
            transition: all 0.2s ease;
        }

        input::placeholder {
            color: #94a3b8;
        }

        input:focus {
            background: #ffffff;
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        input:focus + .icon-left,
        .input-wrap:focus-within .icon-left {
            color: #2563eb;
        }

        input.has-toggle {
            padding-right: 40px;
        }

        .toggle-pass {
            position: absolute;
            top: 50%;
            right: 6px;
            width: 30px;
            height: 30px;
            transform: translateY(-50%);
            border: 0;
            border-radius: 6px;
            background: transparent;
            color: #94a3b8;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: color 0.2s ease;
        }

        .toggle-pass:hover {
            color: #475569;
        }

        .toggle-pass .eye-off {
            display: none;
        }

        .toggle-pass.shown .eye {
            display: none;
        }

        .toggle-pass.shown .eye-off {
            display: block;
        }

        .ico {
            fill: none;
            stroke: currentColor;
            stroke-width: 2;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        .error {
            margin-bottom: 16px;
            padding: 10px 12px;
            border-radius: 8px;
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #dc2626;
            font-size: 13px;
            font-weight: 500;
        }

        /* ===== Tombol Aksi ===== */
        .action-group {
            display: flex;
            gap: 12px;
            margin-top: 18px;
            align-items: center;
        }

        .submit {
            flex: 1;
            height: 42px;
            border: 0;
            border-radius: 20px;
            background: #2563eb;
            color: #ffffff;
            font-size: 13px;
            font-weight: 600;
            font-family: inherit;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            transition: all 0.2s ease;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.25);
        }

        .submit:hover {
            background: #1d4ed8;
            box-shadow: 0 6px 16px rgba(37, 99, 235, 0.35);
        }

        .secondary-btn {
            flex: 1;
            height: 42px;
            border: 1px solid #cbd5e1;
            border-radius: 20px;
            background: transparent;
            color: #64748b;
            font-size: 13px;
            font-weight: 600;
            font-family: inherit;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
        }

        .secondary-btn:hover {
            background: #f1f5f9;
            color: #0f172a;
            border-color: #94a3b8;
        }

        /* ===== Mode Gelap (Dark Mode) ===== */
        html[data-theme="dark"] body {
            background: #020617;
        }

        html[data-theme="dark"] .login-card {
            background: #0b1220;
            box-shadow: 0 20px 40px -15px rgba(0, 0, 0, 0.5);
        }

        html[data-theme="dark"] .form-side {
            background: #0b1220;
            color: #e2e8f0;
        }

        html[data-theme="dark"] h1,
        html[data-theme="dark"] label {
            color: #f1f5f9;
        }

        html[data-theme="dark"] .subtitle {
            color: #94a3b8;
        }

        html[data-theme="dark"] input {
            background: #111a2e;
            border-color: #1e293b;
            color: #e2e8f0;
        }

        html[data-theme="dark"] input:focus {
            background: #172136;
            border-color: #3b82f6;
        }

        html[data-theme="dark"] .secondary-btn {
            border-color: #334155;
            color: #94a3b8;
        }

        html[data-theme="dark"] .secondary-btn:hover {
            background: #1e293b;
            color: #f1f5f9;
        }

        html[data-theme="dark"] .wave-divider path {
            fill: #0b1220;
        }

        /* ===== Responsif / Tampilan HP (Mobile) ===== */
        @media (max-width: 860px) {
            body {
                padding: 12px;
                align-items: stretch;
            }

            .login-card {
                grid-template-columns: 1fr;
                grid-template-rows: 220px auto;
                max-width: 440px;
                margin: 0 auto;
                border-radius: 16px;
            }

            .wave-divider {
                display: none;
            }

            .hero {
                padding: 24px;
                justify-content: center;
            }

            .hero-desc {
                display: none;
            }

            .hero-top h2 {
                margin-bottom: 12px;
                font-size: 18px;
            }

            .hero-logo-wrap {
                width: 56px;
                height: 56px;
                margin-bottom: 8px;
            }

            .hero-logo {
                width: 28px;
                height: 28px;
            }

            .form-side {
                padding: 32px 24px;
            }
        }
    </style>
</head>

<body>

<div class="login-card">

    <!-- Panel Kiri -->
    <section
        class="hero"
        @if ($oborUrl) style="--obor: url('{{ $oborUrl }}');" @endif
    >
        <div class="hero-top">
            <h2>Welcome to</h2>
            <div class="hero-logo-wrap">
                <img
                    src="{{ asset('images/logo-komdigi.png') }}"
                    alt="Logo Komdigi"
                    class="hero-logo"
                >
            </div>
            <div class="hero-brand-name">Absensi Wajah</div>
        </div>

        <p class="hero-desc">
            Sistem absensi cerdas berbasis pengenalan wajah yang aman, cepat, dan terintegrasi untuk efisiensi instansi Anda.
        </p>

        <div class="hero-footer">
            &copy; {{ date('Y') }} Sistem Absensi Wajah
        </div>

        <!-- Transisi Gelombang SVG Mulus di Batas Kiri-Kanan -->
        <div class="wave-divider">
            <svg viewBox="0 0 100 800" preserveAspectRatio="none">
                <path d="M0,0 C60,200 60,600 0,800 L100,800 L100,0 Z" fill="#ffffff" />
            </svg>
        </div>
    </section>

    <!-- Panel Kanan (Form) -->
    <section class="form-side">
        <div class="form-wrap">

            <h1>Masuk ke akun Anda</h1>
            <p class="subtitle">
                Gunakan username dan password yang diberikan Admin.
            </p>

            @if ($errors->any())
                <div class="error">
                    {{ $errors->first() }}
                </div>
            @endif

            <form
                method="POST"
                action="{{ route('login.process') }}"
            >
                @csrf

                <div class="field">
                    <label for="username">Username</label>
                    <div class="input-wrap">
                        <svg class="ico icon-left" viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        <input
                            type="text"
                            id="username"
                            name="username"
                            value="{{ old('username') }}"
                            placeholder="Masukkan username"
                            autocomplete="username"
                            required
                            autofocus
                        >
                    </div>
                </div>

                <div class="field">
                    <label for="password">Password</label>
                    <div class="input-wrap">
                        <svg class="ico icon-left" viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            class="has-toggle"
                            placeholder="Masukkan password"
                            autocomplete="current-password"
                            required
                        >

                        <button
                            type="button"
                            class="toggle-pass"
                            id="togglePass"
                            title="Tampilkan / sembunyikan password"
                            aria-label="Tampilkan atau sembunyikan password"
                        >
                            <svg class="ico eye" width="16" height="16" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                            <svg class="ico eye-off" width="16" height="16" viewBox="0 0 24 24"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                        </button>
                    </div>
                </div>

                <div class="action-group">
                    <button type="submit" class="submit">
                        <svg class="ico" width="15" height="15" viewBox="0 0 24 24"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg>
                        Masuk
                    </button>
                    <button type="button" class="secondary-btn" onclick="window.location.reload();">
                        Batal
                    </button>
                </div>
            </form>

        </div>
    </section>

</div>

<script>
    (function () {
        var btn = document.getElementById('togglePass');
        var input = document.getElementById('password');

        if (!btn || !input) return;

        btn.addEventListener('click', function () {
            var show = input.type === 'password';
            input.type = show ? 'text' : 'password';
            btn.classList.toggle('shown', show);
        });
    })();
</script>

</body>
</html>