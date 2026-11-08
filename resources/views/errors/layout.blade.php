<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>@yield('code') · @yield('title') - FoodCycle</title>
    <link rel="icon" type="image/png" href="/images/mascot.png">
    <script>
        try {
            const t = localStorage.getItem('theme');
            if (t === 'dark' || (t !== 'light' && matchMedia('(prefers-color-scheme: dark)').matches)) {
                document.documentElement.classList.add('dark');
            }
        } catch (e) {}
    </script>
    {{-- Self-contained styles: error pages must render even when the build or the app itself is broken --}}
    <style>
        :root { --bg: #f8fafc; --card: #ffffff; --text: #0f172a; --muted: #64748b; --border: #e2e8f0; --brand: #0b50b5; --brand2: #4f46e5; --accent: #f97316; }
        .dark { --bg: #020617; --card: #0f172a; --text: #f8fafc; --muted: #94a3b8; --border: #1e293b; }
        * { box-sizing: border-box; }
        body { margin: 0; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 24px 16px;
               background: var(--bg); color: var(--text);
               font-family: 'Plus Jakarta Sans', system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif; }
        .card { width: 100%; max-width: 440px; background: var(--card); border: 1px solid var(--border); border-radius: 24px;
                padding: 36px 28px; text-align: center; box-shadow: 0 20px 40px -20px rgba(15, 23, 42, .25); }
        img { width: 120px; height: auto; }
        .code { margin: 16px 0 0; font-size: 56px; font-weight: 800; line-height: 1; letter-spacing: -2px;
                background: linear-gradient(90deg, var(--brand), var(--brand2)); -webkit-background-clip: text; background-clip: text; color: transparent; }
        h1 { margin: 10px 0 6px; font-size: 20px; font-weight: 800; }
        p { margin: 0; font-size: 14px; line-height: 1.6; color: var(--muted); }
        .actions { margin-top: 24px; display: flex; gap: 10px; justify-content: center; flex-wrap: wrap; }
        a, button { font: inherit; font-size: 13px; font-weight: 700; text-decoration: none; border-radius: 12px; padding: 10px 18px; cursor: pointer; }
        .primary { color: #fff; border: 0; background: linear-gradient(90deg, var(--brand), var(--brand2)); }
        .ghost { color: var(--text); background: transparent; border: 1px solid var(--border); }
    </style>
</head>
<body>
    <main class="card">
        <img src="/images/mascot.png" alt="Maskot FoodCycle">
        <div class="code">@yield('code')</div>
        <h1>@yield('title')</h1>
        <p>@yield('message')</p>
        <div class="actions">
            <a class="primary" href="/">Ke Beranda</a>
            <button class="ghost" type="button" onclick="history.length > 1 ? history.back() : location.assign('/')">Kembali</button>
        </div>
    </main>
</body>
</html>
