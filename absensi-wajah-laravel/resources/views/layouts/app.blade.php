<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        @yield('title', 'ABSENSI WAJAH')
    </title>

    <meta
        name="csrf-token"
        content="{{ csrf_token() }}"
    >

    @include('partials.ui')

    @stack('styles')

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family:
                Inter,
                ui-sans-serif,
                system-ui,
                -apple-system,
                BlinkMacSystemFont,
                "Segoe UI",
                sans-serif;
            background: #f8fafc;
            color: #0f172a;
        }

        .app {
            min-height: 100vh;
            display: flex;
        }

        .sidebar {
            width: 250px;
            flex-shrink: 0;
            background: #0f172a;
            color: white;
            min-height: 100vh;
            padding: 24px 16px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            overflow: hidden;
            transition: width .2s ease;
        }

        .sidebar-top {
            display: flex;
            flex-direction: column;
        }

        .sidebar-bottom {
            padding-top: 16px;
            border-top: 1px solid #1e293b;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 28px;
            padding: 0 0 0 4px;
            min-height: 40px;
        }

        .brand-text {
            flex: 1;
            min-width: 0;
        }

        /* Logo Komdigi tanpa background putih */
        .brand-logo {
            width: 36px;
            height: 36px;
            flex-shrink: 0;
            object-fit: contain;
            background: transparent;
            border-radius: 0;
            padding: 0;
            display: block;
        }

        .brand-title {
            font-size: 15px;
            font-weight: 700;
            line-height: 1.25;
            letter-spacing: .02em;
            white-space: nowrap;
        }

        .brand-subtitle {
            font-size: 11px;
            color: #94a3b8;
            margin-top: 2px;
            white-space: nowrap;
        }

        .menu-title {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: .08em;
            color: #64748b;
            padding: 0 10px;
            margin-bottom: 8px;
        }

        .menu {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .menu a {
            text-decoration: none;
            color: #cbd5e1;
            padding: 11px 12px;
            border-radius: 8px;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 12px;
            white-space: nowrap;
        }

        .menu a:hover,
        .menu a.active {
            background: #1e293b;
            color: white;
        }

        /* Ikon menu */
        .ico {
            width: 20px;
            height: 20px;
            flex-shrink: 0;
            fill: none;
            stroke: currentColor;
            stroke-width: 2;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        /* Tombol kecilkan / lebarkan menu (di dalam brand) */
        .sb-btn {
            margin-left: auto;
            flex-shrink: 0;
            border: 0;
            background: transparent;
            color: #94a3b8;
            width: 30px;
            height: 30px;
            border-radius: 8px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .sb-btn:hover {
            background: #1e293b;
            color: #fff;
        }

        .sb-btn svg {
            transition: transform .2s ease;
        }

        /* Menu dalam keadaan kecil: hanya ikon */
        html.sb-collapsed .sidebar {
            width: 72px;
            padding: 24px 10px;
        }

        html.sb-collapsed .brand {
            flex-direction: column;
            gap: 10px;
            padding: 0;
            margin-bottom: 24px;
        }

        html.sb-collapsed .sb-btn {
            margin-left: 0;
        }

        html.sb-collapsed .sb-btn svg {
            transform: rotate(180deg);
        }

        html.sb-collapsed .brand-text,
        html.sb-collapsed .menu-title,
        html.sb-collapsed .user-name,
        html.sb-collapsed .menu a span,
        html.sb-collapsed .logout-button span {
            display: none;
        }

        html.sb-collapsed .menu a {
            justify-content: center;
            padding: 11px 0;
        }

        html.sb-collapsed .logout-button {
            padding: 10px 0;
        }

        /* Tombol mode gelap / terang */
        .theme-btn {
            border: 1px solid #e2e8f0;
            background: #ffffff;
            color: #334155;
            width: 38px;
            height: 38px;
            border-radius: 10px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .theme-btn:hover {
            background: #f1f5f9;
        }

        .theme-btn .i-sun { display: none; }

        /* ===== MODE GELAP (hanya di layar, cetak tetap terang) ===== */
        @media screen {
            html[data-theme="dark"] { color-scheme: dark; }

            html[data-theme="dark"] body { background: #0b1220; color: #e2e8f0; }

            html[data-theme="dark"] .sidebar { background: #080d1a; border-right: 1px solid #1e293b; }

            html[data-theme="dark"] .topbar { background: #0f172a; border-bottom-color: #1e293b; }
            html[data-theme="dark"] .page-title { color: #f1f5f9; }
            html[data-theme="dark"] .page-subtitle { color: #94a3b8; }

            html[data-theme="dark"] .theme-btn { background: #111a2e; border-color: #243049; color: #fcd34d; }
            html[data-theme="dark"] .theme-btn:hover { background: #1e293b; }
            html[data-theme="dark"] .theme-btn .i-moon { display: none; }
            html[data-theme="dark"] .theme-btn .i-sun { display: block; }

            html[data-theme="dark"] .panel,
            html[data-theme="dark"] .summary .item,
            html[data-theme="dark"] .card {
                background: #111a2e;
                box-shadow: 0 5px 20px rgba(0, 0, 0, .35);
            }

            html[data-theme="dark"] .data-table th,
            html[data-theme="dark"] .data-table td,
            html[data-theme="dark"] .list-item,
            html[data-theme="dark"] .detail-list dt,
            html[data-theme="dark"] .detail-list dd { border-bottom-color: #243049; }

            html[data-theme="dark"] .data-table th,
            html[data-theme="dark"] .detail-list dt,
            html[data-theme="dark"] .muted,
            html[data-theme="dark"] .summary .label,
            html[data-theme="dark"] .card-title,
            html[data-theme="dark"] .chart .lbl { color: #94a3b8; }

            html[data-theme="dark"] .data-table tr:hover td { background: #16213a; }
            html[data-theme="dark"] .empty,
            html[data-theme="dark"] .card-note { color: #64748b; }

            html[data-theme="dark"] .btn { background: #1e293b; color: #e2e8f0; }
            html[data-theme="dark"] .btn-light { background: #1e293b; color: #cbd5e1; }
            html[data-theme="dark"] .btn-primary { background: #2563eb; color: #fff; }
            html[data-theme="dark"] .btn-danger { background: rgba(239, 68, 68, .16); color: #fca5a5; }
            html[data-theme="dark"] .btn-warning { background: rgba(245, 158, 11, .16); color: #fcd34d; }
            html[data-theme="dark"] .btn-success { background: rgba(34, 197, 94, .16); color: #86efac; }

            html[data-theme="dark"] .badge-green { background: rgba(34, 197, 94, .18); color: #86efac; }
            html[data-theme="dark"] .badge-red { background: rgba(239, 68, 68, .18); color: #fca5a5; }
            html[data-theme="dark"] .badge-yellow { background: rgba(245, 158, 11, .18); color: #fcd34d; }
            html[data-theme="dark"] .badge-blue { background: rgba(59, 130, 246, .20); color: #93c5fd; }
            html[data-theme="dark"] .badge-gray { background: #1e293b; color: #94a3b8; }

            html[data-theme="dark"] .field input,
            html[data-theme="dark"] .field select,
            html[data-theme="dark"] .field textarea {
                background: #0b1220;
                border-color: #334155;
                color: #e2e8f0;
            }

            html[data-theme="dark"] .alert-warning { background: rgba(245, 158, 11, .16); color: #fcd34d; }
            html[data-theme="dark"] .alert-info { background: rgba(59, 130, 246, .18); color: #93c5fd; }

            html[data-theme="dark"] .flash-success { background: rgba(34, 197, 94, .16) !important; color: #86efac !important; }
            html[data-theme="dark"] .flash-error { background: rgba(239, 68, 68, .16) !important; color: #fca5a5 !important; }
        }

        .main {
            flex: 1;
            min-width: 0;
        }

        .topbar {
            height: 72px;
            background: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 30px;
            border-bottom: 1px solid #e2e8f0;
        }

        .page-title {
            font-size: 20px;
            font-weight: 700;
        }

        .page-subtitle {
            font-size: 13px;
            color: #64748b;
            margin-top: 3px;
        }

        .user-area {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .user-name {
            font-size: 14px;
            font-weight: 600;
            color: #cbd5e1;
            margin-bottom: 10px;
            padding: 0 10px;
        }

        .logout-form {
            margin: 0;
            width: 100%;
        }

        .logout-button {
            border: 1px solid rgba(248, 113, 113, .35);
            background: rgba(248, 113, 113, .12);
            color: #fca5a5;
            padding: 10px 12px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 13px;
            font-weight: 600;
            width: 100%;
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: background 0.2s;
        }

        .logout-button:hover {
            background: rgba(248, 113, 113, .24);
            color: #fecaca;
        }

        .content {
            padding: 30px;
        }

        @media (max-width: 800px) {
            .sidebar {
                width: 210px;
            }

            .topbar {
                padding: 0 20px;
            }

            .content {
                padding: 20px;
            }
        }

        @media (max-width: 650px) {
            .sidebar {
                display: none;
            }
        }
    </style>

    @stack('styles')

    <script>
        try {
            if (localStorage.getItem('sidebarCollapsed') === '1') {
                document.documentElement.classList.add('sb-collapsed');
            }
            if (localStorage.getItem('theme') === 'dark') {
                document.documentElement.setAttribute('data-theme', 'dark');
            }
        } catch (e) {}
    </script>
</head>

<body>

<div class="app">

    <aside class="sidebar">

        <!-- Bagian Atas Sidebar (Brand & Menu) -->
        <div class="sidebar-top">
            <div class="brand">
                <img
                    src="{{ asset('images/logo-komdigi.png') }}"
                    alt="Logo Komdigi"
                    class="brand-logo"
                >

                <div class="brand-text">
                    <div class="brand-title">
                        ABSENSI WAJAH
                    </div>

                    <div class="brand-subtitle">
                        Sistem Absensi Karyawan
                    </div>
                </div>

                <button
                    type="button"
                    id="sidebarToggle"
                    class="sb-btn"
                    title="Kecilkan / lebarkan menu"
                    aria-label="Kecilkan atau lebarkan menu"
                >
                    <svg class="ico" viewBox="0 0 24 24"><polyline points="11 17 6 12 11 7"/><polyline points="18 17 13 12 18 7"/></svg>
                </button>
            </div>

            <div class="menu-title">
                Menu
            </div>

            <nav class="menu">
                <a
                    href="{{ route('dashboard') }}"
                    title="Dashboard"
                    class="{{ request()->routeIs('dashboard') ? 'active' : '' }}"
                >
                    <svg class="ico" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                    <span>Dashboard</span>
                </a>

                <a
                    href="{{ route('employees.index') }}"
                    title="Karyawan"
                    class="{{ request()->routeIs('employees.*') ? 'active' : '' }}"
                >
                    <svg class="ico" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    <span>Karyawan</span>
                </a>

                <a
                    href="{{ route('work-schedules.index') }}"
                    title="Jadwal Kerja"
                    class="{{ request()->routeIs('work-schedules.*') ? 'active' : '' }}"
                >
                    <svg class="ico" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                    <span>Jadwal Kerja</span>
                </a>

                <a
                    href="{{ route('attendance.today') }}"
                    title="Absensi Hari Ini"
                    class="{{ request()->routeIs('attendance.today') ? 'active' : '' }}"
                >
                    <svg class="ico" viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                    <span>Absensi Hari Ini</span>
                </a>

                <a
                    href="{{ route('attendance.index') }}"
                    title="Riwayat Absensi"
                    class="{{ request()->routeIs('attendance.index', 'attendance.show') ? 'active' : '' }}"
                >
                    <svg class="ico" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    <span>Riwayat Absensi</span>
                </a>

                <a
                    href="{{ route('reports.index') }}"
                    title="Laporan"
                    class="{{ request()->routeIs('reports.*') ? 'active' : '' }}"
                >
                    <svg class="ico" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                    <span>Laporan</span>
                </a>

                <a
                    href="{{ route('audit-logs.index') }}"
                    title="Audit Log"
                    class="{{ request()->routeIs('audit-logs.*') ? 'active' : '' }}"
                >
                    <svg class="ico" viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                    <span>Audit Log</span>
                </a>

                <a
                    href="{{ route('python-api.status') }}"
                    title="Status Python API"
                    class="{{ request()->routeIs('python-api.*') ? 'active' : '' }}"
                >
                    <svg class="ico" viewBox="0 0 24 24"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
                    <span>Status Python API</span>
                </a>
            </nav>
        </div>

        <!-- Bagian Bawah Sidebar (User Info & Tombol Logout) -->
        @auth
            <div class="sidebar-bottom">
                <div class="user-name">
                    {{ auth()->user()->name }}
                </div>

                <form
                    method="POST"
                    action="{{ route('logout') }}"
                    class="logout-form"
                >
                    @csrf

                    <button
                        type="submit"
                        class="logout-button"
                        title="Logout"
                    >
                        <svg class="ico" viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                        <span>Logout</span>
                    </button>
                </form>
            </div>
        @endauth

    </aside>

    <main class="main">

        <header class="topbar">
            <div>
                <div class="page-title">
                    @yield('title', 'Dashboard')
                </div>

                <div class="page-subtitle">
                    @yield(
                        'subtitle',
                        'Sistem Absensi Karyawan Otomatis'
                    )
                </div>
            </div>

            <button
                type="button"
                id="themeToggle"
                class="theme-btn"
                title="Mode gelap / terang"
                aria-label="Ganti mode gelap atau terang"
            >
                <svg class="ico i-moon" viewBox="0 0 24 24"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
                <svg class="ico i-sun" viewBox="0 0 24 24"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>
            </button>
        </header>

        <section class="content">

            @if(session('success'))
                <div class="flash flash-success" style="
                    background:#dcfce7;
                    color:#166534;
                    padding:12px 16px;
                    border-radius:8px;
                    margin-bottom:20px;
                ">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="flash flash-error" style="
                    background:#fee2e2;
                    color:#991b1b;
                    padding:12px 16px;
                    border-radius:8px;
                    margin-bottom:20px;
                ">
                    {{ session('error') }}
                </div>
            @endif

            @if($errors->any())
                <div class="flash flash-error" style="
                    background:#fee2e2;
                    color:#991b1b;
                    padding:12px 16px;
                    border-radius:8px;
                    margin-bottom:20px;
                ">
                    {{ $errors->first() }}
                </div>
            @endif

            @yield('content')

        </section>

    </main>

</div>

<script>
    (function () {
        var root = document.documentElement;

        // Kecilkan / lebarkan menu
        var sbBtn = document.getElementById('sidebarToggle');
        if (sbBtn) {
            sbBtn.setAttribute('aria-expanded', root.classList.contains('sb-collapsed') ? 'false' : 'true');

            sbBtn.addEventListener('click', function () {
                var collapsed = root.classList.toggle('sb-collapsed');
                sbBtn.setAttribute('aria-expanded', collapsed ? 'false' : 'true');
                try { localStorage.setItem('sidebarCollapsed', collapsed ? '1' : '0'); } catch (e) {}
            });
        }

        // Mode gelap / terang
        var themeBtn = document.getElementById('themeToggle');
        if (themeBtn) {
            themeBtn.addEventListener('click', function () {
                var dark = root.getAttribute('data-theme') === 'dark';

                if (dark) {
                    root.removeAttribute('data-theme');
                } else {
                    root.setAttribute('data-theme', 'dark');
                }

                try { localStorage.setItem('theme', dark ? 'light' : 'dark'); } catch (e) {}
            });
        }
    })();
</script>

@stack('scripts')

</body>
</html>