<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('code') - @yield('title')</title>

    <style>
        * { box-sizing: border-box; }

        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, "Segoe UI", sans-serif;
            background: #f4f7fb;
            color: #0f172a;
            padding: 20px;
        }

        .box {
            background: #fff;
            border-radius: 16px;
            padding: 40px 32px;
            max-width: 460px;
            width: 100%;
            text-align: center;
            box-shadow: 0 15px 40px rgba(0, 0, 0, .08);
        }

        .code { font-size: 64px; font-weight: 800; color: #2563eb; line-height: 1; }
        h1 { font-size: 22px; margin: 16px 0 8px; }
        p { color: #64748b; font-size: 14px; line-height: 1.6; margin: 0 0 24px; }

        a {
            display: inline-block;
            background: #2563eb;
            color: #fff;
            text-decoration: none;
            padding: 11px 18px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="box">
        <div class="code">@yield('code')</div>
        <h1>@yield('title')</h1>
        <p>@yield('message')</p>
        <a href="{{ url('/') }}">Kembali ke Beranda</a>
    </div>
</body>
</html>